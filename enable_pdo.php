<?php
/**
 * PDO Extension Enabler Script
 * This script will check and provide instructions to enable PDO
 */

$extensionDir = '/opt/alt/php84/usr/lib64/php/modules';
$iniScanDir = '/opt/alt/php84/link/conf';
$pdoSo = $extensionDir . '/pdo.so';
$pdoMysqlSo = $extensionDir . '/pdo_mysql.so';

echo "<h2>PDO Extension Status Check</h2>";
echo "<pre>";

// Check if extension files exist
echo "Checking extension files...\n";
echo "Extension Directory: $extensionDir\n\n";

if (file_exists($pdoSo)) {
    echo "✓ pdo.so found at: $pdoSo\n";
} else {
    echo "✗ pdo.so NOT found at: $pdoSo\n";
}

if (file_exists($pdoMysqlSo)) {
    echo "✓ pdo_mysql.so found at: $pdoMysqlSo\n";
} else {
    echo "✗ pdo_mysql.so NOT found at: $pdoMysqlSo\n";
}

echo "\n";
echo "Current PDO Status:\n";
if (extension_loaded('pdo')) {
    echo "✓ PDO extension is LOADED\n";
} else {
    echo "✗ PDO extension is NOT LOADED\n";
}

if (extension_loaded('pdo_mysql')) {
    echo "✓ PDO MySQL driver is LOADED\n";
} else {
    echo "✗ PDO MySQL driver is NOT LOADED\n";
}

echo "\n";
echo "=== INSTRUCTIONS TO ENABLE PDO ===\n\n";

echo "Method 1: Via cPanel MultiPHP INI Editor (Recommended)\n";
echo "1. Log into cPanel\n";
echo "2. Go to 'MultiPHP INI Editor' or 'Select PHP Version'\n";
echo "3. Select PHP 8.4 for your domain\n";
echo "4. In the 'Extensions' section, check/enable:\n";
echo "   - pdo\n";
echo "   - pdo_mysql\n";
echo "5. Click 'Save'\n\n";

echo "Method 2: Create extension INI file\n";
echo "Create a file at: $iniScanDir/20-pdo.ini\n";
echo "With content:\n";
echo "extension=pdo.so\n";
echo "extension=pdo_mysql.so\n\n";

echo "Method 3: Add to .htaccess (if you have file access)\n";
echo "Add to your .htaccess file:\n";
echo "<IfModule mod_php.c>\n";
echo "  php_value extension pdo.so\n";
echo "  php_value extension pdo_mysql.so\n";
echo "</IfModule>\n\n";

echo "Method 4: Create php.ini in public_html\n";
echo "Create php.ini in /home/tascesal/public_html/ with:\n";
echo "extension=pdo.so\n";
echo "extension=pdo_mysql.so\n";

echo "</pre>";
?>