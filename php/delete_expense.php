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
        echo json_encode(['status' => 'error', 'message' => 'Invalid expense ID']);
        exit();
    }
    
    // Check if expense exists
    $stmt = $pdo->prepare("SELECT id FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Expense not found']);
        exit();
    }
    
    // Delete expense
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Expense deleted successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>