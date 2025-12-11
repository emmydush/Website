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
    <title>Customers - Inventory Management</title>
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
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-row.full {
            grid-template-columns: 1fr;
        }
        .form-group {
            margin-bottom: 0;
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
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
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
        .btn-edit {
            background: #4CAF50;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .customers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            font-size: 0.9rem;
        }
        .customers-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        .customers-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .customers-table tr:hover {
            background: #f9f9f9;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
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
                <a href="customers.php" class="menu-item active">
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
                    <h2>Customers Management</h2>
                    <button class="btn btn-primary" id="addCustomerBtn">
                        <i class="fas fa-plus"></i> Add Customer
                    </button>
                </div>

                <div id="customersContainer">
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>Loading customers...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="customerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Customer</h2>
            </div>
            <form id="customerForm">
                <div class="modal-body">
                    <input type="hidden" id="customerId">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerName">Customer Name *</label>
                            <input type="text" id="customerName" placeholder="Enter customer name" required>
                        </div>
                        <div class="form-group">
                            <label for="customerType">Customer Type *</label>
                            <select id="customerType" required>
                                <option value="retail">Retail</option>
                                <option value="wholesale">Wholesale</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerEmail">Email</label>
                            <input type="email" id="customerEmail" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label for="customerPhone">Phone</label>
                            <input type="tel" id="customerPhone" placeholder="Enter phone number">
                        </div>
                    </div>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="customerAddress">Address</label>
                            <input type="text" id="customerAddress" placeholder="Enter address">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerCity">City</label>
                            <input type="text" id="customerCity" placeholder="Enter city">
                        </div>
                        <div class="form-group">
                            <label for="customerState">State</label>
                            <input type="text" id="customerState" placeholder="Enter state">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerPostalCode">Postal Code</label>
                            <input type="text" id="customerPostalCode" placeholder="Enter postal code">
                        </div>
                        <div class="form-group">
                            <label for="customerCountry">Country</label>
                            <input type="text" id="customerCountry" placeholder="Enter country">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('customerModal');
        const addCustomerBtn = document.getElementById('addCustomerBtn');
        const closeModal = document.getElementById('closeModal');
        const customerForm = document.getElementById('customerForm');

        addCustomerBtn.addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Add Customer';
            customerForm.reset();
            document.getElementById('customerId').value = '';
            modal.classList.add('show');
        });

        closeModal.addEventListener('click', () => {
            modal.classList.remove('show');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });

        customerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const id = document.getElementById('customerId').value;
            const data = {
                name: document.getElementById('customerName').value,
                email: document.getElementById('customerEmail').value,
                phone: document.getElementById('customerPhone').value,
                address: document.getElementById('customerAddress').value,
                city: document.getElementById('customerCity').value,
                state: document.getElementById('customerState').value,
                postal_code: document.getElementById('customerPostalCode').value,
                country: document.getElementById('customerCountry').value,
                customer_type: document.getElementById('customerType').value
            };
            
            if (id) {
                data.id = id;
                data.status = 'active';
            }
            
            const endpoint = id ? 'php/update_customer.php' : 'php/add_customer.php';
            const body = new URLSearchParams(data);
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: body
                });
                
                const result = await response.json();

                if (result.status === 'success') {
                    showSuccess(result.message);
                    modal.classList.remove('show');
                    loadCustomers();
                } else {
                    showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred');
            }
        });

        async function loadCustomers() {
            try {
                const response = await fetch('php/get_customers.php');
                const data = await response.json();
                
                const container = document.getElementById('customersContainer');
                
                if (data.status === 'success' && data.data.length > 0) {
                    let html = '<table class="customers-table"><thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Email</th><th>Phone</th><th>City</th><th>Actions</th></tr></thead><tbody>';
                    
                    data.data.forEach(customer => {
                        html += `<tr>
                            <td>${customer.id}</td>
                            <td>${customer.name}</td>
                            <td><span style="background: ${customer.customer_type === 'retail' ? '#e3f2fd' : '#f3e5f5'}; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.85rem;">${customer.customer_type}</span></td>
                            <td>${customer.email || '-'}</td>
                            <td>${customer.phone || '-'}</td>
                            <td>${customer.city || '-'}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-edit" onclick="editCustomer(${customer.id})">Edit</button>
                                    <button class="btn btn-sm btn-delete" onclick="deleteCustomer(${customer.id})">Delete</button>
                                </div>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><p>No customers found. Add your first customer!</p></div>';
                }
            } catch (error) {
                console.error('Error loading customers:', error);
                document.getElementById('customersContainer').innerHTML = '<div class="empty-state"><p>Error loading customers</p></div>';
            }
        }

        function editCustomer(id) {
            fetch(`php/get_customers.php`)
                .then(r => r.json())
                .then(data => {
                    const customer = data.data.find(c => c.id === id);
                    if (customer) {
                        document.getElementById('modalTitle').textContent = 'Edit Customer';
                        document.getElementById('customerId').value = customer.id;
                        document.getElementById('customerName').value = customer.name;
                        document.getElementById('customerEmail').value = customer.email || '';
                        document.getElementById('customerPhone').value = customer.phone || '';
                        document.getElementById('customerType').value = customer.customer_type;
                        document.getElementById('customerAddress').value = customer.address || '';
                        document.getElementById('customerCity').value = customer.city || '';
                        document.getElementById('customerState').value = customer.state || '';
                        document.getElementById('customerPostalCode').value = customer.postal_code || '';
                        document.getElementById('customerCountry').value = customer.country || '';
                        modal.classList.add('show');
                    }
                });
        }

        function deleteCustomer(id) {
            showConfirm('Are you sure you want to delete this customer?', () => {
                fetch('php/delete_customer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showSuccess(data.message);
                        loadCustomers();
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while deleting the customer');
                });
            });
        }

        loadCustomers();

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
