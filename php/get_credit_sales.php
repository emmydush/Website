<?php
require_once 'db_connect.php';

try {
    $stmt = $pdo->prepare("
        SELECT cs.*, c.name as customer_name, c.email, c.phone 
        FROM credit_sales cs 
        JOIN customers c ON cs.customer_id = c.id 
        ORDER BY cs.created_at DESC
    ");
    $stmt->execute();
    $credit_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $credit_sales]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
