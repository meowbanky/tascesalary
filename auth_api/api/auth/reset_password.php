<?php
if (ob_get_level()) ob_end_clean();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Include CORS handler
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

    if (!isset($data->email) || !isset($data->new_password) || !isset($data->reset_token)) {
        throw new Exception('Email, new password, and reset token are required');
    }

    $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
    $newPassword = $data->new_password;
   $resetToken = htmlspecialchars(strip_tags($data->reset_token), ENT_QUOTES, 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (strlen($newPassword) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }

    // Initialize services
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Reset password
    $result = $user->resetPassword($email, $newPassword, $resetToken);
    
    if ($result['success']) {
        $response = [
            'success' => true,
            'message' => 'Password reset successfully'
        ];
        http_response_code(200);
    } else {
        $response = [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to reset password'
        ];
        http_response_code(400);
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}