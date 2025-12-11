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
    
    // Validation
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid purchase ID']);
        exit();
    }
    
    // Check if purchase exists
    $stmt = $pdo->prepare("
        SELECT p.id, p.quantity, p.status, p.product_id, pr.name as product_name
        FROM purchases p
        LEFT JOIN products pr ON p.product_id = pr.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase) {
        echo json_encode(['status' => 'error', 'message' => 'Purchase not found']);
        exit();
    }
    
    // Check if purchase is already received
    if ($purchase['status'] == 'received') {
        echo json_encode(['status' => 'error', 'message' => 'Purchase is already received']);
        exit();
    }
    
    // Check if purchase is cancelled
    if ($purchase['status'] == 'cancelled') {
        echo json_encode(['status' => 'error', 'message' => 'Cannot receive a cancelled purchase']);
        exit();
    }
    
    // Update purchase status to received
    $stmt = $pdo->prepare("UPDATE purchases SET status = 'received' WHERE id = ?");
    $stmt->execute([$id]);
    
    // Update product quantity
    $stmt = $pdo->prepare("
        UPDATE products 
        SET quantity = quantity + ? 
        WHERE id = ?
    ");
    $stmt->execute([$purchase['quantity'], $purchase['product_id']]);
    
    // Add transaction record
    $stmt = $pdo->prepare("
        INSERT INTO transactions (product_id, type, quantity, reason) 
        VALUES (?, 'in', ?, ?)
    ");
    $reason = "Purchase received: " . $purchase['product_name'];
    $stmt->execute([$purchase['product_id'], $purchase['quantity'], $reason]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Purchase marked as received and inventory updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>