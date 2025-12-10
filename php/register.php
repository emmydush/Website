<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username or email already exists']);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$username, $email, $hashedPassword, $role])) {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>