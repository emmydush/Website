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
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid purchase ID']);
        exit();
    }
    
    $stmt = $pdo->prepare("
        SELECT id, supplier_id, product_id, quantity, unit_price, total_amount, purchase_date, status, notes
        FROM purchases 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase) {
        echo json_encode(['status' => 'error', 'message' => 'Purchase not found']);
        exit();
    }
    
    echo json_encode([
        'status' => 'success',
        'purchase' => $purchase
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>