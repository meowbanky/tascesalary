<?php
if (ob_get_level()) ob_end_clean();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

     require_once __DIR__ . '/../../config/Database.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../utils/emailservice.php';
    require_once __DIR__ . '/../../config/CorsHandler.php';
    CorsHandler::handleCors();


// Set JSON content type and CORS headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log the request
error_log("Forgot password request received: " . $_SERVER['REQUEST_METHOD']);

try {
    error_log("Starting forgot password processing...");
    
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Invalid method: " . $_SERVER['REQUEST_METHOD']);
        throw new Exception('Method not allowed', 405);
    }

    // Get and validate input
    $input = file_get_contents('php://input');
    error_log("Raw input: " . $input);
    
    $data = json_decode($input);
    error_log("Decoded data: " . print_r($data, true));
    
    if (!$data) {
        error_log("JSON decode failed: " . json_last_error_msg());
        throw new Exception('Invalid JSON data');
    }

    if (!isset($data->email)) {
        throw new Exception('Email is required');
    }

    $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Initialize services
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $emailService = new EmailService();

    // Check if user exists
    $userExists = $user->getUserByEmail($email);
    if (!$userExists) {
        // Don't reveal if email exists or not for security
        $response = [
            'success' => true,
            'message' => 'If the email exists, password reset instructions have been sent.'
        ];
        error_log("User not found, sending response: " . json_encode($response));
        http_response_code(200);
        echo json_encode($response);
        exit;
    }

    // Generate OTP (6 digits)
    $otp = sprintf('%06d', mt_rand(0, 999999));
    
    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    
    // Set expiration (15 minutes from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Save OTP and reset token to database
    $result = $user->savePasswordResetData($userExists['id'], $otp, $resetToken, $expiresAt);
    
    if ($result) {
        // Send email with OTP
        $emailSent = $emailService->sendPasswordResetEmail($email, $otp);
        
        if ($emailSent) {
            $response = [
                'success' => true,
                'message' => 'Password reset instructions have been sent to your email.'
            ];
            error_log("Sending response: " . json_encode($response));
            http_response_code(200);
            echo json_encode($response);
            exit;
        } else {
            throw new Exception('Failed to send email. Please try again.');
        }
    } else {
        throw new Exception('Failed to process reset request. Please try again.');
    }

} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    $errorResponse = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    error_log("Sending error response: " . json_encode($errorResponse));
    
    http_response_code($e->getCode() ?: 400);
    echo json_encode($errorResponse);
    exit;
}

error_log("Forgot password script completed successfully"); 