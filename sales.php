<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
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
    <title>Sales - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/responsive.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <style>
        .page-header {
            margin-bottom: 30px;
        }
        
        .search-container {
            margin-bottom: 25px;
        }
        
        .search-bar {
            display: flex;
            gap: 10px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .search-bar button {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .sales-table th,
        .sales-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .sales-table th {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        .sales-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
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
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff" alt="User" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <a href="#"><i class="fas fa-user"></i> Profile</a>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <aside class="sidebar">
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff&size=64" alt="User" class="profile-image">
                <div class="user-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></h3>
                    <p class="user-role"><?php echo htmlspecialchars($userRole ?? 'Staff'); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="modern_dashboard.php" class="menu-item"><i class="fas fa-home"></i><span>Home</span></a>
                <a href="products.php" class="menu-item"><i class="fas fa-box"></i><span>Products</span></a>
                <a href="categories.php" class="menu-item"><i class="fas fa-tags"></i><span>Categories</span></a>
                <a href="units.php" class="menu-item"><i class="fas fa-ruler"></i><span>Units</span></a>
                <a href="sales.php" class="menu-item active"><i class="fas fa-shopping-cart"></i><span>Sales</span></a>
                <a href="pos.php" class="menu-item"><i class="fas fa-cash-register"></i><span>Point of Sale</span></a>
                <a href="credit_sales.php" class="menu-item"><i class="fas fa-credit-card"></i><span>Credit Sales</span></a>
                <a href="customers.php" class="menu-item"><i class="fas fa-users"></i><span>Customers</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Settings</span></a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Sales Transactions</h1>
                <p style="color: #777; margin-top: 5px;">View all sales transactions and history</p>
            </div>

            <div class="search-container">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by customer, product, or date...">
                    <button id="searchBtn"><i class="fas fa-search"></i> Search</button>
                </div>
            </div>

            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">Loading sales data...</td>
                    </tr>
                </tbody>
            </table>

            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-shopping-cart"></i>
                <p>No sales transactions found</p>
            </div>
        </main>
    </div>

    <script>
        const salesTableBody = document.getElementById('salesTableBody');
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const emptyState = document.getElementById('emptyState');
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');

        userMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });

        document.addEventListener('DOMContentLoaded', loadSales);

        searchBtn.addEventListener('click', () => loadSales(searchInput.value));
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') loadSales(searchInput.value);
        });

        function loadSales(searchTerm = '') {
            // Sample data for demo
            const mockSales = [
                { id: 1, date: '2025-01-10', customer: 'John Doe', product: 'Laptop', quantity: 1, amount: 1200, status: 'Completed' },
                { id: 2, date: '2025-01-09', customer: 'Jane Smith', product: 'Mouse', quantity: 2, amount: 50, status: 'Completed' },
                { id: 3, date: '2025-01-08', customer: 'Bob Johnson', product: 'Keyboard', quantity: 1, amount: 80, status: 'Pending' }
            ];

            displaySales(mockSales);
        }

        function displaySales(sales) {
            salesTableBody.innerHTML = '';

            if (sales.length === 0) {
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            sales.forEach(sale => {
                const row = document.createElement('tr');
                const statusClass = sale.status === 'Completed' ? 'status-completed' : 'status-pending';
                row.innerHTML = `
                    <td>${sale.date}</td>
                    <td>${sale.customer}</td>
                    <td>${sale.product}</td>
                    <td>${sale.quantity}</td>
                    <td>$${sale.amount.toFixed(2)}</td>
                    <td><span class="status-badge ${statusClass}">${sale.status}</span></td>
                `;
                salesTableBody.appendChild(row);
            });
        }
    </script>
</body>
</html>
