<?php
require_once 'db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM customers ORDER BY created_at DESC");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $customers]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
