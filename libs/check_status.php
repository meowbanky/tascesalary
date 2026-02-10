// check_status.php
<?php
$statusFile = __DIR__ . '/mailer_status.json';
if (file_exists($statusFile)) {
    $status = json_decode(file_get_contents($statusFile), true);
    echo "Last Run: " . ($status['last_run'] ?? 'Never') . "\n";
    echo "Current Offset: " . ($status['last_offset'] ?? 0) . "\n";
    echo "Completed: " . ($status['completed'] ? 'Yes' : 'No') . "\n";
} else {
    echo "No status file found. Process hasn't started.\n";
}