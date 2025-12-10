<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Validate input
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
        exit;
    }
    
    try {
        // Get product with category and supplier information
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, s.name as supplier_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            echo json_encode(['status' => 'success', 'data' => $product]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>