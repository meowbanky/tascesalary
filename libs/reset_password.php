<?php
require_once 'App.php';
$App = new App();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = [];
    $token = trim($_POST['token']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

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

    // Validate token and expiry
    $query = "SELECT email FROM password_resets WHERE token = :token AND expiry > NOW()";
    $params = [':token' => $token];
    $result = $App->selectOne($query, $params);

    if (!$result) {

        $response['status'] = 'error';
        $response['message'] = 'Invalid or expired token.';
        echo json_encode($response);
        exit();
    }


    $email = $result['email'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email exists in the employee table
    $checkEmailQuery = "SELECT staff_id FROM employee WHERE EMAIL = :email LIMIT 1";
    $checkEmailParams = [':email' => $email];
    $existingEmployee = $App->selectOne($checkEmailQuery, $checkEmailParams);

    $username = $existingEmployee['staff_id'];
    // Update the user's password in the database
    $updateQuery = "UPDATE username SET password = :password WHERE username = :username";
    $updateParams = [
        ':password' => $hashedPassword,
        ':username' => $username
    ];
    $App->executeNonSelect($updateQuery, $updateParams);

    // Delete the token
    $deleteQuery = "DELETE FROM password_resets WHERE token = :token";
    $deleteParams = [':token' => $token];
    $App->executeNonSelect($deleteQuery, $deleteParams);

    $response['status'] = 'success';
    $response['message'] = 'Your password has been successfully reset.';
    echo json_encode($response);
    exit();
}
?>