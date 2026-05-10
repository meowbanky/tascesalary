<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $query = "DELETE FROM tbl_deduction_payee WHERE id = :id";
    $result = $App->executeNonSelect($query, [':id' => $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Payee deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
