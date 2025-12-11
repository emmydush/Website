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
    <link rel="stylesheet" href="/emmanuel/css/modern_dashboard.css">
    <link rel="stylesheet" href="/emmanuel/css/toast.css">
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

        <!-- Main Content -->
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

        showPanel('profile');

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
    </script>
    <script src="/emmanuel/js/toast.js"></script>
</body>
</html>
