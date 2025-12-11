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
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid supplier ID']);
        exit();
    }
    
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier name is required']);
        exit();
    }
    
    // Check if supplier exists
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier not found']);
        exit();
    }
    
    // Check if another supplier already has this name
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Another supplier with this name already exists']);
        exit();
    }
    
    // Update supplier
    $stmt = $pdo->prepare("
        UPDATE suppliers 
        SET name = ?, contact_person = ?, email = ?, phone = ?, address = ? 
        WHERE id = ?
    ");
    $stmt->execute([$name, $contact_person, $email, $phone, $address, $id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Supplier updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>