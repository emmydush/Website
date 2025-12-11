<?php
session_start();
require_once 'php/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.html');
    exit();
}

// Initialize ALL variables to prevent undefined variable warnings
$totalProducts = $lowStockItems = $outOfStockItems = $totalCategories = 0;
$todaysSales = $stockValue = 0.00;
$userName = $userRole = "";

// Initialize variables with default values
$totalProducts = 1248;
$lowStockItems = 24;
$outOfStockItems = 8;
$totalCategories = 18;
$todaysSales = 2450.00;
$stockValue = 42680.00;
$userName = isset($_SESSION['username']) ? $_SESSION['username'] : "Emmanuel";
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : "Administrator";

// Fetch categories and suppliers for the form
$categories = [];
$suppliers = [];

try {
    // Get categories
    $stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get suppliers
    $stmt = $pdo->prepare("SELECT id, name FROM suppliers ORDER BY name");
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Products page error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/toast.css">
    <style>
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
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
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        
        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .products-table th {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        .products-table tr:last-child td {
            border-bottom: none;
        }
        
        .products-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit, .btn-delete {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
            transform: scale(1.05);
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: color 0.2s;
        }
        
        .close:hover {
            color: #333;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-half {
            margin-bottom: 0;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #6a11cb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 14px;
        }
        
        .form-text.text-muted {
            color: #6c757d;
        }
        
        .form-text.text-info {
            color: #17a2b8;
            font-weight: 500;
        }
        
        .scanner-active {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25) !important;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .search-container {
            margin-bottom: 25px;
        }
        
        .search-bar {
            display: flex;
            gap: 10px;
            max-width: 500px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .search-bar button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .search-bar button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-in {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-low {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-out {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .products-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-bar {
                max-width: 100%;
            }
            
            .products-table {
                font-size: 14px;
            }
            
            .products-table th,
            .products-table td {
                padding: 10px 8px;
            }
            
            .action-buttons {
                gap: 5px;
            }
            
            .btn-edit, .btn-delete {
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
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
                <span class="notification-badge">3</span>
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
                <a href="products.php" class="menu-item active">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-ruler"></i>
                    <span>Units</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
                <a href="pos_system.php" class="menu-item">
                    <i class="fas fa-cash-register"></i>
                    <span>Point of Sale</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Credit Sales</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="#" class="menu-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="products-header">
                <h1>Products Management</h1>
                <button class="btn-primary" id="addProductBtn">
                    <i class="fas fa-plus"></i> Add New Product
                </button>
            </div>

            <div class="search-container">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search products by name, barcode, category, or supplier...">
                    <button id="searchBtn"><i class="fas fa-search"></i> Search</button>
                </div>
            </div>

            <div class="table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <!-- Products will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <span class="close">&times;</span>
            </div>
            <form id="productForm">
                <input type="hidden" id="productId" value="">
                <div class="form-group">
                    <label for="productName">Product Name *</label>
                    <input type="text" id="productName" required>
                </div>
                <div class="form-row">
                    <div class="form-group form-half">
                        <label for="productCategory">Category *</label>
                        <select id="productCategory" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-half">
                        <label for="productSupplier">Supplier *</label>
                        <select id="productSupplier" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-half">
                        <label for="productPrice">Price ($) *</label>
                        <input type="number" id="productPrice" step="0.01" min="0" required>
                    </div>
                    <div class="form-group form-half">
                        <label for="productQuantity">Quantity *</label>
                        <input type="number" id="productQuantity" min="0" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-half">
                        <label for="productMinStock">Min Stock Level</label>
                        <input type="number" id="productMinStock" min="0" value="10">
                    </div>
                    <div class="form-group form-half">
                        <label for="productBarcode">Barcode</label>
                        <input type="text" id="productBarcode">
                        <small class="form-text text-muted">Supports barcode scanners - press Enter after scanning to save</small>
                        <small class="form-text text-info">Shortcut: Press Ctrl+B to focus on barcode field</small>
                        <small class="form-text text-muted" id="scannerStatus" style="display: none; color: #28a745;">Scanner ready - scan barcode now</small>
                        <button type="button" class="btn-secondary" id="cameraScanBtn" style="margin-top: 10px;">
                            <i class="fas fa-camera"></i> Scan with Camera
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" rows="2" placeholder="Optional: Add product description"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <script src="js/modern_dashboard.js"></script>
    <script>
        // DOM Elements
        const addProductBtn = document.getElementById('addProductBtn');
        const productModal = document.getElementById('productModal');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const productForm = document.getElementById('productForm');
        const modalTitle = document.getElementById('modalTitle');
        const productsTableBody = document.getElementById('productsTableBody');
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const cameraScanBtn = document.getElementById('cameraScanBtn');

        // Load products when page loads
        document.addEventListener('DOMContentLoaded', loadProducts);

        // Open modal for adding new product
        addProductBtn.addEventListener('click', () => {
            // Reset form
            productForm.reset();
            document.getElementById('productId').value = '';
            document.getElementById('productMinStock').value = '10';
            modalTitle.textContent = 'Add New Product';
            productModal.style.display = 'flex';
            // Focus on first field
            document.getElementById('productName').focus();
        });

        // Close modal
        closeBtn.addEventListener('click', () => {
            productModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            productModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === productModal) {
                productModal.style.display = 'none';
            }
        });

        // Camera scan button event
        cameraScanBtn.addEventListener('click', startCameraScan);

        // Handle form submission
        productForm.addEventListener('submit', (e) => {
            e.preventDefault();
            saveProduct();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+B to focus on barcode field when modal is open
            if (e.ctrlKey && e.key === 'b' && productModal.style.display === 'flex') {
                e.preventDefault();
                document.getElementById('productBarcode').focus();
            }
            
            // If we're in a form field, don't interfere with normal typing
            if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') {
                return;
            }

            // Clear buffer if timeout has passed
            if (barcodeTimeout) {
                clearTimeout(barcodeTimeout);
            }

            // Add character to buffer
            if (e.key === 'Enter') {
                // Barcode scan completed
                if (barcodeBuffer.length > 0) {
                    // If we're on the products page and the modal is open, fill the barcode field
                    if (productModal.style.display === 'flex') {
                        const barcodeField = document.getElementById('productBarcode');
                        barcodeField.value = barcodeBuffer;
                        // Add visual feedback
                        barcodeField.classList.add('scanner-active');
                        setTimeout(() => {
                            barcodeField.classList.remove('scanner-active');
                        }, 1000);
                        // Focus on the field to show the scanned value
                        barcodeField.focus();
                    }
                    barcodeBuffer = '';
                }
            } else if (e.key.length === 1) {
                // Add character to buffer
                barcodeBuffer += e.key;
                
                // Set timeout to clear buffer (barcode scanners typically send characters very quickly)
                barcodeTimeout = setTimeout(() => {
                    barcodeBuffer = '';
                }, 100);
            }
        });

        // Barcode scanner support - detect when barcode is scanned
        let barcodeBuffer = '';
        let barcodeTimeout;

        // Special handling for barcode input in the barcode field
        const barcodeField = document.getElementById('productBarcode');
        const scannerStatus = document.getElementById('scannerStatus');
        let fieldBarcodeBuffer = '';
        let fieldBarcodeTimeout;

        // Show scanner status when barcode field is focused
        barcodeField.addEventListener('focus', () => {
            scannerStatus.style.display = 'block';
        });

        // Hide scanner status when barcode field loses focus
        barcodeField.addEventListener('blur', () => {
            scannerStatus.style.display = 'none';
        });

        barcodeField.addEventListener('input', (e) => {
            // If the input is coming from a barcode scanner (fast input), 
            // we'll handle it differently than manual typing
            if (fieldBarcodeTimeout) {
                clearTimeout(fieldBarcodeTimeout);
            }

            fieldBarcodeTimeout = setTimeout(() => {
                // If we have a buffered barcode and the field is empty or has the same value
                if (fieldBarcodeBuffer && (!e.target.value || e.target.value === fieldBarcodeBuffer)) {
                    e.target.value = fieldBarcodeBuffer;
                    // Add visual feedback for successful scan
                    e.target.classList.add('scanner-active');
                    setTimeout(() => {
                        e.target.classList.remove('scanner-active');
                    }, 1000);
                }
                fieldBarcodeBuffer = '';
            }, 50);
        });

        barcodeField.addEventListener('keydown', (e) => {
            if (fieldBarcodeTimeout) {
                clearTimeout(fieldBarcodeTimeout);
            }

            if (e.key.length === 1) {
                fieldBarcodeBuffer += e.key;
                fieldBarcodeTimeout = setTimeout(() => {
                    fieldBarcodeBuffer = '';
                }, 50);
            } else if (e.key === 'Enter') {
                // When Enter is pressed in barcode field, save the product
                e.preventDefault();
                saveProduct();
            }
        });

        // Search functionality
        searchBtn.addEventListener('click', () => {
            loadProducts(searchInput.value);
        });

        // Also search when pressing Enter in search box
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                loadProducts(searchInput.value);
            }
        });

        // Load products from server
        function loadProducts(searchTerm = '') {
            fetch('php/get_products.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayProducts(data.data, searchTerm);
                    } else {
                        console.error('Error loading products:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                });
        }

        // Display products in table
        function displayProducts(products, searchTerm = '') {
            // Filter products based on search term
            if (searchTerm) {
                products = products.filter(product => 
                    product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.barcode?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.category_name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.supplier_name?.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            // Clear table
            productsTableBody.innerHTML = '';

            // Show message if no products found
            if (products.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        No products found. ${searchTerm ? 'Try a different search term.' : 'Add a new product to get started.'}
                    </td>
                `;
                productsTableBody.appendChild(row);
                return;
            }

            // Add products to table
            products.forEach(product => {
                const row = document.createElement('tr');
                
                // Determine stock status
                let status = '';
                let statusClass = '';
                if (product.quantity == 0) {
                    status = 'Out of Stock';
                    statusClass = 'status-out';
                } else if (product.quantity <= product.min_stock_level) {
                    status = 'Low Stock';
                    statusClass = 'status-low';
                } else {
                    status = 'In Stock';
                    statusClass = 'status-in';
                }

                row.innerHTML = `
                    <td>${product.id}</td>
                    <td>
                        <strong>${product.name}</strong>
                        ${product.barcode ? `<br><small>Barcode: ${product.barcode}</small>` : ''}
                    </td>
                    <td>${product.category_name || 'N/A'}</td>
                    <td>${product.supplier_name || 'N/A'}</td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td>${product.quantity}</td>
                    <td><span class="status ${statusClass}">${status}</span></td>
                    <td class="action-buttons">
                        <button class="btn-edit" onclick="editProduct(${product.id})" title="Edit Product">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete" onclick="deleteProduct(${product.id})" title="Delete Product">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                productsTableBody.appendChild(row);
            });
        }

        // Save product (add or update)
        function saveProduct() {
            // Form validation
            const name = document.getElementById('productName').value.trim();
            const categoryId = document.getElementById('productCategory').value;
            const supplierId = document.getElementById('productSupplier').value;
            const price = document.getElementById('productPrice').value;
            const quantity = document.getElementById('productQuantity').value;
            
            if (!name) {
                alert('Product name is required');
                return;
            }
            
            if (!categoryId) {
                alert('Please select a category');
                return;
            }
            
            if (!supplierId) {
                alert('Please select a supplier');
                return;
            }
            
            if (!price || parseFloat(price) <= 0) {
                alert('Please enter a valid price');
                return;
            }
            
            if (!quantity || parseInt(quantity) < 0) {
                alert('Please enter a valid quantity');
                return;
            }
            
            const productId = document.getElementById('productId').value;
            const productData = {
                name: name,
                description: document.getElementById('productDescription').value.trim(),
                category_id: categoryId,
                supplier_id: supplierId,
                price: parseFloat(price).toFixed(2),
                quantity: parseInt(quantity),
                min_stock_level: parseInt(document.getElementById('productMinStock').value) || 10,
                barcode: document.getElementById('productBarcode').value.trim()
            };

            // Determine endpoint based on whether we're adding or updating
            const endpoint = productId ? 'php/update_product.php' : 'php/add_product.php';
            
            // Add ID for updates
            if (productId) {
                productData.id = productId;
            }

            // Show loading indicator
            const saveBtn = productForm.querySelector('button[type="submit"]');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = 'Saving...';
            saveBtn.disabled = true;

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(productData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success message
                    alert(data.message);
                    productModal.style.display = 'none';
                    loadProducts(); // Reload products
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving product:', error);
                alert('An error occurred while saving the product');
            })
            .finally(() => {
                // Restore button state
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            });
        }

        // Edit product
        function editProduct(productId) {
            // Fetch product details from server
            fetch(`php/get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const product = data.data;
                        
                        // Populate form with product data
                        document.getElementById('productId').value = product.id;
                        document.getElementById('productName').value = product.name;
                        document.getElementById('productDescription').value = product.description || '';
                        document.getElementById('productCategory').value = product.category_id || '';
                        document.getElementById('productSupplier').value = product.supplier_id || '';
                        document.getElementById('productPrice').value = product.price;
                        document.getElementById('productQuantity').value = product.quantity;
                        document.getElementById('productMinStock').value = product.min_stock_level || 10;
                        document.getElementById('productBarcode').value = product.barcode || '';
                        
                        modalTitle.textContent = 'Edit Product';
                        productModal.style.display = 'flex';
                    } else {
                        alert('Error loading product: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading product:', error);
                    alert('An error occurred while loading the product');
                });
        }

        // Delete product
        function deleteProduct(productId) {
            // Confirm deletion
            showConfirm('Are you sure you want to delete this product? This action cannot be undone.', () => {
                fetch('php/delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showSuccess('Product deleted successfully!');
                        loadProducts(); // Reload the products table
                    } else {
                        showError('Error deleting product: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while deleting the product');
                });
            });
        }

        // Start camera-based barcode scanning
        function startCameraScan() {
            // Check if the browser supports the necessary APIs
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Your browser does not support camera access. Please try Chrome, Firefox, or Edge.');
                return;
            }

            // Create a modal for the camera scanner
            const scannerModal = document.createElement('div');
            scannerModal.id = 'cameraScannerModal';
            scannerModal.className = 'modal';
            scannerModal.style.display = 'flex';
            scannerModal.innerHTML = `
                <div class="modal-content" style="width: 90%; max-width: 800px; padding: 20px;">
                    <div class="modal-header">
                        <h2>Scan Barcode</h2>
                        <span class="close" id="scannerClose">&times;</span>
                    </div>
                    <div style="text-align: center; margin: 20px 0;">
                        <div id="scannerVideoContainer" style="position: relative; display: inline-block; width: 100%; max-width: 500px;">
                            <video id="scannerVideo" style="width: 100%; border: 2px solid #ddd; border-radius: 10px;"></video>
                            <canvas id="scannerCanvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
                        </div>
                        <div id="scannerMessage" style="margin-top: 15px; font-size: 18px; font-weight: bold;">
                            Initializing camera...
                        </div>
                        <button id="stopScannerBtn" class="btn-cancel" style="margin-top: 15px;">Cancel Scan</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(scannerModal);
            
            const scannerClose = document.getElementById('scannerClose');
            const stopScannerBtn = document.getElementById('stopScannerBtn');
            const scannerVideo = document.getElementById('scannerVideo');
            const scannerMessage = document.getElementById('scannerMessage');
            
            let stream = null;
            
            // Close scanner modal
            const closeScanner = () => {
                // Stop Quagga if it's running
                if (Quagga && typeof Quagga.stop === 'function') {
                    Quagga.stop();
                }
                
                // Stop camera stream
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
                
                scannerModal.remove();
            };
            
            scannerClose.addEventListener('click', closeScanner);
            stopScannerBtn.addEventListener('click', closeScanner);
            
            // Close when clicking outside
            scannerModal.addEventListener('click', (e) => {
                if (e.target === scannerModal) {
                    closeScanner();
                }
            });
            
            // Initialize QuaggaJS for barcode scanning
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector("#scannerVideoContainer"),
                    constraints: {
                        width: 640,
                        height: 480,
                        facingMode: "environment" // Use rear camera if available
                    }
                },
                decoder: {
                    readers: [
                        "code_128_reader",
                        "ean_reader",
                        "ean_8_reader",
                        "code_39_reader",
                        "code_39_vin_reader",
                        "codabar_reader",
                        "upc_reader",
                        "upc_e_reader",
                        "i2of5_reader"
                    ]
                }
            }, function(err) {
                if (err) {
                    console.error('QuaggaJS initialization error:', err);
                    scannerMessage.innerHTML = '<span style="color: #dc3545;">Error initializing scanner: ' + err.message + '</span>';
                    return;
                }
                
                scannerMessage.innerHTML = '<span style="color: #ffc107;">Camera active - Point at barcode</span>';
                Quagga.start();
            });
            
            // When a barcode is detected
            Quagga.onDetected(function(data) {
                const code = data.codeResult.code;
                scannerMessage.innerHTML = `<span style="color: #28a745;">Scanned: ${code}</span>`;
                
                // Fill the barcode field in the main form
                document.getElementById('productBarcode').value = code;
                document.getElementById('productBarcode').classList.add('scanner-active');
                setTimeout(() => {
                    document.getElementById('productBarcode').classList.remove('scanner-active');
                }, 1000);
                
                // Stop scanning and close modal
                setTimeout(() => {
                    closeScanner();
                }, 1500);
            });
        }

        // Display products in product cards
        function displayProductsInCards(products, searchTerm = '') {
            // Filter products based on search term
            if (searchTerm) {
                products = products.filter(product => 
                    product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.barcode?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.category_name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    product.supplier_name?.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            // Clear container
            productsContainer.innerHTML = '';

            // Show message if no products found
            if (products.length === 0) {
                productsContainer.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        No products found. ${searchTerm ? 'Try a different search term.' : 'Add a new product to get started.'}
                    </div>
                `;
                return;
            }

            // Add products to container
            let html = '';
            products.forEach(product => {
                html += `
                <div class="product-card">
                    <div class="product-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="product-name">${product.name}</div>
                    <div class="product-price">FRW ${parseFloat(product.price).toLocaleString()}</div>
                    <div class="product-quantity">${parseFloat(product.quantity).toFixed(2)} ${product.unit_name || 'PCS'} left</div>
                    ${product.has_variants ? '<div class="variant-badge">Has Variants</div>' : ''}
                    <button class="add-btn" onclick="addToCart(${product.id}, '${product.name}', ${product.price})">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>`;
            });
            productsContainer.innerHTML = html;
        }

    </script>
</body>
</html>
