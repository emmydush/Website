<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 1);

session_start();
require_once 'php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$userName = $_SESSION['username'] ?? "User";
$userRole = $_SESSION['role'] ?? "Staff";

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
} catch (PDOException $e) {
    error_log("Notification count error: " . $e->getMessage());
    $notificationCount = 0; // Default to 0 if there's an error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/emmanuel/css/modern_dashboard.css">
    <link rel="stylesheet" href="/emmanuel/css/toast.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        .report-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .report-card h3 {
            margin: 0 0 1rem 0;
            color: #333;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .report-card i {
            color: #667eea;
        }
        .report-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .report-label {
            font-size: 0.85rem;
            color: #999;
        }
        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 2rem 0;
            position: relative;
            height: 400px;
        }
        .chart-container h3 {
            margin: 0 0 1.5rem 0;
            color: #333;
        }
        .table-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 2rem 0;
            overflow-x: auto;
        }
        .table-container h3 {
            margin: 0 0 1.5rem 0;
            color: #333;
        }
        .reports-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .reports-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        .reports-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .reports-table tr:hover {
            background: #f9f9f9;
        }
        .export-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        .content-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .section-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.75rem;
        }
        
        /* New styles for report sections */
        .report-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 2rem 0;
        }
        .report-section h2 {
            margin: 0 0 1.5rem 0;
            color: #333;
            font-size: 1.75rem;
        }
        .report-section h3 {
            margin: 0 0 1.5rem 0;
            color: #333;
            font-size: 1.5rem;
        }
        .report-section p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .quick-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .quick-report-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .quick-report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }
        .quick-report-card h3 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .quick-report-card p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .report-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }
        .icon-blue { background: linear-gradient(135deg, #2196f3, #21cbf3); }
        .icon-green { background: linear-gradient(135deg, #4caf50, #8bc34a); }
        .icon-orange { background: linear-gradient(135deg, #ff9800, #ff5722); }
        .icon-purple { background: linear-gradient(135deg, #9c27b0, #673ab7); }
        .icon-red { background: linear-gradient(135deg, #ff5252, #ff4081); }
        .icon-teal { background: linear-gradient(135deg, #00bcd4, #0097a7); }
        .icon-indigo { background: linear-gradient(135deg, #3f51b5, #7986cb); }
        .icon-pink { background: linear-gradient(135deg, #e91e63, #f50057); }
        
        /* Advanced Report Modal Styles */
        .report-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .report-modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .report-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .report-modal-header h2 {
            margin: 0;
            color: #333;
        }
        
        .close-modal {
            font-size: 2rem;
            cursor: pointer;
            color: #999;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .report-modal-body {
            padding: 1.5rem;
        }
        
        .report-modal-body h3 {
            margin: 1.5rem 0 1rem 0;
            color: #333;
        }
        
        .report-modal-body p {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .report-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: flex-end;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .loading-content {
            text-align: center;
            color: white;
        }
        
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid white;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .report-card h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .report-card .report-value {
            font-size: 1.5rem;
        }
    </style>
    
    <!-- Print Styles -->
    <style media="print">
        .sidebar, .top-nav, .export-btn, .close-modal, .report-actions {
            display: none !important;
        }
        
        .main-content {
            margin-left: 0;
            padding: 0;
        }
        
        .report-modal-content {
            box-shadow: none;
            border-radius: 0;
        }
        
        .report-section {
            box-shadow: none;
            border: 1px solid #ccc;
        }
        
        .report-card {
            box-shadow: none;
            border: 1px solid #eee;
        }
        
        body {
            background: white;
        }
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-left">
            <div class="logo">InventoryPro</div>
        </div>
        <div class="nav-right">
            <div class="date-time" id="currentDateTime"></div>
            <div class="language-selector">
                <select>
                    <option>English</option>
                    <option>Spanish</option>
                    <option>French</option>
                </select>
            </div>
            <div class="notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge"><?php echo $notificationCount; ?></span>
            </div>
            <div class="branch-selector">
                <select>
                    <option>Main Branch</option>
                    <option>Branch 1</option>
                    <option>Branch 2</option>
                </select>
            </div>
            <div class="user-menu" id="userMenu">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff" alt="User" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            
            <!-- Dropdown menu for user actions -->
            <div class="user-dropdown" id="userDropdown">
                <a href="#"><i class="fas fa-user"></i> Profile</a>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Left Sidebar -->
        <aside class="sidebar">
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff&size=64" alt="User" class="profile-image">
                <div class="user-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></h3>
                    <p class="user-role"><?php echo htmlspecialchars($userRole ?? 'Staff'); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="modern_dashboard.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="products.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="categories.php" class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="units.php" class="menu-item">
                    <i class="fas fa-ruler"></i>
                    <span>Units</span>
                </a>
                <a href="suppliers.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span>Suppliers</span>
                </a>
                <a href="purchases.php" class="menu-item">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Purchases</span>
                </a>
                <a href="expenses.php" class="menu-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Expenses</span>
                </a>
                <a href="sales.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
                <a href="pos_system.php" class="menu-item">
                    <i class="fas fa-cash-register"></i>
                    <span>Point of Sale</span>
                </a>
                <a href="credit_sales.php" class="menu-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Credit Sales</span>
                </a>
                <a href="customers.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="reports.php" class="menu-item active">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="logout.php" class="menu-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-section">
                <div class="section-header">
                    <h2>Reports & Analytics</h2>
                    <button class="export-btn" onclick="exportToCSV()"><i class="fas fa-download"></i> Export Report</button>
                </div>
                
                <p>Generate various reports to analyze your business performance.</p>
            </div>

            <!-- Advanced Reports Link -->
            <div class="report-section">
                <h2><i class="fas fa-rocket"></i> Advanced Analytics</h2>
                <p>Access our new advanced analytics platform with predictive modeling and deeper insights.</p>
                
                <div style="text-align: center; margin: 2rem 0;">
                    <a href="advanced_reports.php" class="export-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chart-line"></i> Launch Advanced Reports
                    </a>
                </div>
            </div>

            <!-- Quick Reports Section -->
            <div class="report-section">
                <h2><i class="fas fa-bolt"></i> Quick Reports</h2>
                <p>Quickly generate reports for common time periods:</p>
                
                <div class="quick-reports-grid">
                    <div class="quick-report-card" onclick="generateReport('today')">
                        <h3><span class="report-icon icon-blue"><i class="fas fa-calendar-day"></i></span> Today's Report</h3>
                        <p>View today's sales, inventory changes, and performance metrics</p>
                    </div>
                    <div class="quick-report-card" onclick="generateReport('week')">
                        <h3><span class="report-icon icon-green"><i class="fas fa-calendar-week"></i></span> Weekly Report</h3>
                        <p>Analyze performance trends over the past 7 days</p>
                    </div>
                    <div class="quick-report-card" onclick="generateReport('month')">
                        <h3><span class="report-icon icon-orange"><i class="fas fa-calendar-alt"></i></span> Monthly Report</h3>
                        <p>Comprehensive overview of monthly business performance</p>
                    </div>
                    <div class="quick-report-card" onclick="generateReport('quarter')">
                        <h3><span class="report-icon icon-purple"><i class="fas fa-calendar"></i></span> Quarterly Report</h3>
                        <p>Detailed quarterly analysis with year-over-year comparisons</p>
                    </div>
                </div>
            </div>

            <!-- Multi-Branch Performance Dashboard -->
            <div class="report-section">
                <h2><i class="fas fa-building"></i> Multi-Branch Performance Dashboard</h2>
                <p>Get an overview of performance across all your branches in one place.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-code-branch"></i> Branch 1</h3>
                        <div class="report-value">$12,450</div>
                        <div class="report-label">Monthly Revenue</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-code-branch"></i> Branch 2</h3>
                        <div class="report-value">$9,870</div>
                        <div class="report-label">Monthly Revenue</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-code-branch"></i> Branch 3</h3>
                        <div class="report-value">$15,230</div>
                        <div class="report-label">Monthly Revenue</div>
                    </div>
                </div>
            </div>

            <!-- Sales Report -->
            <div class="report-section">
                <h2><i class="fas fa-chart-line"></i> Sales Report</h2>
                <p>Analyze sales performance, top selling products, and revenue trends.</p>
                
                <div class="reports-grid" id="reportsGrid">
                    <div class="report-card">
                        <h3><i class="fas fa-shopping-cart"></i> Total Sales</h3>
                        <div class="report-value" id="totalSales">0</div>
                        <div class="report-label">Total number of sales</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-dollar-sign"></i> Total Revenue</h3>
                        <div class="report-value" id="totalRevenue">$0.00</div>
                        <div class="report-label">Total sales revenue</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-chart-line"></i> Average Sale</h3>
                        <div class="report-value" id="averageSale">$0.00</div>
                        <div class="report-label">Average sale amount</div>
                    </div>
                </div>

                <div class="chart-container">
                    <h3>Sales Trend (Last 30 Days)</h3>
                    <canvas id="salesChart"></canvas>
                </div>

                <div class="chart-container">
                    <h3>Top 10 Best Selling Products</h3>
                    <canvas id="productsChart"></canvas>
                </div>

                <div class="table-container">
                    <h3>Recent Sales by Date</h3>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Number of Sales</th>
                                <th>Daily Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="salesByDateTable">
                            <tr>
                                <td colspan="3" style="text-align: center; color: #999;">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-container">
                    <h3>Top 10 Best Selling Products</h3>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="topProductsTable">
                            <tr>
                                <td colspan="3" style="text-align: center; color: #999;">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory Report -->
            <div class="report-section">
                <h2><i class="fas fa-boxes"></i> Inventory Report</h2>
                <p>Track stock levels, low stock alerts, and expiry information.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-boxes"></i> Total Products</h3>
                        <div class="report-value" id="totalProducts">0</div>
                        <div class="report-label">Total inventory items</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-exclamation-triangle"></i> Out of Stock</h3>
                        <div class="report-value" id="outOfStock">0</div>
                        <div class="report-label">Products with zero stock</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-exclamation-circle"></i> Low Stock</h3>
                        <div class="report-value" id="lowStock">0</div>
                        <div class="report-label">Items below minimum level</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-boxes"></i> Inventory Value</h3>
                        <div class="report-value" id="inventoryValue">$0.00</div>
                        <div class="report-label">Total inventory value</div>
                    </div>
                </div>
            </div>

            <!-- Profit & Loss Report -->
            <div class="report-section">
                <h2><i class="fas fa-file-invoice-dollar"></i> Profit & Loss Report</h2>
                <p>Review financial performance including revenue, costs, and profits.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-dollar-sign"></i> Gross Revenue</h3>
                        <div class="report-value" id="grossRevenue">$0.00</div>
                        <div class="report-label">Total sales revenue</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-money-bill-wave"></i> Cost of Goods</h3>
                        <div class="report-value" id="cogs">$0.00</div>
                        <div class="report-label">Direct product costs</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-percentage"></i> Gross Profit</h3>
                        <div class="report-value" id="grossProfit">$0.00</div>
                        <div class="report-label">Revenue minus COGS</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-chart-pie"></i> Net Profit</h3>
                        <div class="report-value" id="netProfit">$0.00</div>
                        <div class="report-label">After all expenses</div>
                    </div>
                </div>
            </div>

            <!-- Expense Report -->
            <div class="report-section">
                <h2><i class="fas fa-receipt"></i> Expense Report</h2>
                <p>Track and analyze business expenses by category.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-shopping-cart"></i> Purchases</h3>
                        <div class="report-value">$2,450</div>
                        <div class="report-label">Supplier purchases</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-tools"></i> Maintenance</h3>
                        <div class="report-value">$320</div>
                        <div class="report-label">Equipment & repairs</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-lightbulb"></i> Utilities</h3>
                        <div class="report-value">$180</div>
                        <div class="report-label">Electricity, water, etc.</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-users"></i> Salaries</h3>
                        <div class="report-value">$4,200</div>
                        <div class="report-label">Employee compensation</div>
                    </div>
                </div>
            </div>

            <!-- Purchase Report -->
            <div class="report-section">
                <h2><i class="fas fa-truck-loading"></i> Purchase Report</h2>
                <p>Analyze supplier purchases and procurement trends.</p>
                
                <div class="table-container">
                    <h3>Recent Purchases</h3>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2023-06-15</td>
                                <td>Tech Supplies Co.</td>
                                <td>15 items</td>
                                <td>$1,250.00</td>
                                <td><span style="color: #4caf50;">Completed</span></td>
                            </tr>
                            <tr>
                                <td>2023-06-10</td>
                                <td>Office Essentials</td>
                                <td>8 items</td>
                                <td>$680.50</td>
                                <td><span style="color: #4caf50;">Completed</span></td>
                            </tr>
                            <tr>
                                <td>2023-06-05</td>
                                <td>Furniture World</td>
                                <td>3 items</td>
                                <td>$1,850.00</td>
                                <td><span style="color: #ff9800;">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Customer Report -->
            <div class="report-section">
                <h2><i class="fas fa-user-friends"></i> Customer Report</h2>
                <p>Review customer purchasing patterns and loyalty metrics.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-users"></i> Total Customers</h3>
                        <div class="report-value">1,248</div>
                        <div class="report-label">Active customers</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-star"></i> VIP Customers</h3>
                        <div class="report-value">86</div>
                        <div class="report-label">Top spenders</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-chart-line"></i> Repeat Rate</h3>
                        <div class="report-value">64%</div>
                        <div class="report-label">Returning customers</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-heart"></i> Avg. Order Value</h3>
                        <div class="report-value">$87.50</div>
                        <div class="report-label">Per customer purchase</div>
                    </div>
                </div>

                <div class="chart-container">
                    <h3>Customer Acquisition (Last 6 Months)</h3>
                    <canvas id="customerChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        let chartData = {};

        // Initialize with sample data for demonstration
        document.addEventListener('DOMContentLoaded', function() {
            // Sample data for demonstration
            document.getElementById('totalSales').textContent = '142';
            document.getElementById('totalRevenue').textContent = '$' + '24,580.00';
            document.getElementById('averageSale').textContent = '$' + '173.09';
            document.getElementById('totalProducts').textContent = '842';
            document.getElementById('outOfStock').textContent = '12';
            document.getElementById('lowStock').textContent = '34';
            document.getElementById('inventoryValue').textContent = '$' + '42,680.00';
            document.getElementById('grossRevenue').textContent = '$' + '24,580.00';
            document.getElementById('cogs').textContent = '$' + '12,340.00';
            document.getElementById('grossProfit').textContent = '$' + '12,240.00';
            document.getElementById('netProfit').textContent = '$' + '8,760.00';
            
            // Initialize charts with sample data
            initializeSampleCharts();
            generateReport('sales_summary');

            // Notification badge click handler
            const notificationBadge = document.querySelector('.notifications');
            if (notificationBadge) {
                notificationBadge.addEventListener('click', function(e) {
                    e.stopPropagation();
                    loadNotifications();
                });
            }

            // Function to load and display notifications
            function loadNotifications() {
                fetch('php/stock_alerts.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.alerts.length > 0) {
                            // Create notification dropdown
                            createNotificationDropdown(data.alerts);
                        } else {
                            // Show info message if no alerts
                            alert('No new notifications');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading notifications:', error);
                        alert('Failed to load notifications');
                    });
            }

            // Function to create notification dropdown
            function createNotificationDropdown(alerts) {
                // Remove existing dropdown if present
                const existingDropdown = document.querySelector('.notification-dropdown');
                if (existingDropdown) {
                    existingDropdown.remove();
                }
                
                // Create dropdown container
                const dropdown = document.createElement('div');
                dropdown.className = 'notification-dropdown';
                dropdown.style.cssText = `
                    position: absolute;
                    top: 100%;
                    right: -100px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
                    width: 300px;
                    z-index: 1001;
                    margin-top: 10px;
                    max-height: 400px;
                    overflow-y: auto;
                `;
                
                // Create dropdown header
                const header = document.createElement('div');
                header.style.cssText = `
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px;
                    border-bottom: 1px solid #eee;
                    background: #f8f9fa;
                `;
                header.innerHTML = `
                    <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: #333;">Notifications (${alerts.length})</h3>
                    <button class="close-dropdown" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #999; padding: 0; width: 24px; height: 24px;">&times;</button>
                `;
                
                // Create dropdown content
                const content = document.createElement('div');
                content.style.cssText = `
                    max-height: 300px;
                    overflow-y: auto;
                `;
                
                // Add alerts to content
                alerts.forEach(alert => {
                    const alertElement = document.createElement('div');
                    alertElement.style.cssText = `
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        padding: 12px 15px;
                        border-bottom: 1px solid #eee;
                        font-size: 13px;
                        line-height: 1.4;
                    `;
                    
                    // Set background based on alert type
                    if (alert.type === 'out_of_stock') {
                        alertElement.style.background = '#ffebee';
                    } else if (alert.type === 'critical_low') {
                        alertElement.style.background = '#fff3e0';
                    } else {
                        alertElement.style.background = '#e8f5e9';
                    }
                    
                    // Set icon based on alert type
                    let iconClass = '';
                    let iconColor = '';
                    if (alert.type === 'out_of_stock') {
                        iconClass = 'fas fa-times-circle';
                        iconColor = '#f44336';
                    } else if (alert.type === 'critical_low') {
                        iconClass = 'fas fa-exclamation-triangle';
                        iconColor = '#ff9800';
                    } else {
                        iconClass = 'fas fa-exclamation-circle';
                        iconColor = '#4caf50';
                    }
                    
                    alertElement.innerHTML = `
                        <i class="${iconClass}" style="font-size: 16px; margin-top: 2px; color: ${iconColor};"></i>
                        <div style="flex: 1; color: #333;">${alert.message}</div>
                    `;
                    
                    content.appendChild(alertElement);
                });
                
                // Add header and content to dropdown
                dropdown.appendChild(header);
                dropdown.appendChild(content);
                
                // Add dropdown to notifications container
                const notificationsContainer = document.querySelector('.notifications');
                notificationsContainer.appendChild(dropdown);
                
                // Add close button event
                const closeBtn = dropdown.querySelector('.close-dropdown');
                closeBtn.addEventListener('click', function() {
                    dropdown.remove();
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function closeDropdown(e) {
                    if (!notificationsContainer.contains(e.target)) {
                        dropdown.remove();
                        document.removeEventListener('click', closeDropdown);
                    }
                });
            }
        });

        async function loadReports() {
            try {
                const response = await fetch('php/get_reports_data.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    chartData = data.data;
                    updateReportCards();
                    initializeCharts();
                }
            } catch (error) {
                console.error('Error loading reports:', error);
            }
        }

        function updateReportCards() {
            const stats = chartData.sales_summary;
            const inv = chartData.inventory_status;
            const credit = chartData.credit_sales_summary;
            
            document.getElementById('totalSales').textContent = stats.total_sales;
            document.getElementById('totalRevenue').textContent = '$' + stats.total_revenue;
            document.getElementById('averageSale').textContent = '$' + stats.average_sale;
            document.getElementById('totalProducts').textContent = inv.total_products;
            document.getElementById('outOfStock').textContent = inv.out_of_stock;
            document.getElementById('lowStock').textContent = inv.low_stock;
            document.getElementById('inventoryValue').textContent = '$' + inv.total_inventory_value;
            // Profit & Loss values would need additional backend data
        }

        function initializeSampleCharts() {
            // Sales trend chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Jun 1', 'Jun 5', 'Jun 10', 'Jun 15', 'Jun 20', 'Jun 25', 'Jun 30'],
                    datasets: [{
                        label: 'Daily Revenue',
                        data: [1200, 1900, 1500, 2200, 1800, 2400, 2100],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Products chart
            const productsCtx = document.getElementById('productsChart').getContext('2d');
            new Chart(productsCtx, {
                type: 'bar',
                data: {
                    labels: ['Laptop', 'Phone', 'Tablet', 'Monitor', 'Keyboard', 'Mouse', 'Headphones', 'Speaker', 'Camera', 'Printer'],
                    datasets: [{
                        label: 'Units Sold',
                        data: [42, 68, 35, 52, 87, 94, 63, 28, 31, 19],
                        backgroundColor: 'rgba(102, 126, 234, 0.7)',
                        borderColor: '#667eea',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Customer acquisition chart
            const customerCtx = document.getElementById('customerChart').getContext('2d');
            new Chart(customerCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Customers',
                        data: [42, 56, 48, 63, 72, 68],
                        borderColor: '#4caf50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Populate tables with sample data
            populateSampleTables();
        }

        function initializeCharts() {
            const salesByDate = chartData.sales_by_date;
            const topProducts = chartData.top_products;
            
            const dates = salesByDate.map(s => s.sale_date);
            const revenues = salesByDate.map(s => parseFloat(s.daily_revenue));
            
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Daily Revenue',
                        data: revenues,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            const productNames = topProducts.map(p => p.name);
            const productRevenues = topProducts.map(p => parseFloat(p.revenue));
            
            const productsCtx = document.getElementById('productsChart').getContext('2d');
            new Chart(productsCtx, {
                type: 'bar',
                data: {
                    labels: productNames,
                    datasets: [{
                        label: 'Revenue',
                        data: productRevenues,
                        backgroundColor: 'rgba(102, 126, 234, 0.7)',
                        borderColor: '#667eea',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            populateSalesByDateTable();
            populateTopProductsTable();
        }

        function populateSampleTables() {
            // Sample data for sales table
            const salesTable = document.getElementById('salesByDateTable');
            salesTable.innerHTML = `
                <tr>
                    <td>2023-06-30</td>
                    <td>24</td>
                    <td>$2,100.00</td>
                </tr>
                <tr>
                    <td>2023-06-29</td>
                    <td>18</td>
                    <td>$1,650.00</td>
                </tr>
                <tr>
                    <td>2023-06-28</td>
                    <td>22</td>
                    <td>$1,980.00</td>
                </tr>
                <tr>
                    <td>2023-06-27</td>
                    <td>15</td>
                    <td>$1,320.00</td>
                </tr>
                <tr>
                    <td>2023-06-26</td>
                    <td>27</td>
                    <td>$2,450.00</td>
                </tr>`;

            // Sample data for products table
            const productsTable = document.getElementById('topProductsTable');
            productsTable.innerHTML = `
                <tr>
                    <td>Wireless Mouse</td>
                    <td>94</td>
                    <td>$1,880.00</td>
                </tr>
                <tr>
                    <td>Mechanical Keyboard</td>
                    <td>87</td>
                    <td>$4,350.00</td>
                </tr>
                <tr>
                    <td>Smartphone XYZ</td>
                    <td>68</td>
                    <td>$34,000.00</td>
                </tr>
                <tr>
                    <td>Laptop Pro 15"</td>
                    <td>42</td>
                    <td>$63,000.00</td>
                </tr>
                <tr>
                    <td>4K Monitor</td>
                    <td>52</td>
                    <td>$15,600.00</td>
                </tr>`;
        }

        function populateSalesByDateTable() {
            const tbody = document.getElementById('salesByDateTable');
            let html = '';
            
            chartData.sales_by_date.forEach(item => {
                html += `<tr>
                    <td>${item.sale_date}</td>
                    <td>${item.sales_count}</td>
                    <td>$${parseFloat(item.daily_revenue).toFixed(2)}</td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }

        function populateTopProductsTable() {
            const tbody = document.getElementById('topProductsTable');
            let html = '';
            
            chartData.top_products.slice(0, 10).forEach(item => {
                html += `<tr>
                    <td>${item.name}</td>
                    <td>${item.total_sold}</td>
                    <td>$${parseFloat(item.revenue).toFixed(2)}</td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
        }

        function exportToCSV() {
            const csv = [];
            const headers = ['Date', 'Sales Count', 'Revenue'];
            csv.push(headers.join(','));
            
            // Use sample data if no chart data is available
            if (chartData && chartData.sales_by_date) {
                chartData.sales_by_date.forEach(item => {
                    csv.push([item.sale_date, item.sales_count, item.daily_revenue].join(','));
                });
            } else {
                // Sample data
                csv.push(['2023-06-30', '24', '2100'].join(','));
                csv.push(['2023-06-29', '18', '1650'].join(','));
                csv.push(['2023-06-28', '22', '1980'].join(','));
            }
            
            const csvContent = csv.join('\n');
            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent);
            link.download = 'sales_report_' + new Date().toISOString().split('T')[0] + '.csv';
            link.click();
        }

        function generateReport(type) {
            let reportTitle = '';
            let reportDescription = '';
            let dateRange = '';
            
            switch(type) {
                case 'today':
                    reportTitle = "Today's Report";
                    reportDescription = "View today's sales, inventory changes, and performance metrics";
                    dateRange = "today";
                    break;
                case 'week':
                    reportTitle = "Weekly Report";
                    reportDescription = "Analyze performance trends over the past 7 days";
                    dateRange = "week";
                    break;
                case 'month':
                    reportTitle = "Monthly Report";
                    reportDescription = "Comprehensive overview of monthly business performance";
                    dateRange = "month";
                    break;
                case 'quarter':
                    reportTitle = "Quarterly Report";
                    reportDescription = "Detailed quarterly analysis with year-over-year comparisons";
                    dateRange = "quarter";
                    break;
                default:
                    reportTitle = "Custom Report";
                    reportDescription = "Generated report based on your selection";
                    dateRange = "custom";
            }
            
            // Show loading indicator
            showLoadingIndicator(reportTitle);
            
            // Fetch report data based on type
            fetchReportData(dateRange).then(data => {
                hideLoadingIndicator();
                displayAdvancedReport(reportTitle, reportDescription, data);
            }).catch(error => {
                hideLoadingIndicator();
                showError('Failed to generate report: ' + error.message);
            });
        }
        
        function fetchReportData(dateRange) {
            // In a real implementation, this would fetch data from the server
            // For demonstration, we'll return mock data with a delay
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    const mockData = {
                        sales: {
                            total: Math.floor(Math.random() * 100) + 50,
                            revenue: (Math.random() * 10000 + 5000).toFixed(2),
                            average: (Math.random() * 200 + 50).toFixed(2)
                        },
                        inventory: {
                            totalProducts: Math.floor(Math.random() * 500) + 300,
                            outOfStock: Math.floor(Math.random() * 20),
                            lowStock: Math.floor(Math.random() * 50) + 10
                        },
                        customers: {
                            new: Math.floor(Math.random() * 30),
                            returning: Math.floor(Math.random() * 100) + 50
                        }
                    };
                    resolve(mockData);
                }, 1500);
            });
        }
        
        function displayAdvancedReport(title, description, data) {
            // Create modal for advanced report display
            const modal = document.createElement('div');
            modal.className = 'report-modal';
            modal.innerHTML = `
                <div class="report-modal-content">
                    <div class="report-modal-header">
                        <h2>${title}</h2>
                        <span class="close-modal" onclick="closeReportModal()">&times;</span>
                    </div>
                    <div class="report-modal-body">
                        <p>${description}</p>
                        
                        <div class="report-section">
                            <h3>Sales Metrics</h3>
                            <div class="reports-grid">
                                <div class="report-card">
                                    <h4><i class="fas fa-shopping-cart"></i> Total Sales</h4>
                                    <div class="report-value">${data.sales.total}</div>
                                </div>
                                <div class="report-card">
                                    <h4><i class="fas fa-dollar-sign"></i> Revenue</h4>
                                    <div class="report-value">$${data.sales.revenue}</div>
                                </div>
                                <div class="report-card">
                                    <h4><i class="fas fa-chart-line"></i> Average Sale</h4>
                                    <div class="report-value">$${data.sales.average}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-section">
                            <h3>Inventory Status</h3>
                            <div class="reports-grid">
                                <div class="report-card">
                                    <h4><i class="fas fa-boxes"></i> Total Products</h4>
                                    <div class="report-value">${data.inventory.totalProducts}</div>
                                </div>
                                <div class="report-card">
                                    <h4><i class="fas fa-exclamation-triangle"></i> Out of Stock</h4>
                                    <div class="report-value">${data.inventory.outOfStock}</div>
                                </div>
                                <div class="report-card">
                                    <h4><i class="fas fa-exclamation-circle"></i> Low Stock</h4>
                                    <div class="report-value">${data.inventory.lowStock}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-section">
                            <h3>Customer Insights</h3>
                            <div class="reports-grid">
                                <div class="report-card">
                                    <h4><i class="fas fa-user-plus"></i> New Customers</h4>
                                    <div class="report-value">${data.customers.new}</div>
                                </div>
                                <div class="report-card">
                                    <h4><i class="fas fa-users"></i> Returning Customers</h4>
                                    <div class="report-value">${data.customers.returning}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="report-actions">
                            <button class="export-btn" onclick="exportReport('${title}')"><i class="fas fa-download"></i> Export Report</button>
                            <button class="export-btn" onclick="printReport()"><i class="fas fa-print"></i> Print Report</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
        }
        
        function showLoadingIndicator(title) {
            const loader = document.createElement('div');
            loader.id = 'reportLoader';
            loader.className = 'loading-overlay';
            loader.innerHTML = `
                <div class="loading-content">
                    <div class="spinner"></div>
                    <p>Generating ${title}...</p>
                </div>
            `;
            document.body.appendChild(loader);
            document.body.style.overflow = 'hidden';
        }
        
        function hideLoadingIndicator() {
            const loader = document.getElementById('reportLoader');
            if (loader) {
                loader.remove();
                document.body.style.overflow = 'auto';
            }
        }
        
        function closeReportModal() {
            const modal = document.querySelector('.report-modal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }
        
        function exportReport(title) {
            alert(`Exporting ${title} as CSV file...`);
            // In a real implementation, this would generate and download a CSV file
        }
        
        function printReport() {
            window.print();
        }
        
        function showError(message) {
            alert('Error: ' + message);
        }

        // Load actual reports data
        loadReports();
    </script>
    <script src="/emmanuel/js/toast.js"></script>
</body>
</html>
