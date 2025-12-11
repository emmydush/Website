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
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid supplier ID']);
        exit();
    }
    
    $stmt = $pdo->prepare("
        SELECT id, name, contact_person, email, phone, address 
        FROM suppliers 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$supplier) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier not found']);
        exit();
    }
    
    echo json_encode([
        'status' => 'success',
        'supplier' => $supplier
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>