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
    <title>Settings - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/responsive.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <style>
        .settings-grid { display: grid; grid-template-columns: 250px 1fr; gap: 20px; margin-top: 30px; }
        .settings-menu { background: white; border-radius: 10px; padding: 0; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); height: fit-content; }
        .settings-menu a { display: block; padding: 15px 20px; border-bottom: 1px solid #eee; color: #333; text-decoration: none; transition: all 0.2s; }
        .settings-menu a:hover, .settings-menu a.active { background-color: #f0f0f0; border-left: 4px solid #6a11cb; color: #6a11cb; font-weight: 600; }
        .settings-content { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); }
        .settings-group { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .settings-group:last-child { border-bottom: none; }
        .settings-group h3 { margin: 0 0 15px; color: #333; }
        .setting-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .setting-label { color: #555; font-weight: 500; }
        .setting-value { color: #777; }
        .btn-save { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .toggle { width: 50px; height: 25px; background: #ddd; border-radius: 12px; cursor: pointer; position: relative; transition: all 0.3s; }
        .toggle.active { background: #6a11cb; }
        .toggle-circle { width: 21px; height: 21px; background: white; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: all 0.3s; }
        .toggle.active .toggle-circle { left: 27px; }
        @media (max-width: 768px) { .settings-grid { grid-template-columns: 1fr; } }
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
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="settings.php" class="menu-item active"><i class="fas fa-cog"></i><span>Settings</span></a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Settings</h1>

            <div class="settings-grid">
                <div class="settings-menu">
                    <a href="#general" class="active"><i class="fas fa-cog"></i> General</a>
                    <a href="#business"><i class="fas fa-store"></i> Business</a>
                    <a href="#notifications"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="#security"><i class="fas fa-lock"></i> Security</a>
                </div>

                <div class="settings-content">
                    <div class="settings-group">
                        <h3>General Settings</h3>
                        <div class="setting-row">
                            <span class="setting-label">Currency</span>
                            <select style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option>USD ($)</option>
                                <option>EUR (€)</option>
                                <option>GBP (£)</option>
                            </select>
                        </div>
                        <div class="setting-row">
                            <span class="setting-label">Language</span>
                            <select style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option>English</option>
                                <option>Spanish</option>
                                <option>French</option>
                            </select>
                        </div>
                        <div class="setting-row">
                            <span class="setting-label">Time Zone</span>
                            <select style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                <option>UTC</option>
                                <option>EST</option>
                                <option>PST</option>
                            </select>
                        </div>
                    </div>

                    <div class="settings-group">
                        <h3>Business Settings</h3>
                        <div class="setting-row">
                            <span class="setting-label">Tax Rate (%)</span>
                            <input type="number" value="10" style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div class="setting-row">
                            <span class="setting-label">Low Stock Alert Threshold</span>
                            <input type="number" value="10" style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                    </div>

                    <div class="settings-group">
                        <h3>Notification Settings</h3>
                        <div class="setting-row">
                            <span class="setting-label">Email Notifications</span>
                            <div class="toggle active">
                                <div class="toggle-circle"></div>
                            </div>
                        </div>
                        <div class="setting-row">
                            <span class="setting-label">Low Stock Alerts</span>
                            <div class="toggle active">
                                <div class="toggle-circle"></div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <button class="btn-save"><i class="fas fa-save"></i> Save Settings</button>
                    </div>
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
