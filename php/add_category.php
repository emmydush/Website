<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Category name is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, description) 
            VALUES (?, ?)
        ");
        
        if ($stmt->execute([$name, $description])) {
            $category_id = $pdo->lastInsertId();
            echo json_encode(['status' => 'success', 'message' => 'Category added successfully', 'category_id' => $category_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add category']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
