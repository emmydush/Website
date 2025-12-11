<?php
// This script adds the avatar column to the users table if it doesn't exist
require_once 'db_connect.php';

try {
    // Check if avatar column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'avatar'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add avatar column
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255)");
        echo "✓ Avatar column added to users table<br>";
    } else {
        echo "✓ Avatar column already exists in users table<br>";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}
?>
