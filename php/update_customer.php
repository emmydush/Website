<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? '';
    $customer_type = $_POST['customer_type'] ?? 'retail';
    $status = $_POST['status'] ?? 'active';
    
    if (empty($id) || empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer ID and name are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE customers 
            SET name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ?, country = ?, customer_type = ?, status = ? 
            WHERE id = ?
        ");
        
        if ($stmt->execute([$name, $email, $phone, $address, $city, $state, $postal_code, $country, $customer_type, $status, $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update customer']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
