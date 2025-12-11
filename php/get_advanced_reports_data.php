<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Get comprehensive sales data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_sales,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(AVG(total_amount), 0) as average_sale,
            MIN(created_at) as first_sale_date,
            MAX(created_at) as last_sale_date
        FROM sales
    ");
    $stmt->execute();
    $sales_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get top selling products with more details
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            c.name as category_name,
            COALESCE(SUM(s.quantity_sold), 0) as total_sold,
            COALESCE(SUM(s.total_amount), 0) as revenue,
            COALESCE(AVG(s.sale_price), 0) as avg_selling_price,
            p.price as current_price
        FROM products p
        LEFT JOIN sales s ON p.id = s.product_id
        LEFT JOIN categories c ON p.category_id = c.id
        GROUP BY p.id, p.name, c.name, p.price
        ORDER BY total_sold DESC
        LIMIT 15
    ");
    $stmt->execute();
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get sales by date (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as sale_date,
            COUNT(*) as sales_count,
            COALESCE(SUM(total_amount), 0) as daily_revenue,
            COALESCE(AVG(total_amount), 0) as avg_daily_sale
        FROM sales
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY sale_date DESC
    ");
    $stmt->execute();
    $sales_by_date = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get inventory status with detailed breakdown
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN quantity <= min_stock_level AND quantity > 0 THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN quantity > min_stock_level THEN 1 ELSE 0 END) as adequate_stock,
            COALESCE(SUM(price * quantity), 0) as total_inventory_value,
            COALESCE(AVG(quantity), 0) as avg_stock_level
        FROM products
    ");
    $stmt->execute();
    $inventory_status = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get credit sales summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_credit_sales,
            COALESCE(SUM(total_amount), 0) as total_credit_amount,
            COALESCE(SUM(balance_due), 0) as total_outstanding,
            COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_accounts
        FROM credit_sales
    ");
    $stmt->execute();
    $credit_sales_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get expenses summary with categorization
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_expenses,
            COALESCE(SUM(amount), 0) as total_expense_amount,
            category,
            COUNT(*) as category_count
        FROM expenses
        GROUP BY category
        ORDER BY total_expense_amount DESC
    ");
    $stmt->execute();
    $expenses_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get purchases summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_purchases,
            COALESCE(SUM(total_amount), 0) as total_purchase_amount,
            COUNT(CASE WHEN status = 'received' THEN 1 END) as received_purchases,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_purchases
        FROM purchases
    ");
    $stmt->execute();
    $purchases_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get customer summary
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_customers,
            SUM(CASE WHEN balance > 0 THEN 1 ELSE 0 END) as customers_with_balance,
            SUM(CASE WHEN customer_type = 'wholesale' THEN 1 ELSE 0 END) as wholesale_customers,
            COALESCE(SUM(balance), 0) as total_customer_balance
        FROM customers
    ");
    $stmt->execute();
    $customers_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sales by category with performance metrics
    $stmt = $pdo->prepare("
        SELECT 
            c.name as category_name,
            COUNT(s.id) as sales_count,
            COALESCE(SUM(s.total_amount), 0) as category_revenue,
            COALESCE(AVG(s.total_amount), 0) as avg_sale_per_category,
            COUNT(DISTINCT s.product_id) as products_sold_in_category
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
            COALESCE(SUM(total_amount), 0) as monthly_revenue,
            COALESCE(AVG(total_amount), 0) as avg_monthly_sale
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
            COALESCE(SUM(p.quantity), 0) as total_stock,
            COALESCE(AVG(p.price), 0) as avg_product_price
        FROM suppliers s
        LEFT JOIN products p ON s.id = p.supplier_id
        GROUP BY s.id, s.name
        ORDER BY total_stock DESC
        LIMIT 15
    ");
    $stmt->execute();
    $supplier_performance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate profit and loss data with more precision
    $total_revenue = $sales_summary['total_revenue'];
    
    // Calculate COGS more accurately based on product prices and quantities sold
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(p.price * s.quantity_sold), 0) as total_cogs
        FROM sales s
        JOIN products p ON s.product_id = p.id
    ");
    $stmt->execute();
    $cogs_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $cogs = $cogs_data['total_cogs'];
    
    // If we don't have COGS data, estimate at 60% of revenue
    if ($cogs == 0) {
        $cogs_percentage = 0.6;
        $cogs = $total_revenue * $cogs_percentage;
    }
    
    $gross_profit = $total_revenue - $cogs;
    $total_expenses = array_sum(array_column($expenses_by_category, 'total_expense_amount'));
    $net_profit = $gross_profit - $total_expenses;
    
    // Calculate additional financial metrics
    $profit_margin = $total_revenue > 0 ? ($net_profit / $total_revenue) * 100 : 0;
    $gross_profit_margin = $total_revenue > 0 ? ($gross_profit / $total_revenue) * 100 : 0;
    
    $profit_loss_data = [
        'gross_revenue' => $total_revenue,
        'cogs' => $cogs,
        'gross_profit' => $gross_profit,
        'gross_profit_margin' => $gross_profit_margin,
        'operating_expenses' => $total_expenses,
        'net_profit' => $net_profit,
        'profit_margin' => $profit_margin
    ];

    // Get top customers by spending
    $stmt = $pdo->prepare("
        SELECT 
            c.name as customer_name,
            COUNT(cs.id) as purchases_count,
            COALESCE(SUM(cs.total_amount), 0) as total_spent
        FROM customers c
        LEFT JOIN credit_sales cs ON c.id = cs.customer_id
        GROUP BY c.id, c.name
        ORDER BY total_spent DESC
        LIMIT 10
    ");
    $stmt->execute();
    $top_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate inventory turnover ratio
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(quantity_sold), 0) as total_sold,
            COALESCE(SUM(p.quantity), 0) as avg_inventory
        FROM products p
        LEFT JOIN (
            SELECT product_id, SUM(quantity_sold) as quantity_sold
            FROM sales
            GROUP BY product_id
        ) s ON p.id = s.product_id
    ");
    $stmt->execute();
    $inventory_turnover_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $avg_inventory = $inventory_turnover_data['avg_inventory'] > 0 ? $inventory_turnover_data['avg_inventory'] : 1;
    $inventory_turnover_ratio = $avg_inventory > 0 ? $inventory_turnover_data['total_sold'] / $avg_inventory : 0;

    $performance_metrics = [
        'inventory_turnover' => $inventory_turnover_ratio,
        'customer_retention_rate' => $customers_summary['total_customers'] > 0 ? 
            (($customers_summary['total_customers'] - $customers_summary['wholesale_customers']) / $customers_summary['total_customers']) * 100 : 0
    ];

    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'sales_summary' => $sales_summary,
            'top_products' => $top_products,
            'sales_by_date' => $sales_by_date,
            'inventory_status' => $inventory_status,
            'credit_sales_summary' => $credit_sales_summary,
            'expenses_by_category' => $expenses_by_category,
            'purchases_summary' => $purchases_summary,
            'customers_summary' => $customers_summary,
            'sales_by_category' => $sales_by_category,
            'monthly_sales_trend' => $monthly_sales_trend,
            'supplier_performance' => $supplier_performance,
            'profit_loss_data' => $profit_loss_data,
            'top_customers' => $top_customers,
            'performance_metrics' => $performance_metrics
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