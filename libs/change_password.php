<?php
require_once 'App.php';
$App = new App();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = [];
    $currentpassword = trim($_POST['cpassword']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $staff_id = $_SESSION['SESS_MEMBER_ID'];


    // Validate token and expiry
    $query = "SELECT password FROM username WHERE staff_id = :staff_id";
    $params = [':staff_id' => $staff_id];
    $result = $App->selectOne($query, $params);

    if ($result) {
        $passwordcheck   = $result['password'];
        if(!password_verify($currentpassword,$passwordcheck)){
            $response['status'] = 'error';
            $response['message'] = 'Current Password is not correct.';
            echo json_encode($response);
            exit();
        }
    }


    if ($password !== $confirm_password) {

        $response['status'] = 'error';
        $response['message'] = 'Passwords do not match.';
        echo json_encode($response);
        exit();

    }

    if (strlen($password) < 6) {
        $response['status'] = 'error';
        $response['message'] = 'Password must be at least 6 characters.';
        echo json_encode($response);
        exit();

    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $updateQuery = "UPDATE username SET password = :password WHERE staff_id = :staff_id";
    $updateParams = [
        ':password' => $hashedPassword,
        ':staff_id' => $staff_id
    ];
    $App->executeNonSelect($updateQuery, $updateParams);

    $response['status'] = 'success';
    $response['message'] = 'Your password has been successfully changed.';
    echo json_encode($response);
    exit();
}
?>