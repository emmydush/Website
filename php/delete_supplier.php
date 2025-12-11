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
    
    // Validation
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid supplier ID']);
        exit();
    }
    
    // Check if supplier exists
    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier not found']);
        exit();
    }
    
    // Check if supplier is referenced in products
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE supplier_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($productCount > 0) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Cannot delete supplier. It is referenced by ' . $productCount . ' product(s).'
        ]);
        exit();
    }
    
    // Delete supplier
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Supplier deleted successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>