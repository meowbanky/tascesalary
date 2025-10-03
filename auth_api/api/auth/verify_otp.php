<?php
// auth/verify_otp.php

// Clear any existing output and enable error reporting
if (ob_get_level()) ob_end_clean();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

    require_once __DIR__ . '/../../config/Database.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../utils/emailservice.php';
    require_once __DIR__ . '/../../config/CorsHandler.php';
    CorsHandler::handleCors();

// Set JSON content type
header('Content-Type: application/json; charset=UTF-8');

try {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Get and validate input
    $input = file_get_contents('php://input');
    $data = json_decode($input);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    if (!isset($data->email) || !isset($data->otp)) {
        throw new Exception('Email and OTP are required');
    }

    $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
    $otp = filter_var($data->otp, FILTER_SANITIZE_NUMBER_INT);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (strlen($otp) !== 6 || !is_numeric($otp)) {
        throw new Exception('Invalid OTP format');
    }

    // Include dependencies
  

    // Initialize services
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Verify OTP
    $result = $user->verifyPasswordResetOTP($email, $otp);
    
    if ($result['success']) {
        $response = [
            'success' => true,
            'message' => 'OTP verified successfully',
            'reset_token' => $result['reset_token']
        ];
        http_response_code(200);
    } else {
        $response = [
            'success' => false,
            'message' => $result['message'] ?? 'Invalid or expired OTP'
        ];
        http_response_code(400);
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("OTP verification error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}