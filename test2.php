<?php
/**
 * PHP Configuration Checker
 * Check if PDO and required extensions are enabled
 */

echo "<h1>PHP Configuration Check</h1>";
echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";

echo "<h3>PDO Status:</h3>";
if (extension_loaded('pdo')) {
    echo "<p style='color: green;'>✓ PDO extension is LOADED</p>";
    $drivers = PDO::getAvailableDrivers();
    echo "<p>Available PDO Drivers: " . implode(', ', $drivers) . "</p>";
    
    if (in_array('mysql', $drivers)) {
        echo "<p style='color: green;'>✓ PDO MySQL driver is available</p>";
    } else {
        echo "<p style='color: red;'>✗ PDO MySQL driver is NOT available</p>";
    }
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ PDO extension is NOT LOADED</p>";
    echo "<p>You need to enable PDO extension. See ENABLE_PDO.md for instructions.</p>";
}

echo "<h3>Extension Directory:</h3>";
echo "<p>" . ini_get('extension_dir') . "</p>";

echo "<h3>To enable PDO on cPanel:</h3>";
echo "<ol>";
echo "<li>Go to cPanel → MultiPHP INI Editor</li>";
echo "<li>Select your domain</li>";
echo "<li>Add these lines:</li>";
echo "<pre>extension=pdo.so\nextension=pdo_mysql.so</pre>";
echo "<li>Click Save</li>";
echo "</ol>";

echo "<hr>";
echo "<h3>Full PHP Info:</h3>";
phpinfo();
?>