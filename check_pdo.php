<?php
/**
 * PDO Extension Checker
 * Upload this file to your server and access it via browser to check PDO status
 */

echo "<h2>PDO Extension Status</h2>";
echo "<pre>";

// Check if PDO extension is loaded
if (extension_loaded('pdo')) {
    echo "✓ PDO extension is LOADED\n";
    echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
} else {
    echo "✗ PDO extension is NOT LOADED\n";
}

echo "\n";

// Check if PDO MySQL driver is loaded
if (extension_loaded('pdo_mysql')) {
    echo "✓ PDO MySQL driver is LOADED\n";
} else {
    echo "✗ PDO MySQL driver is NOT LOADED\n";
}

echo "\n";

// Check loaded extensions
echo "Loaded Extensions:\n";
$loaded = get_loaded_extensions();
$pdo_found = false;
$pdo_mysql_found = false;

foreach ($loaded as $ext) {
    if ($ext === 'pdo') {
        $pdo_found = true;
        echo "  ✓ $ext\n";
    } elseif ($ext === 'pdo_mysql') {
        $pdo_mysql_found = true;
        echo "  ✓ $ext\n";
    }
}

if (!$pdo_found) {
    echo "  ✗ pdo (NOT FOUND)\n";
}
if (!$pdo_mysql_found) {
    echo "  ✗ pdo_mysql (NOT FOUND)\n";
}

echo "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Extension Directory: " . ini_get('extension_dir') . "\n";

echo "\n";
echo "To enable PDO on cPanel/CloudLinux:\n";
echo "1. Log into cPanel\n";
echo "2. Go to 'Select PHP Version' or 'MultiPHP INI Editor'\n";
echo "3. Enable 'pdo' and 'pdo_mysql' extensions\n";
echo "4. Save changes\n";
echo "</pre>";

?>

