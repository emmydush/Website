<?php
require_once 'php/db_connect.php';

echo "Testing database connection...\n";

try {
    // Simple query to test connection
    $stmt = $pdo->prepare("SELECT DATABASE() as db_name");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "SUCCESS: Connected to database: " . $result['db_name'] . "\n";
    } else {
        echo "ERROR: Query executed but no result returned\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>