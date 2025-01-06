<?php
require_once 'App.php';
$App = new App();

$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $allowcode = $_POST['ID'];
        $value = $_POST['value'];

        try {
            $stmt = $App->link->prepare("UPDATE allowancetable SET value = :value WHERE allow_id = :allow_id");
            $stmt->execute([':value' => $value, ':allow_id' => $allowcode]);

            $response['status'] = 'success';
            $response['message'] = 'Record updated successfully';
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid action';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
