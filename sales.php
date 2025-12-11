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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/toast.css">
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
            margin-top: 2rem;
            font-size: 0.9rem;
        }
        .sales-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        .sales-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .sales-table tr:hover {
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
                <a href="sales.php" class="menu-item active">
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

        <main class="main-content">
            <div class="content-section">
                <div class="section-header">
                    <h2>Sales Management</h2>
                    <button class="btn btn-primary" id="addSaleBtn">
                        <i class="fas fa-plus"></i> Record Sale
                    </button>
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
                    document.getElementById('totalRevenue').textContent = '$' + totalRev.toFixed(2);
                    document.getElementById('averageSale').textContent = '$' + (totalRev / totalCount).toFixed(2);
                    
                    let html = '<table class="sales-table"><thead><tr><th>ID</th><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Total Amount</th><th>Sold By</th><th>Date</th></tr></thead><tbody>';
                    
                    data.data.forEach(sale => {
                        const saleDate = new Date(sale.created_at).toLocaleDateString();
                        html += `<tr>
                            <td>${sale.id}</td>
                            <td>${sale.product_name}</td>
                            <td>${sale.quantity_sold}</td>
                            <td>$${parseFloat(sale.sale_price).toFixed(2)}</td>
                            <td>$${parseFloat(sale.total_amount).toFixed(2)}</td>
                            <td>${sale.sold_by_name}</td>
                            <td>${saleDate}</td>
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
    </script>
    <script src="js/toast.js"></script>
</body>
</html>
