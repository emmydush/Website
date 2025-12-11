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
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT id, name, contact_person, email, phone 
            FROM suppliers 
            WHERE name LIKE ? OR contact_person LIKE ? OR email LIKE ? OR phone LIKE ?
            ORDER BY name
        ");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id, name, contact_person, email, phone 
            FROM suppliers 
            ORDER BY name
        ");
        $stmt->execute();
    }
    
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'suppliers' => $suppliers
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>