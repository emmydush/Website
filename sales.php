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
    <title>Sales - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/emmanuel/css/modern_dashboard.css">
    <link rel="stylesheet" href="/emmanuel/css/toast.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
        }
        .modal-header {
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 1rem;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }
        .modal-body {
            margin-bottom: 1.5rem;
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
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }
        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
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
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .sales-table th {
            background: transparent;
            color: #666;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
            border-bottom: 1px solid #ddd;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .sales-table td {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            color: #333;
        }
        .sales-table tr:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        .status-badge {
            background: #4caf50;
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        .sale-id {
            font-weight: 600;
            color: #333;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-btn.view {
            background: #2196f3;
            color: white;
        }
        .action-btn.view:hover {
            background: #1976d2;
        }
        .action-btn.edit {
            background: #ff9800;
            color: white;
        }
        .action-btn.edit:hover {
            background: #f57c00;
        }
        .action-btn.delete {
            background: #f44336;
            color: white;
        }
        .action-btn.delete:hover {
            background: #da190b;
        }
        .action-btn.refresh {
            background: #9e9e9e;
            color: white;
        }
        .action-btn.refresh:hover {
            background: #757575;
        }
        .content-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 2rem 0;
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
        .header-buttons {
            display: flex;
            gap: 1rem;
        }
        .btn-create {
            background: #00bcd4;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-create:hover {
            background: #00acc1;
            transform: translateY(-2px);
        }
        .btn-pos {
            background: #00bcd4;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-pos:hover {
            background: #00acc1;
            transform: translateY(-2px);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }
        .summary-card h3 {
            margin: 0;
            font-size: 0.875rem;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        .summary-card .value {
            font-size: 1.75rem;
            font-weight: bold;
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
                <?php
                $avatarBase = ($_SESSION['user_id'] ?? 'guest');
                $avatarPathJ = 'uploads/avatars/' . $avatarBase . '.jpg';
                $avatarPathP = 'uploads/avatars/' . $avatarBase . '.png';
                $avatarPathW = 'uploads/avatars/' . $avatarBase . '.webp';
                if (file_exists(__DIR__ . '/' . $avatarPathJ)) {
                    $avatarUrl = $avatarPathJ;
                } elseif (file_exists(__DIR__ . '/' . $avatarPathP)) {
                    $avatarUrl = $avatarPathP;
                } elseif (file_exists(__DIR__ . '/' . $avatarPathW)) {
                    $avatarUrl = $avatarPathW;
                } else {
                    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($userName ?? 'User') . '&background=0D8ABC&color=fff&size=64';
                }
                ?>
                <img src="<?php echo $avatarUrl; ?>" alt="User" class="profile-image" id="profileImage">
                <div class="user-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></h3>
                    <p class="user-role"><?php echo htmlspecialchars($userRole ?? 'Staff'); ?></p>
                </div>
                <form id="avatarForm" style="margin-top:8px;" enctype="multipart/form-data">
                    <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;">
                    <button type="button" class="btn btn-sm" id="uploadAvatarBtn" title="Upload profile picture"><i class="fas fa-camera"></i></button>
                </form>
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
                <a href="sales.php" class="menu-item active">
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
                    <h2>Sales</h2>
                    <div class="header-buttons">
                        <button class="btn-create" id="addSaleBtn">
                            <i class="fas fa-plus"></i> Create Sale
                        </button>
                        <a href="pos_system.php" class="btn-pos">
                            <i class="fas fa-cash-register"></i> POS
                        </a>
                    </div>
                </div>

                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Sales</h3>
                        <div class="value" id="totalSalesCount">0</div>
                    </div>
                    <div class="summary-card">
                        <h3>Total Revenue</h3>
                        <div class="value" id="totalRevenue">$0.00</div>
                    </div>
                    <div class="summary-card">
                        <h3>Average Sale</h3>
                        <div class="value" id="averageSale">$0.00</div>
                    </div>
                </div>

                <div id="salesContainer">
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Loading sales...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="saleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Record Sale</h2>
            </div>
            <form id="saleForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="productId">Product *</label>
                        <select id="productId" required>
                            <option value="">Select a product</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantitySold">Quantity Sold *</label>
                        <input type="number" id="quantitySold" placeholder="Enter quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="salePrice">Sale Price *</label>
                        <input type="number" id="salePrice" placeholder="Enter sale price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="totalAmount">Total Amount</label>
                        <input type="number" id="totalAmount" placeholder="Auto calculated" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Sale</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('saleModal');
        const addSaleBtn = document.getElementById('addSaleBtn');
        const closeModal = document.getElementById('closeModal');
        const saleForm = document.getElementById('saleForm');
        const quantitySold = document.getElementById('quantitySold');
        const salePrice = document.getElementById('salePrice');
        const totalAmount = document.getElementById('totalAmount');

        addSaleBtn.addEventListener('click', () => {
            saleForm.reset();
            modal.classList.add('show');
            loadProducts();
        });

        closeModal.addEventListener('click', () => {
            modal.classList.remove('show');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });

        quantitySold.addEventListener('input', calculateTotal);
        salePrice.addEventListener('input', calculateTotal);

        function calculateTotal() {
            const qty = parseFloat(quantitySold.value) || 0;
            const price = parseFloat(salePrice.value) || 0;
            totalAmount.value = (qty * price).toFixed(2);
        }

        saleForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const data = {
                product_id: document.getElementById('productId').value,
                quantity_sold: document.getElementById('quantitySold').value,
                sale_price: document.getElementById('salePrice').value,
                total_amount: document.getElementById('totalAmount').value
            };
            
            try {
                const response = await fetch('php/add_sale.php', {
                    method: 'POST',
                    body: new URLSearchParams(data)
                });
                
                const result = await response.json();

                if (result.status === 'success') {
                    showSuccess(result.message);
                    modal.classList.remove('show');
                    loadSales();
                } else {
                    showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred');
            }
        });

        async function loadProducts() {
            try {
                const response = await fetch('php/get_products.php');
                const data = await response.json();
                
                const select = document.getElementById('productId');
                select.innerHTML = '<option value="">Select a product</option>';
                
                if (data.status === 'success') {
                    data.data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = `${product.name} (Stock: ${product.quantity})`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading products:', error);
            }
        }

        async function loadSales() {
            try {
                const response = await fetch('php/get_sales.php');
                const data = await response.json();
                
                const container = document.getElementById('salesContainer');
                
                if (data.status === 'success' && data.data.length > 0) {
                    let totalCount = 0;
                    let totalRev = 0;
                    
                    data.data.forEach(sale => {
                        totalCount++;
                        totalRev += parseFloat(sale.total_amount);
                    });
                    
                    document.getElementById('totalSalesCount').textContent = totalCount;
                    document.getElementById('totalRevenue').textContent = 'FRW' + totalRev.toFixed(2);
                    document.getElementById('averageSale').textContent = 'FRW' + (totalRev / totalCount).toFixed(2);
                    
                    let html = '<table class="sales-table"><thead><tr><th>SALE ID</th><th>CUSTOMER</th><th>DATE</th><th>PAYMENT METHOD</th><th>TOTAL AMOUNT</th><th>TOTAL PROFIT</th><th>STATUS</th><th>ACTIONS</th></tr></thead><tbody>';
                    
                    data.data.forEach((sale, index) => {
                        const saleDate = new Date(sale.created_at);
                        const formattedDate = saleDate.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        }).replace(',', '');
                        
                        // Calculate profit (assuming profit = 35% of total amount)
                        const profit = parseFloat(sale.total_amount) * 0.35;
                        
                        html += `<tr>
                            <td class="sale-id">Sale-${data.data.length - index}</td>
                            <td>Walk-in Customer</td>
                            <td>${formattedDate}</td>
                            <td>Cash</td>
                            <td>FRW${parseFloat(sale.total_amount).toFixed(2)}</td>
                            <td>FRW${profit.toFixed(2)}</td>
                            <td><span class="status-badge">Completed</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn view" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" title="Delete"><i class="fas fa-trash"></i></button>
                                    <button class="action-btn refresh" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><p>No sales recorded yet. Record your first sale!</p></div>';
                }
            } catch (error) {
                console.error('Error loading sales:', error);
                document.getElementById('salesContainer').innerHTML = '<div class="empty-state"><p>Error loading sales</p></div>';
            }
        }

        loadSales();

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
    <script src="js/avatar.js"></script>
    <script src="/emmanuel/js/toast.js"></script>
</body>
</html>
