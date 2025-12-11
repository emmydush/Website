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
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $expense_date = trim($_POST['expense_date']);
    $category = trim($_POST['category']);
    $notes = trim($_POST['notes']);
    
    // Validation
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid expense ID']);
        exit();
    }
    
    if (empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'Description is required']);
        exit();
    }
    
    if ($amount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than zero']);
        exit();
    }
    
    if (empty($expense_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Expense date is required']);
        exit();
    }
    
    // Check if expense exists
    $stmt = $pdo->prepare("SELECT id FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Expense not found']);
        exit();
    }
    
    // Update expense
    $stmt = $pdo->prepare("
        UPDATE expenses 
        SET description = ?, amount = ?, expense_date = ?, category = ?, notes = ? 
        WHERE id = ?
    ");
    $stmt->execute([$description, $amount, $expense_date, $category, $notes, $id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Expense updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>