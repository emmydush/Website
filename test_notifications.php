<?php
require_once 'php/db_connect.php';

// Fetch real notification count
$notificationCount = 0;
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as alert_count
        FROM products 
        WHERE quantity <= min_stock_level
    ");
    $stmt->execute();
    $notificationCount = $stmt->fetch(PDO::FETCH_ASSOC)['alert_count'];
    echo "Notification count: " . $notificationCount;
} catch (PDOException $e) {
    error_log("Notification count error: " . $e->getMessage());
    echo "Error fetching notification count";
}
?>