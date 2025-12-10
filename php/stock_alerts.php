<?php
require_once 'db_connect.php';

// Get products with low stock
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.quantity <= p.min_stock_level 
    ORDER BY p.quantity ASC
");
$stmt->execute();
$lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare alerts
$alerts = [];

foreach ($lowStockProducts as $product) {
    if ($product['quantity'] == 0) {
        $alerts[] = [
            'type' => 'out_of_stock',
            'message' => "⚠️ {$product['name']} is out of stock",
            'product_id' => $product['id']
        ];
    } elseif ($product['quantity'] <= $product['min_stock_level'] * 0.3) {
        $alerts[] = [
            'type' => 'critical_low',
            'message' => "⚠️ {$product['name']} reducing abnormally",
            'product_id' => $product['id']
        ];
    } else {
        $alerts[] = [
            'type' => 'low_stock',
            'message' => "⚠️ Low stock – possible missing items for {$product['name']}",
            'product_id' => $product['id']
        ];
    }
}

echo json_encode(['status' => 'success', 'alerts' => $alerts]);
?>