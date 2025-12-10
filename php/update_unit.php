<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $symbol = $_POST['symbol'] ?? '';
    
    if (empty($id) || empty($name) || empty($symbol)) {
        echo json_encode(['status' => 'error', 'message' => 'Unit ID, name, and symbol are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE units SET name = ?, symbol = ? WHERE id = ?
        ");
        
        if ($stmt->execute([$name, $symbol, $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Unit updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update unit']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
