<?php
// Test MySQL connection
echo "<h2>MySQL Database Connection Test</h2>";

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_management');

try {
    // Test 1: Connect to MySQL server
    echo "<h3>Test 1: Connecting to MySQL server...</h3>";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Successfully connected to MySQL server</p>";
    
    // Test 2: Check if database exists
    echo "<h3>Test 2: Checking if database exists...</h3>";
    $stmt = $pdo->prepare("SHOW DATABASES LIKE ?");
    $stmt->execute([DB_NAME]);
    $databaseExists = $stmt->fetch();
    
    if ($databaseExists) {
        echo "<p style='color: green;'>✓ Database '" . DB_NAME . "' exists</p>";
        
        // Test 3: Connect to specific database
        echo "<h3>Test 3: Connecting to specific database...</h3>";
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p style='color: green;'>✓ Successfully connected to database '" . DB_NAME . "'</p>";
        
        // Test 4: Check if tables exist
        echo "<h3>Test 4: Checking tables...</h3>";
        $stmt = $pdo->prepare("SHOW TABLES");
        $stmt->execute();
        $tables = $stmt->fetchAll();
        
        if (count($tables) > 0) {
            echo "<p style='color: green;'>✓ Found " . count($tables) . " tables in database</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . $table[0] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠ No tables found in database</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Database '" . DB_NAME . "' does not exist</p>";
        echo "<p>You need to create the database and import the schema from database_schema.sql</p>";
    }
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Connection failed: " . $e->getMessage() . "</p>";
    
    // Provide troubleshooting tips
    echo "<h3>Troubleshooting Tips:</h3>";
    echo "<ul>";
    echo "<li>Make sure MySQL service is running in XAMPP Control Panel</li>";
    echo "<li>Check if the database 'inventory_management' has been created</li>";
    echo "<li>Verify database credentials in php/db_connect.php</li>";
    echo "<li>Ensure port 3306 is not blocked by firewall</li>";
    echo "</ul>";
}

// Test 5: Check PHP configuration
echo "<h3>Test 5: PHP Configuration Check</h3>";
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green;'>✓ PDO MySQL extension is loaded</p>";
} else {
    echo "<p style='color: red;'>✗ PDO MySQL extension is not loaded</p>";
}

echo "<p><a href='login.html'>← Back to Login</a></p>";
?>