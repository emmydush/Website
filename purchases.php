<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Database connection
require_once 'php/db_connect.php';

// Get user info
$userName = $_SESSION['username'] ?? 'User';
$userRole = $_SESSION['role'] ?? 'Staff';

// Get notification count
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
    $notificationCount = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchases - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/emmanuel/css/modern_dashboard.css">
    <style>
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 25px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        margin-bottom: 25px;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }

    .close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #999;
        transition: color 0.2s;
    }

    .close:hover {
        color: #333;
    }

    .modal-content form {
        padding: 0;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-half {
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #777;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #6a11cb;
        box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        margin-top: 30px;
    }

    /* Button Styles */
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        opacity: 0.9;
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .btn-cancel {
        background-color: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    .edit-btn,
    .delete-btn,
    .receive-btn {
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .edit-btn {
        background-color: #ffc107;
        color: #212529;
    }

    .edit-btn:hover {
        background-color: #e0a800;
        transform: scale(1.05);
    }

    .delete-btn {
        background-color: #dc3545;
        color: white;
    }

    .delete-btn:hover {
        background-color: #c82333;
        transform: scale(1.05);
    }

    .receive-btn {
        background-color: #17a2b8;
        color: white;
    }

    .receive-btn:hover {
        background-color: #138496;
        transform: scale(1.05);
    }

    .form-text {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
        display: block;
    }

    .text-muted {
        color: #6c757d;
    }

    .text-info {
        color: #17a2b8;
        font-weight: 500;
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-received {
        background: #d4edda;
        color: #155724;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    /* Stats Cards */
    .stats-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        flex: 1;
    }

    .stat-card h3 {
        margin-top: 0;
        color: #666;
        font-size: 16px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #4CAF50;
    }

    /* Filter Box */
    .filter-box {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-group label {
        font-weight: bold;
        white-space: nowrap;
    }

    .filter-group select,
    .filter-group input {
        width: auto;
        min-width: 120px;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
    }

    /* Search Box */
    .search-box {
        margin-bottom: 20px;
    }

    .search-box input {
        width: 300px;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
    }

    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    th {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover {
        background-color: #f8f9fa;
    }

    .actions {
        display: flex;
        gap: 8px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .filter-box {
            flex-direction: column;
            gap: 10px;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            min-width: auto;
        }

        .stats-container {
            flex-direction: column;
            gap: 15px;
        }

        .search-box input {
            width: 100%;
        }

        .actions {
            gap: 5px;
        }

        .edit-btn,
        .delete-btn,
        .receive-btn {
            padding: 6px 10px;
        }
    }
</style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-left">
            <div class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </div>
            <h1 class="page-title">Purchases</h1>
        </div>
        
        <div class="nav-right">
            <div class="notifications" id="notificationBell">
                <i class="fas fa-bell"></i>
                <span class="notification-badge"><?php echo $notificationCount; ?></span>
            </div>
            
            <div class="user-menu" id="userMenu">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff" alt="User" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            
            <div class="user-dropdown" id="userDropdown">
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
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
                <h3><?php echo htmlspecialchars($userName); ?></h3>
                <p><?php echo ucfirst($userRole); ?></p>
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
                <a href="purchases.php" class="menu-item active">
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
            <div class="page-header">
                <h2>Purchase Management</h2>
                <button class="btn-primary" id="addPurchaseBtn"><i class="fas fa-plus"></i> Add New Purchase</button>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Pending Purchases</h3>
                    <div class="stat-value" id="pendingCount">0</div>
                </div>
                <div class="stat-card">
                    <h3>Total Purchase Value</h3>
                    <div class="stat-value" id="totalValue">$0.00</div>
                </div>
            </div>
            
            <div class="filter-box">
                <div class="filter-group">
                    <label for="statusFilter">Status:</label>
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="supplierFilter">Supplier:</label>
                    <select id="supplierFilter">
                        <option value="">All Suppliers</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="dateFrom">From:</label>
                    <input type="date" id="dateFrom">
                </div>
                <div class="filter-group">
                    <label for="dateTo">To:</label>
                    <input type="date" id="dateTo">
                </div>
                <button class="btn" id="applyFilters">Apply Filters</button>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search purchases...">
            </div>
            
            <div class="table-container">
                <table id="purchasesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="purchasesTableBody">
                        <!-- Purchase data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Add/Edit Purchase Modal -->
        <div id="purchaseModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Add New Purchase</h2>
                    <span class="close">&times;</span>
                </div>
                <form id="purchaseForm">
                    <input type="hidden" id="purchaseId" value="">
                    <div class="form-group">
                        <label for="supplierId">Supplier *</label>
                        <select id="supplierId" required>
                            <option value="">Select Supplier</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="productId">Product *</label>
                        <select id="productId" required>
                            <option value="">Select Product</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="unitPrice">Unit Price *</label>
                        <input type="number" id="unitPrice" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="totalAmount">Total Amount</label>
                        <input type="number" id="totalAmount" step="0.01" readonly>
                    </div>
                    <div class="form-group">
                        <label for="purchaseDate">Purchase Date *</label>
                        <input type="date" id="purchaseDate" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" rows="3" placeholder="Optional: Add notes about this purchase"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelBtn"><i class="fas fa-times"></i> Cancel</button>
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // DOM Elements
    const addPurchaseBtn = document.getElementById('addPurchaseBtn');
    const purchaseModal = document.getElementById('purchaseModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');
    const purchaseForm = document.getElementById('purchaseForm');
    const modalTitle = document.getElementById('modalTitle');
    const purchasesTableBody = document.getElementById('purchasesTableBody');
    const searchInput = document.getElementById('searchInput');
    const applyFiltersBtn = document.getElementById('applyFilters');

    // Load purchases when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadPurchases();
    });

    // Calculate total amount
    document.getElementById('quantity').addEventListener('input', calculateTotal);
    document.getElementById('unitPrice').addEventListener('input', calculateTotal);

    function calculateTotal() {
        const quantity = parseFloat(document.getElementById('quantity').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unitPrice').value) || 0;
        const totalAmount = quantity * unitPrice;
        document.getElementById('totalAmount').value = totalAmount.toFixed(2);
    }

    // Open modal for adding new purchase
    addPurchaseBtn.addEventListener('click', () => {
        // Reset form
        purchaseForm.reset();
        document.getElementById('purchaseId').value = '';
        document.getElementById('totalAmount').value = '';
        modalTitle.textContent = 'Add New Purchase';
        purchaseModal.style.display = 'flex';
        // Set today's date as default
        document.getElementById('purchaseDate').value = new Date().toISOString().split('T')[0];
        // Load suppliers and products
        loadSuppliers();
        loadProducts();
        // Focus on first field
        document.getElementById('supplierId').focus();
    });

    // Close modal
    closeBtn.addEventListener('click', () => {
        purchaseModal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', () => {
        purchaseModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === purchaseModal) {
            purchaseModal.style.display = 'none';
        }
    });

    // Handle form submission
    purchaseForm.addEventListener('submit', (e) => {
        e.preventDefault();
        savePurchase();
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        loadPurchases();
    });

    // Apply filters
    applyFiltersBtn.addEventListener('click', function() {
        loadPurchases();
    });

    // Function to load purchases
    function loadPurchases() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const supplier = document.getElementById('supplierFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        const params = new URLSearchParams({
            search: search,
            status: status,
            supplier_id: supplier,
            date_from: dateFrom,
            date_to: dateTo
        });
        
        fetch(`php/get_purchases.php?${params}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('purchasesTableBody');
                tbody.innerHTML = '';
                
                if (data.status === 'success') {
                    // Update stats
                    document.getElementById('pendingCount').textContent = data.stats.pending_count || 0;
                    document.getElementById('totalValue').textContent = '$' + parseFloat(data.stats.total_value || 0).toFixed(2);
                    
                    // Populate supplier filter
                    const supplierFilter = document.getElementById('supplierFilter');
                    const currentSupplier = supplierFilter.value;
                    supplierFilter.innerHTML = '<option value="">All Suppliers</option>';
                    
                    data.suppliers.forEach(supplier => {
                        const option = document.createElement('option');
                        option.value = supplier.id;
                        option.textContent = supplier.name;
                        supplierFilter.appendChild(option);
                    });
                    
                    supplierFilter.value = currentSupplier;
                    
                    // Populate purchases table
                    data.purchases.forEach(purchase => {
                        const statusClass = `status-${purchase.status}`;
                        const statusText = purchase.status.charAt(0).toUpperCase() + purchase.status.slice(1);
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${purchase.id}</td>
                            <td>${purchase.supplier_name}</td>
                            <td>${purchase.product_name}</td>
                            <td>${purchase.quantity}</td>
                            <td>$${parseFloat(purchase.unit_price).toFixed(2)}</td>
                            <td>$${parseFloat(purchase.total_amount).toFixed(2)}</td>
                            <td>${purchase.purchase_date}</td>
                            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                            <td class="actions">
                                ${purchase.status === 'pending' ? 
                                    `<button class="receive-btn" onclick="receivePurchase(${purchase.id})">
                                        <i class="fas fa-check"></i> Receive
                                    </button>` : ''}
                                <button class="edit-btn" onclick="editPurchase(${purchase.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="delete-btn" onclick="deletePurchase(${purchase.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading purchases:', error);
            });
    }
    
    // Function to load suppliers
    function loadSuppliers() {
        fetch('php/get_suppliers.php')
            .then(response => response.json())
            .then(data => {
                const supplierSelect = document.getElementById('supplierId');
                supplierSelect.innerHTML = '<option value="">Select Supplier</option>';
                
                if (data.status === 'success') {
                    data.suppliers.forEach(supplier => {
                        const option = document.createElement('option');
                        option.value = supplier.id;
                        option.textContent = supplier.name;
                        supplierSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading suppliers:', error);
            });
    }
    
    // Function to load products
    function loadProducts() {
        fetch('php/get_products.php')
            .then(response => response.json())
            .then(data => {
                const productSelect = document.getElementById('productId');
                productSelect.innerHTML = '<option value="">Select Product</option>';
                
                if (data.status === 'success') {
                    data.products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        productSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
            });
    }
    
    // Function to save purchase
    function savePurchase() {
        const id = document.getElementById('purchaseId').value;
        const supplierId = document.getElementById('supplierId').value;
        const productId = document.getElementById('productId').value;
        const quantity = document.getElementById('quantity').value;
        const unitPrice = document.getElementById('unitPrice').value;
        const totalAmount = document.getElementById('totalAmount').value;
        const purchaseDate = document.getElementById('purchaseDate').value;
        const notes = document.getElementById('notes').value;
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('supplier_id', supplierId);
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        formData.append('unit_price', unitPrice);
        formData.append('total_amount', totalAmount);
        formData.append('purchase_date', purchaseDate);
        formData.append('notes', notes);
        
        const url = id ? 'php/update_purchase.php' : 'php/add_purchase.php';
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('purchaseForm').style.display = 'none';
                loadPurchases();
                alert('Purchase saved successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error saving purchase:', error);
            alert('Error saving purchase');
        });
    }
    
    // Function to edit purchase
    function editPurchase(id) {
        fetch(`php/get_purchase.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const purchase = data.purchase;
                    document.getElementById('purchaseId').value = purchase.id;
                    document.getElementById('supplierId').value = purchase.supplier_id;
                    document.getElementById('productId').value = purchase.product_id;
                    document.getElementById('quantity').value = purchase.quantity;
                    document.getElementById('unitPrice').value = purchase.unit_price;
                    document.getElementById('totalAmount').value = purchase.total_amount;
                    document.getElementById('purchaseDate').value = purchase.purchase_date;
                    document.getElementById('notes').value = purchase.notes || '';
                    
                    document.getElementById('formTitle').textContent = 'Edit Purchase';
                    document.getElementById('purchaseForm').style.display = 'block';
                    
                    // Load suppliers and products
                    loadSuppliers();
                    loadProducts();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error loading purchase:', error);
                alert('Error loading purchase');
            });
    }
    
    // Function to delete purchase
    function deletePurchase(id) {
        if (confirm('Are you sure you want to delete this purchase?')) {
            fetch('php/delete_purchase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadPurchases();
                    alert('Purchase deleted successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting purchase:', error);
                alert('Error deleting purchase');
            });
        }
    }
    
    // Function to receive purchase
    function receivePurchase(id) {
        if (confirm('Mark this purchase as received? This will update the product inventory.')) {
            fetch('php/receive_purchase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadPurchases();
                    alert('Purchase marked as received and inventory updated!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error receiving purchase:', error);
                alert('Error receiving purchase');
            });
        }
    }
    
    // Load notifications
    function loadNotifications() {
        fetch('php/stock_alerts.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.alerts.length > 0) {
                    createNotificationDropdown(data.alerts);
                } else {
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
    
    // Notification badge click handler
    const notificationBadge = document.querySelector('.notifications');
    if (notificationBadge) {
        notificationBadge.addEventListener('click', function(e) {
            e.stopPropagation();
            loadNotifications();
        });
    }
    
    // Load purchases on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set default dates
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        
        loadPurchases();
    });
    </script>
    <script src="/emmanuel/js/avatar.js"></script>
</body>
</html>