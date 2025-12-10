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
    <title>Reports - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/responsive.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <style>
        .reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .report-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); border-left: 5px solid #6a11cb; }
        .report-card h3 { margin: 0 0 15px; color: #333; }
        .report-card p { color: #777; margin: 0; }
        .stat-value { font-size: 28px; font-weight: 600; color: #6a11cb; margin: 15px 0; }
        .btn-generate { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; margin-top: 10px; }
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
                <a href="credit_sales.php" class="menu-item"><i class="fas fa-credit-card"></i><span>Credit Sales</span></a>
                <a href="customers.php" class="menu-item"><i class="fas fa-users"></i><span>Customers</span></a>
                <a href="reports.php" class="menu-item active"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Settings</span></a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Business Reports & Analytics</h1>

            <div class="reports-grid">
                <div class="report-card">
                    <h3><i class="fas fa-chart-line"></i> Sales Report</h3>
                    <p>Total sales this month</p>
                    <div class="stat-value">$45,320</div>
                    <button class="btn-generate">Generate PDF</button>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-boxes"></i> Inventory Report</h3>
                    <p>Total products in stock</p>
                    <div class="stat-value">1,248</div>
                    <button class="btn-generate">Generate PDF</button>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-users"></i> Customer Report</h3>
                    <p>Active customers</p>
                    <div class="stat-value">342</div>
                    <button class="btn-generate">Generate PDF</button>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Report</h3>
                    <p>Products below minimum stock</p>
                    <div class="stat-value">24</div>
                    <button class="btn-generate">View Items</button>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-dollar-sign"></i> Revenue Report</h3>
                    <p>Total revenue (YTD)</p>
                    <div class="stat-value">$542,100</div>
                    <button class="btn-generate">Generate PDF</button>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-calendar"></i> Daily Summary</h3>
                    <p>Today's transactions</p>
                    <div class="stat-value">$3,450</div>
                    <button class="btn-generate">View Details</button>
                </div>
            </div>
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
