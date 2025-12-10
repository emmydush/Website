<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($id) || empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer ID and name are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE customers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?
        ");
        
        if ($stmt->execute([$name, $email, $phone, $address, $id])) {
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
