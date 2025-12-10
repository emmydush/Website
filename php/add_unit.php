<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $symbol = $_POST['symbol'] ?? '';
    
    if (empty($name) || empty($symbol)) {
        echo json_encode(['status' => 'error', 'message' => 'Unit name and symbol are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO units (name, symbol) 
            VALUES (?, ?)
        ");
        
        if ($stmt->execute([$name, $symbol])) {
            $unit_id = $pdo->lastInsertId();
            echo json_encode(['status' => 'success', 'message' => 'Unit added successfully', 'unit_id' => $unit_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add unit']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
