<?php
require_once 'php/db_connect.php';

// Check database connection and fetch products directly
try {
    $stmt = $pdo->prepare(
        "SELECT p.*, c.name as category_name, s.name as supplier_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Direct Database Query Result:</h2>";
    echo "<p>Number of products found: " . count($products) . "</p>";
    
    if (count($products) > 0) {
        echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Supplier</th><th>Price</th><th>Quantity</th></tr>";
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($product['id']) . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>" . htmlspecialchars($product['category_name']) . "</td>";
            echo "<td>" . htmlspecialchars($product['supplier_name']) . "</td>";
            echo "<td>$" . htmlspecialchars($product['price']) . "</td>";
            echo "<td>" . htmlspecialchars($product['quantity']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No products found in database.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>