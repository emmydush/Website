<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $abbreviation = $_POST['abbreviation'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($name) || empty($abbreviation)) {
        echo json_encode(['status' => 'error', 'message' => 'Unit name and abbreviation are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO units (name, abbreviation, description) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$name, $abbreviation, $description])) {
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
