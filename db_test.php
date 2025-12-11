<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_management');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Connection Successful!</h2>";
    
    // Check if tables exist
    $tables = ['products', 'categories', 'suppliers', 'users'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>Table '$table' exists with $count records</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Table '$table' does not exist or is inaccessible: " . $e->getMessage() . "</p>";
        }
    }
    
    // Show sample products if they exist
    try {
        $stmt = $pdo->query("SELECT * FROM products LIMIT 5");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($products) > 0) {
            echo "<h3>Sample Products:</h3>";
            echo "<ul>";
            foreach ($products as $product) {
                echo "<li>" . htmlspecialchars($product['name']) . " (ID: " . $product['id'] . ", Quantity: " . $product['quantity'] . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No products found in the database</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error retrieving products: " . $e->getMessage() . "</p>";
    }
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>Database Connection Failed:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>