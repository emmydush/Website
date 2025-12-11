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
    $stmt = $pdo->prepare("SELECT id, status FROM purchases WHERE id = ?");
    $stmt->execute([$id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase) {
        echo json_encode(['status' => 'error', 'message' => 'Purchase not found']);
        exit();
    }
    
    // Check if purchase is already received
    if ($purchase['status'] == 'received') {
        echo json_encode(['status' => 'error', 'message' => 'Cannot delete a received purchase']);
        exit();
    }
    
    // Delete purchase
    $stmt = $pdo->prepare("DELETE FROM purchases WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Purchase deleted successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>