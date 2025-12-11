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
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $expense_date = trim($_POST['expense_date']);
    $category = trim($_POST['category']);
    $notes = trim($_POST['notes']);
    
    // Validation
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
    
    // Insert new expense
    $stmt = $pdo->prepare("
        INSERT INTO expenses (description, amount, expense_date, category, notes) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$description, $amount, $expense_date, $category, $notes]);
    
    $expense_id = $pdo->lastInsertId();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Expense added successfully',
        'expense_id' => $expense_id
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>