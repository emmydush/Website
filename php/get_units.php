<?php
require_once 'db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM units ORDER BY created_at DESC");
    $stmt->execute();
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $units]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
