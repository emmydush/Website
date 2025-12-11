<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($id) || empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Category ID and name are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        
        if ($stmt->execute([$name, $description, $id])) {
            echo json_encode(['status' => 'success', 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update category']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
