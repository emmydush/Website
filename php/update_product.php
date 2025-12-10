<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $min_stock_level = $_POST['min_stock_level'];
    $barcode = $_POST['barcode'];
    
    // Validate input
    if (empty($id) || empty($name) || empty($price) || empty($quantity)) {
        echo json_encode(['status' => 'error', 'message' => 'Product ID, name, price, and quantity are required']);
        exit;
    }
    
    // Update product in database
    $stmt = $pdo->prepare("
        UPDATE products SET 
        name = ?, description = ?, category_id = ?, supplier_id = ?, 
        price = ?, quantity = ?, min_stock_level = ?, barcode = ? 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$name, $description, $category_id, $supplier_id, $price, $quantity, $min_stock_level, $barcode, $id])) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update product']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>