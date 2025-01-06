<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();
$response = [];
$array = [];

// Check if all required POST parameters are set
if(isset($_POST['staff_id'], $_POST['value'], $_POST['allow_id'], $_POST['counter'])) {
    // Sanitize inputs
    $staff_id = filter_var($_POST['staff_id'], FILTER_VALIDATE_INT);
    $value = filter_var($_POST['value'], FILTER_VALIDATE_FLOAT);
    $allow_id = filter_var($_POST['allow_id'], FILTER_VALIDATE_INT);
    $counter = filter_var($_POST['counter'], FILTER_VALIDATE_INT);
    $net = filter_var($_POST['net'], FILTER_VALIDATE_INT);
    $user_id = $_SESSION['SESS_MEMBER_ID'];
    $allow_type = $App->getallowType($allow_id);
    $netCheck = 0;
    $valid = true;
        if(intval($allow_type['edType']) == 2){
          $netCheck =   intval($net) - intval($value);
          if($netCheck <= 0){
              $valid = false;
          }
        }
            // Validate inputs
    if($staff_id && $value > 0 && $allow_id && $counter !== false) {
        if (!$valid){
            $response['status'] = 'error';
            $response['message'] = 'This Deduction will take this Staff Net to negative values';
            echo json_encode($response);
            exit();
                }else{
            $array = [
                ':staff_id' => $staff_id,
                ':allow_id' => $allow_id,
                ':value' => $value,
                ':counter' => $counter,
                ':inserted_by' => $user_id
            ];

            // Check if the record already exists
            $checkQuery = "SELECT COUNT(*) as count FROM allow_deduc WHERE staff_id = :staff_id AND allow_id = :allow_id";
            $checkParams = [
                ':staff_id' => $staff_id,
                ':allow_id' => $allow_id
            ];

            $result = $App->selectOne($checkQuery, $checkParams);

            if ($result && $result['count'] > 0) {
                // Update existing record
                $success = $App->updateAllowances($value, $user_id, $staff_id, $allow_id, $counter);
            } else {
                // Insert new record
                $success = $App->insertAllowances($value, $user_id, $staff_id, $allow_id, $counter);
            }

            if ($success) {
                $response['status'] = 'success';
                $response['message'] = 'Save Successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error Saving';
            }
        }
    } else {
        // Input validation failed
        $response['status'] = 'error';
        $response['message'] = 'Invalid input values';
    }
} else {
    // Missing required parameters
    $response['status'] = 'error';
    $response['message'] = 'Check your Inputs';
}

echo json_encode($response);
?>
