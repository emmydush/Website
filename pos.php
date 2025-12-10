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
    <title>Point of Sale - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/responsive.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
        }
        
        .pos-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .product-card {
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .product-card:hover {
            border-color: #6a11cb;
            background-color: #f0f0f0;
        }
        
        .product-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .product-price {
            color: #6a11cb;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .cart-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            font-size: 14px;
        }
        
        .item-qty {
            color: #777;
            font-size: 12px;
            margin-top: 3px;
        }
        
        .item-price {
            font-weight: 600;
            color: #6a11cb;
        }
        
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .cart-summary {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .summary-row.total {
            font-size: 18px;
            font-weight: 600;
            color: #6a11cb;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .btn-checkout {
            width: 100%;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
        }
        
        .btn-checkout:hover {
            opacity: 0.9;
        }
        
        .btn-checkout:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        @media (max-width: 1024px) {
            .pos-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-left">
            <div class="logo">InventoryPro POS</div>
        </div>
        <div class="nav-right">
            <div class="user-menu" id="userMenu">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff" alt="User" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></span>
            </div>
        </div>
    </nav>

    <div class="container">
        <aside class="sidebar">
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff&size=64" alt="User" class="profile-image">
                <div class="user-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></h3>
                    <p class="user-role"><?php echo htmlspecialchars($userRole ?? 'Staff'); ?></p>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="modern_dashboard.php" class="menu-item"><i class="fas fa-home"></i><span>Home</span></a>
                <a href="products.php" class="menu-item"><i class="fas fa-box"></i><span>Products</span></a>
                <a href="categories.php" class="menu-item"><i class="fas fa-tags"></i><span>Categories</span></a>
                <a href="units.php" class="menu-item"><i class="fas fa-ruler"></i><span>Units</span></a>
                <a href="sales.php" class="menu-item"><i class="fas fa-shopping-cart"></i><span>Sales</span></a>
                <a href="pos.php" class="menu-item active"><i class="fas fa-cash-register"></i><span>Point of Sale</span></a>
                <a href="credit_sales.php" class="menu-item"><i class="fas fa-credit-card"></i><span>Credit Sales</span></a>
                <a href="customers.php" class="menu-item"><i class="fas fa-users"></i><span>Customers</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Settings</span></a>
                <a href="logout.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Point of Sale System</h1>
            
            <div class="pos-container">
                <!-- Products Section -->
                <div class="pos-section">
                    <h2>Products</h2>
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search products...">
                    </div>
                    <div class="product-grid" id="productGrid">
                        <div style="text-align: center; padding: 20px; color: #999;">Loading products...</div>
                    </div>
                </div>

                <!-- Cart Section -->
                <div class="pos-section">
                    <h2>Shopping Cart</h2>
                    <div id="cartItems" style="min-height: 300px;">
                        <p style="color: #999; text-align: center; padding: 40px 0;">Cart is empty</p>
                    </div>
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (10%):</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="total">$0.00</span>
                        </div>
                    </div>
                    <button class="btn-checkout" id="checkoutBtn" disabled>Complete Sale</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        let cart = [];
        const productGrid = document.getElementById('productGrid');
        const cartItems = document.getElementById('cartItems');
        const searchInput = document.getElementById('searchInput');
        const checkoutBtn = document.getElementById('checkoutBtn');

        // Sample products
        const products = [
            { id: 1, name: 'Laptop', price: 1200 },
            { id: 2, name: 'Mouse', price: 25 },
            { id: 3, name: 'Keyboard', price: 80 },
            { id: 4, name: 'Monitor', price: 300 },
            { id: 5, name: 'Headphones', price: 150 },
            { id: 6, name: 'Desk', price: 200 }
        ];

        document.addEventListener('DOMContentLoaded', loadProducts);

        function loadProducts() {
            productGrid.innerHTML = '';
            products.forEach(product => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
                    <div class="product-name">${product.name}</div>
                    <div class="product-price">$${product.price}</div>
                `;
                card.onclick = () => addToCart(product);
                productGrid.appendChild(card);
            });
        }

        function addToCart(product) {
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.qty++;
            } else {
                cart.push({ ...product, qty: 1 });
            }
            updateCart();
        }

        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            updateCart();
        }

        function updateCart() {
            cartItems.innerHTML = '';
            if (cart.length === 0) {
                cartItems.innerHTML = '<p style="color: #999; text-align: center; padding: 40px 0;">Cart is empty</p>';
                checkoutBtn.disabled = true;
            } else {
                cart.forEach(item => {
                    const itemEl = document.createElement('div');
                    itemEl.className = 'cart-item';
                    itemEl.innerHTML = `
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-qty">Qty: ${item.qty} × $${item.price}</div>
                        </div>
                        <div>
                            <div class="item-price">$${(item.price * item.qty).toFixed(2)}</div>
                            <button class="remove-btn" onclick="window.removeFromCart(${item.id})">Remove</button>
                        </div>
                    `;
                    cartItems.appendChild(itemEl);
                });
                checkoutBtn.disabled = false;
            }
            updateSummary();
        }

        function updateSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const tax = subtotal * 0.10;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        }

        checkoutBtn.addEventListener('click', () => {
            alert(`Sale completed! Total: $${document.getElementById('total').textContent}`);
            cart = [];
            updateCart();
        });
    </script>
</body>
</html>
