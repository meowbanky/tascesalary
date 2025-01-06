<?php
require_once '../config/config.php';
require_once '../libs/App.php';
require_once 'PayslipControl.php';

header('Content-Type: application/json');

try {
    $app = new App();
    $control = new PayslipControl($app);

    $progress = $control->getProgress();
    echo json_encode([
        'success' => true,
        'data' => $progress
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get progress'
    ]);
}