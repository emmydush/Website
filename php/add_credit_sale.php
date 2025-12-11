<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $total_amount = $_POST['total_amount'] ?? '';
    $amount_paid = $_POST['amount_paid'] ?? 0;
    $due_date = $_POST['due_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($customer_id) || empty($total_amount)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer ID and total amount are required']);
        exit;
    }
    
    $balance_due = $total_amount - $amount_paid;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO credit_sales (customer_id, total_amount, amount_paid, balance_due, due_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$customer_id, $total_amount, $amount_paid, $balance_due, $due_date, $notes])) {
            $sale_id = $pdo->lastInsertId();
            echo json_encode(['status' => 'success', 'message' => 'Credit sale added successfully', 'sale_id' => $sale_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add credit sale']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
