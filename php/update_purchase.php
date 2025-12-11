<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once 'db_connect.php';

try {
    $id = intval($_POST['id']);
    $supplier_id = intval($_POST['supplier_id']);
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $unit_price = floatval($_POST['unit_price']);
    $total_amount = floatval($_POST['total_amount']);
    $purchase_date = trim($_POST['purchase_date']);
    $notes = trim($_POST['notes']);
    
    // Validation
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid purchase ID']);
        exit();
    }
    
    if ($supplier_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier is required']);
        exit();
    }
    
    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Product is required']);
        exit();
    }
    
    if ($quantity <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Quantity must be greater than zero']);
        exit();
    }
    
    if ($unit_price <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Unit price must be greater than zero']);
        exit();
    }
    
    if (empty($purchase_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Purchase date is required']);
        exit();
    }
    
    // Check if purchase exists
    $stmt = $pdo->prepare("SELECT id, status FROM purchases WHERE id = ?");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase) {
        echo json_encode(['status' => 'error', 'message' => 'Purchase not found']);
        exit();
    }
    
    // Check if purchase is already received
    if ($purchase['status'] == 'received') {
        echo json_encode(['status' => 'error', 'message' => 'Cannot edit a received purchase']);
        exit();
    }
    
    // Check if supplier exists
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Selected supplier not found']);
        exit();
    }
    
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Selected product not found']);
        exit();
    }
    
    // Update purchase
    $stmt = $pdo->prepare("
        UPDATE purchases 
        SET supplier_id = ?, product_id = ?, quantity = ?, unit_price = ?, total_amount = ?, purchase_date = ?, notes = ? 
        WHERE id = ?
    ");
    $stmt->execute([$supplier_id, $product_id, $quantity, $unit_price, $total_amount, $purchase_date, $notes, $id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Purchase updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>