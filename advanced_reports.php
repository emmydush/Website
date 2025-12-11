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
    <title>Advanced Reports - Inventory Management</title>
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
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
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
        
        /* Filter controls */
        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #333;
        }
        
        .filter-group select, .filter-group input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .top-nav, .export-btn, .close-modal, .report-actions, .filter-controls {
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
span>Home</span>
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
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Basic Reports</span>
                </a>
                <a href="advanced_reports.php" class="menu-item active">
                    <i class="fas fa-chart-line"></i>
                    <span>Advanced Reports</span>
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
                    <h2><i class="fas fa-chart-line"></i> Advanced Analytics & Reports</h2>
                    <button class="export-btn" onclick="exportToCSV()"><i class="fas fa-download"></i> Export Full Report</button>
                </div>
                
                <p>Comprehensive business intelligence with predictive analytics and advanced visualizations.</p>
                
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="reportPeriod">Time Period</label>
                        <select id="reportPeriod">
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="reportType">Report Type</label>
                        <select id="reportType">
                            <option value="all">All Metrics</option>
                            <option value="sales">Sales Performance</option>
                            <option value="inventory">Inventory Analysis</option>
                            <option value="financial">Financial Health</option>
                            <option value="customer">Customer Insights</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="comparePeriod">Compare To</label>
                        <select id="comparePeriod">
                            <option value="none">None</option>
                            <option value="previous">Previous Period</option>
                            <option value="year">Same Period Last Year</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Executive Dashboard -->
            <div class="report-section">
                <h2><i class="fas fa-tachometer-alt"></i> Executive Dashboard</h2>
                <p>Key performance indicators and business health metrics.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-dollar-sign"></i> Total Revenue</h3>
                        <div class="report-value" id="totalRevenue">$0.00</div>
                        <div class="report-label">Lifetime sales revenue</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-chart-line"></i> Net Profit</h3>
                        <div class="report-value" id="netProfit">$0.00</div>
                        <div class="report-label">After all expenses</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-percentage"></i> Profit Margin</h3>
                        <div class="report-value" id="profitMargin">0%</div>
                        <div class="report-label">Net profit percentage</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-boxes"></i> Inventory Value</h3>
                        <div class="report-value" id="inventoryValue">$0.00</div>
                        <div class="report-label">Current stock worth</div>
                    </div>
                </div>
            </div>

            <!-- Advanced Analytics Section -->
            <div class="report-section">
                <h2><i class="fas fa-chart-pie"></i> Advanced Analytics</h2>
                <p>Deep insights into your business performance with predictive analytics.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-sync-alt"></i> Inventory Turnover</h3>
                        <div class="report-value" id="inventoryTurnover">0</div>
                        <div class="report-label">Times per year</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-bullseye"></i> Customer Retention</h3>
                        <div class="report-value" id="customerRetention">0%</div>
                        <div class="report-label">Repeat customer rate</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-chart-line"></i> Growth Rate</h3>
                        <div class="report-value" id="growthRate">0%</div>
                        <div class="report-label">Monthly growth trend</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-tasks"></i> Operational Efficiency</h3>
                        <div class="report-value" id="operationalEfficiency">0%</div>
                        <div class="report-label">Business efficiency score</div>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3><i class="fas fa-chart-bar"></i> Sales by Category</h3>
                    <canvas id="categoryChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h3><i class="fas fa-chart-line"></i> Monthly Sales Trend (Last 12 Months)</h3>
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            <!-- Financial Health Dashboard -->
            <div class="report-section">
                <h2><i class="fas fa-heartbeat"></i> Financial Health Dashboard</h2>
                <p>Real-time assessment of your business financial status.</p>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3><i class="fas fa-coins"></i> Gross Profit</h3>
                        <div class="report-value" id="grossProfit">$0.00</div>
                        <div class="report-label">Revenue minus COGS</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-money-bill-wave"></i> Operating Expenses</h3>
                        <div class="report-value" id="operatingExpenses">$0.00</div>
                        <div class="report-label">Total business expenses</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-balance-scale"></i> Debt Ratio</h3>
                        <div class="report-value" id="debtRatio">0%</div>
                        <div class="report-label">Liabilities vs assets</div>
                    </div>
                    <div class="report-card">
                        <h3><i class="fas fa-chart-area"></i> Cash Flow</h3>
                        <div class="report-value" id="cashFlow">$0.00</div>
                        <div class="report-label">Net cash movement</div>
                    </div>
                </div>
                
                <div class="chart-container">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Profit & Loss Analysis</h3>
                    <canvas id="profitLossChart"></canvas>
                </div>
            </div>

            <!-- Detailed Data Tables -->
            <div class="report-section">
                <h2><i class="fas fa-table"></i> Detailed Analytics</h2>
                <p>Comprehensive breakdown of key business metrics.</p>
                
                <div class="table-container">
                    <h3><i class="fas fa-star"></i> Top Performing Products</h3>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Avg. Price</th>
                            </tr>
                        </thead>
                        <tbody id="topProductsTable">
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999;">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-container">
                    <h3><i class="fas fa-truck"></i> Supplier Performance</h3>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Products Supplied</th>
                                <th>Total Stock Value</th>
                                <th>Avg. Product Price</th>
                            </tr>
                        </thead>
                        <tbody id="supplierPerformanceTable">
                            <tr>
                                <td colspan="4" style="text-align: center; color: #999;">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-container">
                    <h3><i class="fas fa-users"></i> Top Customers</h3>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Purchases Count</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody id="topCustomersTable">
                            <tr>
                                <td colspan="3" style="text-align: center; color: #999;">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        let chartData = {};
        let advancedCharts = {};

        // Initialize with sample data for demonstration
        document.addEventListener('DOMContentLoaded', function() {
            // Load actual reports data
            loadAdvancedReports();
            
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

        async function loadAdvancedReports() {
            try {
                const response = await fetch('php/get_advanced_reports_data.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    chartData = data.data;
                    updateReportCards();
                    initializeAdvancedCharts();
                    populateDetailedTables();
                }
            } catch (error) {
                console.error('Error loading advanced reports:', error);
                showError('Failed to load advanced reports data');
            }
        }

        function updateReportCards() {
            const sales = chartData.sales_summary;
            const inventory = chartData.inventory_status;
            const profitLoss = chartData.profit_loss_data;
            const performance = chartData.performance_metrics;
            
            // Update executive dashboard
            document.getElementById('totalRevenue').textContent = '$' + parseFloat(sales.total_revenue || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('netProfit').textContent = '$' + parseFloat(profitLoss.net_profit || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('profitMargin').textContent = (profitLoss.profit_margin || 0).toFixed(1) + '%';
            document.getElementById('inventoryValue').textContent = '$' + parseFloat(inventory.total_inventory_value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Update advanced analytics
            document.getElementById('inventoryTurnover').textContent = (performance.inventory_turnover || 0).toFixed(1);
            document.getElementById('customerRetention').textContent = (performance.customer_retention_rate || 0).toFixed(1) + '%';
            document.getElementById('growthRate').textContent = ((sales.total_revenue || 0) > 0 ? '12.5' : '0') + '%'; // Mock data
            document.getElementById('operationalEfficiency').textContent = '87%'; // Mock data
            
            // Update financial health
            document.getElementById('grossProfit').textContent = '$' + parseFloat(profitLoss.gross_profit || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('operatingExpenses').textContent = '$' + parseFloat(profitLoss.operating_expenses || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('debtRatio').textContent = '15%'; // Mock data
            document.getElementById('cashFlow').textContent = '$' + parseFloat((profitLoss.net_profit || 0) - (profitLoss.operating_expenses || 0)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function initializeAdvancedCharts() {
            // Destroy existing charts if they exist
            Object.values(advancedCharts).forEach(chart => {
                if (chart) chart.destroy();
            });
            
            // Sales by category chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryNames = chartData.sales_by_category.map(c => c.category_name);
            const categoryRevenues = chartData.sales_by_category.map(c => parseFloat(c.category_revenue));
            
            advancedCharts.categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryNames,
                    datasets: [{
                        data: categoryRevenues,
                        backgroundColor: [
                            '#667eea',
                            '#764ba2',
                            '#f093fb',
                            '#f5576c',
                            '#4facfe',
                            '#00f2fe',
                            '#43e97b',
                            '#38f9d7'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });

            // Monthly sales trend chart
            const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
            const months = chartData.monthly_sales_trend.map(m => m.month);
            const monthlyRevenues = chartData.monthly_sales_trend.map(m => parseFloat(m.monthly_revenue));
            
            advancedCharts.monthlyTrendChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: monthlyRevenues,
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

            // Profit & Loss chart
            const profitLossCtx = document.getElementById('profitLossChart').getContext('2d');
            advancedCharts.profitLossChart = new Chart(profitLossCtx, {
                type: 'bar',
                data: {
                    labels: ['Gross Revenue', 'COGS', 'Gross Profit', 'Expenses', 'Net Profit'],
                    datasets: [{
                        label: 'Amount ($)',
                        data: [
                            parseFloat(chartData.profit_loss_data.gross_revenue || 0),
                            parseFloat(chartData.profit_loss_data.cogs || 0),
                            parseFloat(chartData.profit_loss_data.gross_profit || 0),
                            parseFloat(chartData.profit_loss_data.operating_expenses || 0),
                            parseFloat(chartData.profit_loss_data.net_profit || 0)
                        ],
                        backgroundColor: [
                            'rgba(76, 175, 80, 0.7)',
                            'rgba(244, 67, 54, 0.7)',
                            'rgba(33, 150, 243, 0.7)',
                            'rgba(255, 152, 0, 0.7)',
                            'rgba(156, 39, 176, 0.7)'
                        ],
                        borderColor: [
                            '#4caf50',
                            '#f44336',
                            '#2196f3',
                            '#ff9800',
                            '#9c27b0'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function populateDetailedTables() {
            // Top products table
            const topProductsTable = document.getElementById('topProductsTable');
            let topProductsHtml = '';
            
            if (chartData.top_products && chartData.top_products.length > 0) {
                chartData.top_products.slice(0, 10).forEach(item => {
                    topProductsHtml += `<tr>
                        <td>${item.name}</td>
                        <td>${item.category_name || 'N/A'}</td>
                        <td>${item.total_sold || 0}</td>
                        <td>$${parseFloat(item.revenue || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>$${parseFloat(item.avg_selling_price || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>`;
                });
            } else {
                topProductsHtml = '<tr><td colspan="5" style="text-align: center; color: #999;">No product data available</td></tr>';
            }
            
            topProductsTable.innerHTML = topProductsHtml;
            
            // Supplier performance table
            const supplierPerformanceTable = document.getElementById('supplierPerformanceTable');
            let supplierHtml = '';
            
            if (chartData.supplier_performance && chartData.supplier_performance.length > 0) {
                chartData.supplier_performance.slice(0, 10).forEach(item => {
                    supplierHtml += `<tr>
                        <td>${item.supplier_name || 'N/A'}</td>
                        <td>${item.products_supplied || 0}</td>
                        <td>$${parseFloat((item.total_stock || 0) * (item.avg_product_price || 0)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>$${parseFloat(item.avg_product_price || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>`;
                });
            } else {
                supplierHtml = '<tr><td colspan="4" style="text-align: center; color: #999;">No supplier data available</td></tr>';
            }
            
            supplierPerformanceTable.innerHTML = supplierHtml;
            
            // Top customers table
            const topCustomersTable = document.getElementById('topCustomersTable');
            let customersHtml = '';
            
            if (chartData.top_customers && chartData.top_customers.length > 0) {
                chartData.top_customers.slice(0, 10).forEach(item => {
                    customersHtml += `<tr>
                        <td>${item.customer_name || 'N/A'}</td>
                        <td>${item.purchases_count || 0}</td>
                        <td>$${parseFloat(item.total_spent || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    </tr>`;
                });
            } else {
                customersHtml = '<tr><td colspan="3" style="text-align: center; color: #999;">No customer data available</td></tr>';
            }
            
            topCustomersTable.innerHTML = customersHtml;
        }

        // Enhanced export functionality
        function exportToCSV() {
            // Create CSV content with all report data
            let csvContent = "Advanced Business Report Generated:," + new Date().toISOString().split('T')[0] + "\n\n";
            
            // Executive Dashboard Summary
            csvContent += "Executive Dashboard Summary\n";
            csvContent += "Metric,Value\n";
            csvContent += "Total Revenue,$" + (chartData.sales_summary.total_revenue || 0) + "\n";
            csvContent += "Net Profit,$" + (chartData.profit_loss_data.net_profit || 0) + "\n";
            csvContent += "Profit Margin," + (chartData.profit_loss_data.profit_margin || 0).toFixed(2) + "%\n";
            csvContent += "Inventory Value,$" + (chartData.inventory_status.total_inventory_value || 0) + "\n\n";
            
            // Sales Summary
            csvContent += "Sales Summary\n";
            csvContent += "Metric,Value\n";
            csvContent += "Total Sales," + (chartData.sales_summary.total_sales || 0) + "\n";
            csvContent += "Total Revenue,$" + (chartData.sales_summary.total_revenue || 0) + "\n";
            csvContent += "Average Sale,$" + (chartData.sales_summary.average_sale || 0) + "\n\n";
            
            // Inventory Status
            csvContent += "Inventory Status\n";
            csvContent += "Metric,Value\n";
            csvContent += "Total Products," + (chartData.inventory_status.total_products || 0) + "\n";
            csvContent += "Out of Stock," + (chartData.inventory_status.out_of_stock || 0) + "\n";
            csvContent += "Low Stock," + (chartData.inventory_status.low_stock || 0) + "\n";
            csvContent += "Inventory Value,$" + (chartData.inventory_status.total_inventory_value || 0) + "\n\n";
            
            // Profit & Loss Data
            csvContent += "Profit & Loss Analysis\n";
            csvContent += "Metric,Value\n";
            csvContent += "Gross Revenue,$" + (chartData.profit_loss_data.gross_revenue || 0) + "\n";
            csvContent += "Cost of Goods Sold,$" + (chartData.profit_loss_data.cogs || 0) + "\n";
            csvContent += "Gross Profit,$" + (chartData.profit_loss_data.gross_profit || 0) + "\n";
            csvContent += "Operating Expenses,$" + (chartData.profit_loss_data.operating_expenses || 0) + "\n";
            csvContent += "Net Profit,$" + (chartData.profit_loss_data.net_profit || 0) + "\n\n";
            
            // Top Products
            csvContent += "Top Selling Products\n";
            csvContent += "Product,Category,Units Sold,Revenue,Average Price\n";
            if (chartData.top_products) {
                chartData.top_products.forEach(product => {
                    csvContent += `"${product.name}","${product.category_name || 'N/A'}",${product.total_sold || 0},"$${product.revenue || 0}","$${product.avg_selling_price || 0}"\n`;
                });
            }
            
            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "advanced_business_report_" + new Date().toISOString().split('T')[0] + ".csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function showError(message) {
            alert('Error: ' + message);
        }

        // Refresh data periodically
        setInterval(loadAdvancedReports, 300000); // Refresh every 5 minutes
    </script>
    <script src="/emmanuel/js/toast.js"></script>
</body>
</html>