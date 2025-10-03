<?php
// Debug script for backup functionality
session_start();

// Check if user is authenticated
if (!isset($_SESSION['SESS_MEMBER_ID']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    die('Unauthorized access');
}

require_once('config/config.php');

echo "<h2>Backup Debug Information</h2>";

echo "<h3>Configuration:</h3>";
echo "HOST: " . HOST . "<br>";
echo "USER: " . USER . "<br>";
echo "DBNAME: " . DBNAME . "<br>";
echo "Current Directory: " . getcwd() . "<br>";
echo "Script Directory: " . __DIR__ . "<br>";

echo "<h3>Backup Directory:</h3>";
$backupDir = __DIR__ . '/backup/';
echo "Backup Directory: " . $backupDir . "<br>";
echo "Directory exists: " . (is_dir($backupDir) ? 'Yes' : 'No') . "<br>";
echo "Directory writable: " . (is_writable($backupDir) ? 'Yes' : 'No') . "<br>";

if (is_dir($backupDir)) {
    echo "Directory permissions: " . substr(sprintf('%o', fileperms($backupDir)), -4) . "<br>";
}

echo "<h3>Database Connection Test:</h3>";
try {
    $conn = new mysqli(HOST, USER, PASS, DBNAME);
    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "Database connection successful!<br>";
        
        // Test query
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "Tables found: " . $result->num_rows . "<br>";
        } else {
            echo "Error getting tables: " . $conn->error . "<br>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "<br>";
}

echo "<h3>PHP Configuration:</h3>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h3>Test Backup Directory Creation:</h3>";
$testDir = __DIR__ . '/test_backup/';
if (!is_dir($testDir)) {
    if (mkdir($testDir, 0755, true)) {
        echo "Successfully created test directory: " . $testDir . "<br>";
        rmdir($testDir);
        echo "Test directory cleaned up.<br>";
    } else {
        echo "Failed to create test directory: " . $testDir . "<br>";
    }
} else {
    echo "Test directory already exists.<br>";
}
?>
