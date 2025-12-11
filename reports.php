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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/toast.css">
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
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-left">
            <div class="logo">InventoryPro</div>
        </div>
        <div class="nav-right">
            <div class="user-menu" id="userMenu">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($userName); ?></span>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-layout">
        <aside class="sidebar">
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
                    <i class="fas fa-list"></i>
                    <span>Categories</span>
                </a>
                <a href="units.php" class="menu-item">
                    <i class="fas fa-ruler"></i>
                    <span>Units</span>
                </a>
                <a href="customers.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="sales.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
                <a href="credit_sales.php" class="menu-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Credit Sales</span>
                </a>
                <a href="pos.php" class="menu-item">
                    <i class="fas fa-cash-register"></i>
                    <span>Point of Sale</span>
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

        <main class="main-content">
            <div class="content-section">
                <div class="section-header">
                    <h2>Reports & Analytics</h2>
                    <button class="export-btn" onclick="exportToCSV()"><i class="fas fa-download"></i> Export Report</button>
                </div>
            </div>

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
                <div class="report-card">
                    <h3><i class="fas fa-boxes"></i> Inventory Value</h3>
                    <div class="report-value" id="inventoryValue">$0.00</div>
                    <div class="report-label">Total inventory value</div>
                </div>
                <div class="report-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Out of Stock</h3>
                    <div class="report-value" id="outOfStock">0</div>
                    <div class="report-label">Products with zero stock</div>
                </div>
                <div class="report-card">
                    <h3><i class="fas fa-credit-card"></i> Outstanding Credit</h3>
                    <div class="report-value" id="outstandingCredit">$0.00</div>
                    <div class="report-label">Total credit sales balance</div>
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
        </main>
    </div>

    <script>
        let chartData = {};

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
            document.getElementById('inventoryValue').textContent = '$' + inv.total_inventory_value;
            document.getElementById('outOfStock').textContent = inv.out_of_stock;
            document.getElementById('outstandingCredit').textContent = '$' + credit.total_outstanding;
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
            
            chartData.sales_by_date.forEach(item => {
                csv.push([item.sale_date, item.sales_count, item.daily_revenue].join(','));
            });
            
            const csvContent = csv.join('\n');
            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent);
            link.download = 'sales_report_' + new Date().toISOString().split('T')[0] + '.csv';
            link.click();
        }

        loadReports();
    </script>
    <script src="js/toast.js"></script>
</body>
</html>
