<?php
// Usage
require_once '../vendor/autoload.php';
require_once '../config/config.php';
require_once '../libs/App.php';
require_once  '../libs/PayslipMailer.php';

try {
$app = new App();
$mailer = new PayslipMailer($app);
$mailer->setPeriod($_POST['period']);
$mailer->processBatch();
echo 'Batch processed successfully';
} catch (Exception $e) {
error_log("Batch processing failed: " . $e->getMessage());
http_response_code(500);
echo 'An error occurred while processing the batch';
}
?>