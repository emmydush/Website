<?php
require_once 'db_connect.php';

// Get all products with category and supplier information
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, s.name as supplier_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'data' => $products]);
?>