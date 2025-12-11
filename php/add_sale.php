<?php
require_once 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$product_id = $_POST['product_id'] ?? null;
$quantity_sold = $_POST['quantity_sold'] ?? null;
$sale_price = $_POST['sale_price'] ?? null;
$total_amount = $_POST['total_amount'] ?? null;

// Validate data
if (!$product_id || !$quantity_sold || !$sale_price || !$total_amount) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert sale record
    $stmt = $pdo->prepare("
        INSERT INTO sales (product_id, quantity_sold, sale_price, total_amount, sold_by) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$product_id, $quantity_sold, $sale_price, $total_amount, $_SESSION['user_id']]);
    
    // Update product quantity
    $stmt = $pdo->prepare("
        UPDATE products 
        SET quantity = quantity - ? 
        WHERE id = ?
    ");
    $stmt->execute([$quantity_sold, $product_id]);
    
    // Insert transaction record
    $stmt = $pdo->prepare("
        INSERT INTO transactions (product_id, type, quantity, reason) 
        VALUES (?, 'out', ?, 'Sale')
    ");
    $stmt->execute([$product_id, $quantity_sold]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Sale recorded successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>