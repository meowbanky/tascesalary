<?php
require_once 'App.php';
$App = new App();
$App->checkAuthentication();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $ed_id = $_POST['ed_id'] ?? '';
    $payee_name = $_POST['payee_name'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';
    $account_no = $_POST['account_no'] ?? '';
    $fixed_amount = $_POST['fixed_amount'] ?? 0;
    $percentage = $_POST['percentage'] ?? 0;

    if (empty($ed_id) || empty($payee_name)) {
        echo json_encode(['success' => false, 'message' => 'Deduction and Payee Name are required.']);
        exit;
    }

    if (!empty($id)) {
        // Update
        $query = "UPDATE tbl_deduction_payee 
                  SET ed_id = :ed_id, payee_name = :payee_name, bank_name = :bank_name, 
                      account_no = :account_no, fixed_amount = :fixed_amount, percentage = :percentage 
                  WHERE id = :id";
        $params = [
            ':ed_id' => $ed_id,
            ':payee_name' => $payee_name,
            ':bank_name' => $bank_name,
            ':account_no' => $account_no,
            ':fixed_amount' => $fixed_amount,
            ':percentage' => $percentage,
            ':id' => $id
        ];
        $result = $App->executeNonSelect($query, $params);
        $message = "Payee updated successfully.";
    } else {
        // Insert
        $query = "INSERT INTO tbl_deduction_payee (ed_id, payee_name, bank_name, account_no, fixed_amount, percentage) 
                  VALUES (:ed_id, :payee_name, :bank_name, :account_no, :fixed_amount, :percentage)";
        $params = [
            ':ed_id' => $ed_id,
            ':payee_name' => $payee_name,
            ':bank_name' => $bank_name,
            ':account_no' => $account_no,
            ':fixed_amount' => $fixed_amount,
            ':percentage' => $percentage
        ];
        $result = $App->executeNonSelect($query, $params);
        $message = "Payee added successfully.";
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
