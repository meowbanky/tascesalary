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
    $query = "SELECT user_id FROM password_resets WHERE reset_token = :token AND expires_at > NOW()";
    $params = [':token' => $token];
    $result = $App->selectOne($query, $params);

    if (!$result) {

        $response['status'] = 'error';
        $response['message'] = 'Invalid or expired token.';
        echo json_encode($response);
        exit();
    }


    $staff_id = $result['user_id'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    // Note: username table uses 'staff_id' as primary/foreign key
    $updateQuery = "UPDATE username SET password = :password WHERE staff_id = :staff_id";
    $updateParams = [
        ':password' => $hashedPassword,
        ':staff_id' => $staff_id
    ];
    $App->executeNonSelect($updateQuery, $updateParams);

    // Delete the token
    $deleteQuery = "DELETE FROM password_resets WHERE reset_token = :token";
    $deleteParams = [':token' => $token];
    $App->executeNonSelect($deleteQuery, $deleteParams);

    $response['status'] = 'success';
    $response['message'] = 'Your password has been successfully reset.';
    echo json_encode($response);
    exit();
}
?>