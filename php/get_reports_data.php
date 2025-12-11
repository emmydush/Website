<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Get sales summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_sales,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(AVG(total_amount), 0) as average_sale
        FROM sales
    ");
    $stmt->execute();
    $sales_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get top selling products
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            COALESCE(SUM(s.quantity_sold), 0) as total_sold,
            COALESCE(SUM(s.total_amount), 0) as revenue
        FROM products p
        LEFT JOIN sales s ON p.id = s.product_id
        GROUP BY p.id, p.name
        ORDER BY total_sold DESC
        LIMIT 10
    ");
    $stmt->execute();
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get sales by date (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as sale_date,
            COUNT(*) as sales_count,
            COALESCE(SUM(total_amount), 0) as daily_revenue
        FROM sales
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY sale_date DESC
    ");
    $stmt->execute();
    $sales_by_date = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get inventory status
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock,
            COALESCE(SUM(price * quantity), 0) as total_inventory_value
        FROM products
    ");
    $stmt->execute();
    $inventory_status = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get credit sales summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_credit_sales,
            COALESCE(SUM(total_amount), 0) as total_credit_amount,
            COALESCE(SUM(balance_due), 0) as total_outstanding
        FROM credit_sales
    ");
    $stmt->execute();
    $credit_sales_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'data' => [
            'sales_summary' => $sales_summary,
            'top_products' => $top_products,
            'sales_by_date' => $sales_by_date,
            'inventory_status' => $inventory_status,
            'credit_sales_summary' => $credit_sales_summary
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
