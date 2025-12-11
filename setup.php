<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_management');

try {
    // Create connection to MySQL server (without specifying database)
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database created successfully<br>";
    
    // Now connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables
    $sql = file_get_contents('database_schema.sql');
    $pdo->exec($sql);
    echo "Tables created successfully<br>";
    
    // Insert sample data
    require_once 'php/sample_data.php';
    echo "Sample data inserted successfully<br>";
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>You can now access the application:</p>";
    echo "<ul>";
    echo "<li><a href='login.html'>Login Page</a></li>";
    echo "<li><a href='register.html'>Registration Page</a></li>";
    echo "</ul>";
    echo "<p>Default admin login:</p>";
    echo "<ul>";
    echo "<li>Username: admin</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    // Check if database already exists
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Database already exists<br>";
        
        // Create tables
        $sql = file_get_contents('database_schema.sql');
        $pdo->exec($sql);
        echo "Tables created successfully<br>";
        
        // Insert sample data
        require_once 'php/sample_data.php';
        echo "Sample data inserted successfully<br>";
        
        echo "<h2>Setup Complete!</h2>";
        echo "<p>You can now access the application:</p>";
        echo "<ul>";
        echo "<li><a href='login.html'>Login Page</a></li>";
        echo "<li><a href='register.html'>Registration Page</a></li>";
        echo "</ul>";
        echo "<p>Default admin login:</p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: admin123</li>";
        echo "</ul>";
    } catch(Exception $e2) {
        echo "Error: " . $e->getMessage();
    }
}
?>