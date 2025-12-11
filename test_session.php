<?php
session_start();

// Test if session is working
if (isset($_SESSION['user_id'])) {
    echo "Session is active<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . ($_SESSION['username'] ?? 'Not set') . "<br>";
    echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
} else {
    echo "No active session<br>";
    // Set a test session
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
    $_SESSION['role'] = 'admin';
    echo "Test session created. Refresh the page to see if it persists.<br>";
}

// Test database connection
require_once 'php/db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT 1");
    $stmt->execute();
    echo "Database connection successful<br>";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}

echo "<a href='/modern_dashboard'>Go to Dashboard</a> | ";
echo "<a href='/login.html'>Go to Login</a>";
?>