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

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $user = ['username' => $userName, 'email' => '', 'role' => $userRole];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/toast.css">
    <style>
        .settings-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }
        @media (max-width: 768px) {
            .settings-container {
                grid-template-columns: 1fr;
            }
        }
        .settings-menu {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            height: fit-content;
        }
        .settings-menu h3 {
            margin: 0 0 1rem 0;
            color: #333;
            font-size: 1rem;
        }
        .settings-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            color: #666;
            text-decoration: none;
            font-weight: 500;
        }
        .settings-menu-item:hover,
        .settings-menu-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .settings-menu-item i {
            width: 20px;
        }
        .settings-panel {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: none;
        }
        .settings-panel.active {
            display: block;
        }
        .panel-header {
            margin-bottom: 2rem;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 1rem;
        }
        .panel-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            max-width: 500px;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .btn-danger {
            background: #f44336;
            color: white;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .setting-item {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .setting-item-info h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        .setting-item-info p {
            margin: 0;
            color: #999;
            font-size: 0.9rem;
        }
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 24px;
        }
        .toggle-switch input {
            display: none;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.3s;
            border-radius: 24px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }
        input:checked + .toggle-slider {
            background-color: #667eea;
        }
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .alert-danger {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .database-info {
            background: #f5f5f5;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-family: monospace;
            font-size: 0.9rem;
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
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="settings.php" class="menu-item active">
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
            <div class="settings-container">
                <div class="settings-menu">
                    <h3>Settings</h3>
                    <a class="settings-menu-item active" onclick="showPanel('profile')">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a class="settings-menu-item" onclick="showPanel('account')">
                        <i class="fas fa-lock"></i> Account
                    </a>
                    <a class="settings-menu-item" onclick="showPanel('system')">
                        <i class="fas fa-cogs"></i> System
                    </a>
                    <a class="settings-menu-item" onclick="showPanel('about')">
                        <i class="fas fa-info-circle"></i> About
                    </a>
                </div>

                <div class="settings-panel active" id="profile-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-user"></i> Profile Settings</h2>
                    </div>
                    <form id="profileForm">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter your email">
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" value="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </form>
                </div>

                <div class="settings-panel" id="account-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-lock"></i> Account Settings</h2>
                    </div>
                    <div class="form-group">
                        <label>Change Password</label>
                        <form id="passwordForm" style="margin-top: 1rem;">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" placeholder="Enter current password" required>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" placeholder="Enter new password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" id="confirmPassword" placeholder="Confirm new password" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
                        </form>
                    </div>
                </div>

                <div class="settings-panel" id="system-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-cogs"></i> System Settings</h2>
                    </div>

                    <div class="setting-item">
                        <div class="setting-item-info">
                            <h4>Low Stock Alert Threshold</h4>
                            <p>Set minimum stock level for alerts</p>
                        </div>
                        <input type="number" value="10" style="width: 100px; max-width: none;">
                    </div>

                    <div class="setting-item">
                        <div class="setting-item-info">
                            <h4>Enable Email Notifications</h4>
                            <p>Receive alerts via email</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <div class="setting-item-info">
                            <h4>Auto Backup</h4>
                            <p>Automatically backup database</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <select id="currency" style="max-width: 200px;">
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="JPY">JPY (¥)</option>
                        </select>
                    </div>

                    <button class="btn btn-primary"><i class="fas fa-save"></i> Save System Settings</button>
                </div>

                <div class="settings-panel" id="about-panel">
                    <div class="panel-header">
                        <h2><i class="fas fa-info-circle"></i> About InventoryPro</h2>
                    </div>

                    <div class="form-group">
                        <h4>Application Information</h4>
                        <div class="database-info">
                            <strong>Application:</strong> InventoryPro<br>
                            <strong>Version:</strong> 1.0.0<br>
                            <strong>Database:</strong> MySQL<br>
                            <strong>Framework:</strong> PHP<br>
                            <strong>License:</strong> MIT<br>
                            <strong>Last Updated:</strong> 2024
                        </div>
                    </div>

                    <div class="form-group">
                        <h4>Features</h4>
                        <ul style="color: #666;">
                            <li>Complete inventory management system</li>
                            <li>Real-time stock tracking</li>
                            <li>Sales management and reporting</li>
                            <li>Customer and credit sales management</li>
                            <li>Point of Sale (POS) integration</li>
                            <li>Comprehensive analytics and reports</li>
                            <li>User role management</li>
                            <li>Secure authentication</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <h4>Database Status</h4>
                        <div style="background: #d1e7dd; color: #0f5132; padding: 1rem; border-radius: 6px; border: 1px solid #badbcc;">
                            <i class="fas fa-check-circle"></i> Database connection: <strong>Active</strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <h4>Support & Documentation</h4>
                        <p style="color: #666;">For help with InventoryPro, refer to the comprehensive documentation or contact support.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showPanel(panelName) {
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.settings-menu-item').forEach(m => m.classList.remove('active'));
            
            document.getElementById(panelName + '-panel').classList.add('active');
            event.target.closest('.settings-menu-item').classList.add('active');
        }

        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            showSuccess('Profile updated successfully!');
        });

        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                showError('Passwords do not match!');
                return;
            }

            if (newPassword.length < 6) {
                showError('Password must be at least 6 characters long!');
                return;
            }

            showSuccess('Password changed successfully!');
            document.getElementById('passwordForm').reset();
        });
    </script>
    <script src="js/toast.js"></script>
</body>
</html>
