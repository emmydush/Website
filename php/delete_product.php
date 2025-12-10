<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    
    // Validate input
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
        exit;
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Delete related transactions
        $transaction_stmt = $pdo->prepare("DELETE FROM transactions WHERE product_id = ?");
        $transaction_stmt->execute([$id]);
        
        // Delete related sales
        $sales_stmt = $pdo->prepare("DELETE FROM sales WHERE product_id = ?");
        $sales_stmt->execute([$id]);
        
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete product: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>