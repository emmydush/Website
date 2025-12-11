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

    // Get expenses summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_expenses,
            COALESCE(SUM(amount), 0) as total_expense_amount
        FROM expenses
    ");
    $stmt->execute();
    $expenses_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get purchases summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_purchases,
            COALESCE(SUM(total_amount), 0) as total_purchase_amount
        FROM purchases
    ");
    $stmt->execute();
    $purchases_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get customer summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_customers,
            SUM(CASE WHEN balance > 0 THEN 1 ELSE 0 END) as customers_with_balance
        FROM customers
    ");
    $stmt->execute();
    $customers_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sales by category
    $stmt = $pdo->prepare("
        SELECT 
            c.name as category_name,
            COUNT(s.id) as sales_count,
            COALESCE(SUM(s.total_amount), 0) as category_revenue
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        LEFT JOIN sales s ON p.id = s.product_id
        GROUP BY c.id, c.name
        ORDER BY category_revenue DESC
    ");
    $stmt->execute();
    $sales_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get monthly sales trend (last 12 months)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as sales_count,
            COALESCE(SUM(total_amount), 0) as monthly_revenue
        FROM sales
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute();
    $monthly_sales_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get supplier performance
    $stmt = $pdo->prepare("
        SELECT 
            s.name as supplier_name,
            COUNT(p.id) as products_supplied,
            COALESCE(SUM(p.quantity), 0) as total_stock
        FROM suppliers s
        LEFT JOIN products p ON s.id = p.supplier_id
        GROUP BY s.id, s.name
        ORDER BY total_stock DESC
        LIMIT 10
    ");
    $stmt->execute();
    $supplier_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate profit and loss data
    // Assuming COGS is 60% of sales revenue for calculation purposes
    $total_revenue = $sales_summary['total_revenue'];
    $cogs_percentage = 0.6; // 60% cost of goods sold
    $cogs = $total_revenue * $cogs_percentage;
    $gross_profit = $total_revenue - $cogs;
    $total_expenses = $expenses_summary['total_expense_amount'];
    $net_profit = $gross_profit - $total_expenses;

    $profit_loss_data = [
        'gross_revenue' => $total_revenue,
        'cogs' => $cogs,
        'gross_profit' => $gross_profit,
        'operating_expenses' => $total_expenses,
        'net_profit' => $net_profit
    ];

    $response = [
        'status' => 'success',
        'data' => [
            'sales_summary' => $sales_summary,
            'top_products' => $top_products,
            'sales_by_date' => $sales_by_date,
            'inventory_status' => $inventory_status,
            'credit_sales_summary' => $credit_sales_summary,
            'expenses_summary' => $expenses_summary,
            'purchases_summary' => $purchases_summary,
            'customers_summary' => $customers_summary,
            'sales_by_category' => $sales_by_category,
            'monthly_sales_trend' => $monthly_sales_trend,
            'supplier_performance' => $supplier_performance,
            'profit_loss_data' => $profit_loss_data
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