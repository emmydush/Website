<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
        exit;
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful',
                'role' => $user['role']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>