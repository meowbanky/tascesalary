<?php
require_once 'App.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';

$App = new App();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = [];
    $email = trim($_POST['email']);
    $businessNames = $App->getBusinessName();
    $businessName = $businessNames['business_name'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid email address.';
        echo json_encode($response);
        exit();
    }

    // Check if the email exists in the employee table
    $checkEmailQuery = "SELECT staff_id FROM employee WHERE EMAIL = :email LIMIT 1";
    $checkEmailParams = [':email' => $email];
    $existingEmployee = $App->selectOne($checkEmailQuery, $checkEmailParams);

    if (!$existingEmployee) {
        $response['status'] = 'error';
        $response['message'] = 'No account found with that email address.';
        echo json_encode($response);
        exit();
    }
    
    $staff_id = $existingEmployee['staff_id'];

    // Generate a unique token
    $token = bin2hex(random_bytes(32)); // 64 chars
    $otp = '000000'; // Default OTP as table requires it, though we are using link

    // Store the token in the database with an expiration date
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Using schema: user_id, otp, reset_token, expires_at, created_at
    // First clear old requests for this user
    $clearQuery = "DELETE FROM password_resets WHERE user_id = :user_id";
    $clearParams = [':user_id' => $staff_id];
    $App->executeNonSelect($clearQuery, $clearParams);
    
    $query = "INSERT INTO password_resets (user_id, otp, reset_token, expires_at, created_at) VALUES (:user_id, :otp, :reset_token, :expires_at, NOW())";
    $params = [
        ':user_id' => $staff_id,
        ':otp' => $otp,
        ':reset_token' => $token,
        ':expires_at' => $expiry
    ];
    $App->executeNonSelect($query, $params);

    // Send the reset link to the user's email using PHPMailer
    $resetLink = "https://tascesalary.com.ng/reset_password.php?token=$token";

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = HOST_MAIL; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = USERNAME; // SMTP username
        $mail->Password = PASSWORD; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Enable SMTP debugging
        $mail->SMTPDebug = 0; // Enable verbose debug output (0 = off, 1 = client messages, 2 = client and server messages)
        $mail->Debugoutput = 'html'; // Output format

        // Recipients
        $mail->setFrom('no-reply@tascesalary.com.ng',  $businessName);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Click the link to reset your password: <a href=\"$resetLink\">$resetLink</a>";

        $mail->send();

        $response['status'] = 'success';
        $response['message'] = 'A password reset link has been sent to your email address.';
        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        echo json_encode($response);
        exit();
    }
}
?>