<?php
echo "<h2>Database Driver Diagnosis</h2>";

// Check available PDO drivers
echo "<h3>Available PDO Drivers:</h3>";
$drivers = PDO::getAvailableDrivers();
if (count($drivers) > 0) {
    echo "<ul>";
    foreach ($drivers as $driver) {
        echo "<li>$driver</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No PDO drivers available</p>";
}

// Check all loaded extensions
echo "<h3>All Loaded Extensions:</h3>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<div style='columns: 3;'>";
foreach ($extensions as $ext) {
    if (strpos(strtolower($ext), 'pdo') !== false || strpos(strtolower($ext), 'mysql') !== false) {
        echo "<p>$ext</p>";
    }
}
echo "</div>";

// Check PHP configuration
echo "<h3>PHP Configuration:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Try to show php.ini location
echo "<p>Configuration file: " . php_ini_loaded_file() . "</p>";

echo "<hr>";

// Show instructions
echo "<h3>Resolution Steps:</h3>";
echo "<ol>";
echo "<li>Make sure MySQL service is running in XAMPP Control Panel</li>";
echo "<li>Ensure PDO MySQL extension is enabled (it should be by default)</li>";
echo "<li>Restart the web server after making changes</li>";
echo "</ol>";

echo "<p><a href='login.html'>← Back to Login</a></p>";
?>