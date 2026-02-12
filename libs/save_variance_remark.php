<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $month1 = filter_input(INPUT_POST, 'month1', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $month2 = filter_input(INPUT_POST, 'month2', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $remark = filter_input(INPUT_POST, 'remark', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Log incoming data for debugging
    error_log("Variance Remark Save - staff_id: $staff_id, month1: $month1, month2: $month2");
    
    // Handle empty staff_id gracefully - user may not have entered anything yet
    if (empty($staff_id) || empty($month1) || empty($month2)) {
        $response['status'] = 'success';
        $response['message'] = 'No data to save';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    if ($staff_id && $month1 && $month2) {
        try {
            // First check if the variance_remarks table exists
            $tableCheck = $App->link->query("SHOW TABLES LIKE 'variance_remarks'");
            if ($tableCheck->rowCount() == 0) {
                throw new Exception('variance_remarks table does not exist. Please run the database migration first.');
            }
            
            $created_by = $_SESSION['SESS_MEMBER_ID'] ?? null;
            
            // Get the actual staff_id from employee table using OGNO
            $staffQuery = $App->link->prepare("SELECT staff_id FROM employee WHERE OGNO = :ogno");
            $staffQuery->execute([':ogno' => $staff_id]);
            $staffRecord = $staffQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$staffRecord) {
                throw new Exception("Staff member with OGNO $staff_id not found");
            }
            
            $actual_staff_id = $staffRecord['staff_id'];
            
            // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both new and existing remarks
            $stmt = $App->link->prepare("
                INSERT INTO variance_remarks 
                    (staff_id, month1_period_id, month2_period_id, remark, created_by) 
                VALUES 
                    (:staff_id, :month1, :month2, :remark, :created_by)
                ON DUPLICATE KEY UPDATE 
                    remark = :remark_update,
                    updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([
                ':staff_id' => $actual_staff_id,
                ':month1' => $month1,
                ':month2' => $month2,
                ':remark' => $remark,
                ':created_by' => $created_by,
                ':remark_update' => $remark
            ]);
            
            $response['status'] = 'success';
            $response['message'] = 'Remark saved successfully';
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Database error: ' . $e->getMessage();
            error_log("Variance Remark DB Error: " . $e->getMessage());
        } catch (Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            error_log("Variance Remark Error: " . $e->getMessage());
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid input: staff_id, month1, and month2 are required. Received: staff_id=' . ($staff_id ?? 'null') . ', month1=' . ($month1 ?? 'null') . ', month2=' . ($month2 ?? 'null');
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
