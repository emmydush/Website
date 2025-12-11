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
        echo json_encode(['status' => 'error', 'message' => 'Invalid expense ID']);
        exit();
    }
    
    $stmt = $pdo->prepare("
        SELECT id, description, amount, expense_date, category, notes
        FROM expenses 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$expense) {
        echo json_encode(['status' => 'error', 'message' => 'Expense not found']);
        exit();
    }
    
    echo json_encode([
        'status' => 'success',
        'expense' => $expense
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>