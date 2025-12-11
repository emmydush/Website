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
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier name is required']);
        exit();
    }
    
    // Check if supplier already exists
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier with this name already exists']);
        exit();
    }
    
    // Insert new supplier
    $stmt = $pdo->prepare("
        INSERT INTO suppliers (name, contact_person, email, phone, address) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $contact_person, $email, $phone, $address]);
    
    $supplier_id = $pdo->lastInsertId();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Supplier added successfully',
        'supplier_id' => $supplier_id
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>