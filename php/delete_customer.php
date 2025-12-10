<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer ID is required']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        if ($stmt->execute([$id])) {
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Customer deleted successfully']);
        } else {
            $pdo->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete customer']);
        }
    } catch (Exception $e) {
        $pdo->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
