<?php
require_once 'db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$userId = intval($_SESSION['user_id']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get current profile information
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, avatar, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

if ($method === 'POST') {
    // Update profile information
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['email'])) {
        echo json_encode(['status' => 'error', 'message' => 'Email is required']);
        exit();
    }
    
    $email = trim($data['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }
    
    try {
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already in use']);
            exit();
        }
        
        // Update email
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $userId]);
        
        // Fetch updated user info
        $stmt = $pdo->prepare("SELECT id, username, email, avatar, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully', 'user' => $user]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
?>
