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
    <title>Credit Sales - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/responsive.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <style>
        .page-header { margin-bottom: 30px; }
        .btn-primary { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .credit-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); margin-top: 20px; }
        .credit-table th, .credit-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .credit-table th { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; font-weight: 600; text-transform: uppercase; }
        .credit-table tr:hover { background-color: #f8f9fa; }
        .status-paid { color: #28a745; font-weight: 600; }
        .status-pending { color: #dc3545; font-weight: 600; }
        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-left"><div class="logo">InventoryPro</div></div>
        <div class="nav-right">
            <div class="user-menu" id="userMenu">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff" alt="User" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></span>
            </div>
            <div class="user-dropdown" id="userDropdown">
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
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="modern_dashboard.php" class="menu-item"><i class="fas fa-home"></i><span>Home</span></a>
                <a href="products.php" class="menu-item"><i class="fas fa-box"></i><span>Products</span></a>
                <a href="categories.php" class="menu-item"><i class="fas fa-tags"></i><span>Categories</span></a>
                <a href="units.php" class="menu-item"><i class="fas fa-ruler"></i><span>Units</span></a>
                <a href="sales.php" class="menu-item"><i class="fas fa-shopping-cart"></i><span>Sales</span></a>
                <a href="pos.php" class="menu-item"><i class="fas fa-cash-register"></i><span>Point of Sale</span></a>
                <a href="credit_sales.php" class="menu-item active"><i class="fas fa-credit-card"></i><span>Credit Sales</span></a>
                <a href="customers.php" class="menu-item"><i class="fas fa-users"></i><span>Customers</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Settings</span></a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Credit Sales Management</h1>
            </div>

            <table class="credit-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2025-01-10</td>
                        <td>John Doe</td>
                        <td>$1,500.00</td>
                        <td>2025-02-10</td>
                        <td><span class="status-pending">Pending</span></td>
                    </tr>
                    <tr>
                        <td>2025-01-05</td>
                        <td>Jane Smith</td>
                        <td>$800.00</td>
                        <td>2025-02-05</td>
                        <td><span class="status-paid">Paid</span></td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');
        userMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target)) userDropdown.classList.remove('show');
        });
    </script>
</body>
</html>
