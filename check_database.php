<?php
require_once 'php/db_connect.php';

try {
    // Check if we can connect to the database
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'products'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "Products table exists\n";
        
        // Check for low stock products
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as low_stock_count 
            FROM products 
            WHERE quantity <= min_stock_level
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Low stock products count: " . $result['low_stock_count'] . "\n";
        
        // Show some sample products
        $stmt = $pdo->prepare("SELECT id, name, quantity, min_stock_level FROM products LIMIT 5");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Sample products:\n";
        foreach ($products as $product) {
            echo "- " . $product['name'] . " (Qty: " . $product['quantity'] . ", Min: " . $product['min_stock_level'] . ")\n";
        }
    } else {
        echo "Products table does not exist\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>