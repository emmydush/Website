<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $abbreviation = $_POST['abbreviation'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($id) || empty($name) || empty($abbreviation)) {
        echo json_encode(['status' => 'error', 'message' => 'Unit ID, name, and abbreviation are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE units SET name = ?, abbreviation = ?, description = ? WHERE id = ?");
        
        if ($stmt->execute([$name, $abbreviation, $description, $id])) {
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
