<?php
require_once 'php/db_connect.php';

echo "Testing if database and tables exist...\n";

try {
    // Check if database exists and has tables
    $stmt = $pdo->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        echo "SUCCESS: Database 'inventory_management' exists with " . count($tables) . " tables\n";
        echo "Tables found:\n";
        foreach ($tables as $table) {
            echo "- " . $table[0] . "\n";
        }
    } else {
        echo "WARNING: Database exists but has no tables\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>