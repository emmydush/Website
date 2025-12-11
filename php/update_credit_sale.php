<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $amount_paid = $_POST['amount_paid'] ?? '';
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($id) || empty($amount_paid)) {
        echo json_encode(['status' => 'error', 'message' => 'Sale ID and amount paid are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT total_amount FROM credit_sales WHERE id = ?");
        $stmt->execute([$id]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $balance_due = $sale['total_amount'] - $amount_paid;
        
        $stmt = $pdo->prepare("
            UPDATE credit_sales 
            SET amount_paid = ?, balance_due = ?, status = ?, notes = ? 
            WHERE id = ?
        ");
        
        if ($stmt->execute([$amount_paid, $balance_due, $status, $notes, $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Credit sale updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update credit sale']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
