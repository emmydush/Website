<?php
require_once 'db_connect.php';

try {
    // Get all sales with product and user information
    $stmt = $pdo->prepare("
        SELECT s.*, p.name as product_name, u.username as sold_by_name
        FROM sales s
        JOIN products p ON s.product_id = p.id
        JOIN users u ON s.sold_by = u.id
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $sales]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>