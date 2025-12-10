<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $min_stock_level = $_POST['min_stock_level'];
    $barcode = $_POST['barcode'];
    
    // Validate input
    if (empty($name) || empty($price) || empty($quantity)) {
        echo json_encode(['status' => 'error', 'message' => 'Name, price, and quantity are required']);
        exit;
    }
    
    // Insert product into database
    $stmt = $pdo->prepare("
        INSERT INTO products (name, description, category_id, supplier_id, price, quantity, min_stock_level, barcode) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$name, $description, $category_id, $supplier_id, $price, $quantity, $min_stock_level, $barcode])) {
        $product_id = $pdo->lastInsertId();
        
        // Log transaction
        $transaction_stmt = $pdo->prepare("INSERT INTO transactions (product_id, type, quantity, reason) VALUES (?, 'in', ?, 'Initial stock')");
        $transaction_stmt->execute([$product_id, $quantity]);
        
        echo json_encode(['status' => 'success', 'message' => 'Product added successfully', 'product_id' => $product_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add product']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>