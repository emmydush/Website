<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Category ID is required']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if category has products
        $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $check_stmt->execute([$id]);
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete category with associated products']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully']);
        } else {
            $pdo->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete category']);
        }
    } catch (Exception $e) {
        $pdo->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
