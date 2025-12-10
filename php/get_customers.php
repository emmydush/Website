<?php
require_once 'db_connect.php';

try {
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(s.id) as total_purchases 
        FROM customers c 
        LEFT JOIN sales s ON c.id = s.customer_id 
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $customers]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
