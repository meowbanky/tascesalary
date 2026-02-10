<?php
require_once '../config/config.php';
require_once '../libs/App.php';
require_once 'PayslipControl.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $app = new App();
    $control = new PayslipControl($app);

    $action = $_POST['action'] ?? '';
    $result = false;

    switch ($action) {
        case 'start':
            $result = $control->startProcess();
            break;
        case 'stop':
            $result = $control->stopProcess();
            break;
        case 'reset':
            $result = $control->resetProgress();
            break;
        default:
            throw new Exception('Invalid action');
    }

    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Action completed successfully' : 'Action failed'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}