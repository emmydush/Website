<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer name is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, email, phone, address) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$name, $email, $phone, $address])) {
            $customer_id = $pdo->lastInsertId();
            echo json_encode(['status' => 'success', 'message' => 'Customer added successfully', 'customer_id' => $customer_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add customer']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
