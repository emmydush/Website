<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Get total products count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get low stock items count
    $stmt = $pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE quantity <= min_stock_level AND quantity > 0");
    $stmt->execute();
    $lowStockItems = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];

    // Get out of stock items count
    $stmt = $pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products WHERE quantity = 0");
    $stmt->execute();
    $outOfStockItems = $stmt->fetch(PDO::FETCH_ASSOC)['out_of_stock'];

    // Get total categories count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categories");
    $stmt->execute();
    $totalCategories = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get today's sales value (assuming sales table exists)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as today_sales 
        FROM sales 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $todaysSales = $stmt->fetch(PDO::FETCH_ASSOC)['today_sales'];

    // Get total stock value
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(price * quantity), 0) as stock_value 
        FROM products
    ");
    $stmt->execute();
    $stockValue = $stmt->fetch(PDO::FETCH_ASSOC)['stock_value'];

    // Get fast-moving items (top 6 products by quantity sold)
    $stmt = $pdo->prepare("
        SELECT p.name, COALESCE(SUM(s.quantity_sold), 0) as units_sold
        FROM products p
        LEFT JOIN sales s ON p.id = s.product_id
        GROUP BY p.id, p.name
        ORDER BY units_sold DESC
        LIMIT 6
    ");
    $stmt->execute();
    $fastMovingItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get category-wise stock distribution
    $stmt = $pdo->prepare("
        SELECT c.name as category, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        GROUP BY c.id, c.name
        ORDER BY product_count DESC
    ");
    $stmt->execute();
    $categoryDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'status' => 'success',
        'stats' => [
            'total_products' => $totalProducts,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'total_categories' => $totalCategories,
            'todays_sales' => number_format($todaysSales, 2),
            'stock_value' => number_format($stockValue, 2)
        ],
        'charts' => [
            'fast_moving_items' => $fastMovingItems,
            'category_distribution' => $categoryDistribution
        ]
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>