<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? '';
    $customer_type = $_POST['customer_type'] ?? 'retail';
    
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer name is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, email, phone, address, city, state, postal_code, country, customer_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$name, $email, $phone, $address, $city, $state, $postal_code, $country, $customer_type])) {
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
