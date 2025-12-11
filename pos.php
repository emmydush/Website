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
    <title>Point of Sale - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/toast.css">
    <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }
        @media (max-width: 1200px) {
            .pos-container {
                grid-template-columns: 1fr;
            }
        }
        .pos-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .pos-section h2 {
            margin: 0 0 1.5rem 0;
            color: #333;
            font-size: 1.5rem;
        }
        .search-box {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .search-box input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .search-box button {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .product-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
            max-height: 500px;
            overflow-y: auto;
        }
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        .product-card:hover {
            background: #f5f5f5;
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .product-card h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 0.9rem;
        }
        .product-price {
            font-size: 1.25rem;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .product-stock {
            font-size: 0.85rem;
            color: #999;
        }
        .cart-items {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            gap: 1rem;
        }
        .cart-item-details {
            flex: 1;
        }
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .cart-item-qty {
            font-size: 0.85rem;
            color: #999;
        }
        .cart-item-total {
            font-weight: 600;
            min-width: 80px;
            text-align: right;
        }
        .qty-input {
            width: 50px;
            padding: 0.4rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .remove-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .cart-summary {
            background: #f5f5f5;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        .summary-row.total {
            border-top: 2px solid #ddd;
            padding-top: 0.75rem;
            font-size: 1.25rem;
            font-weight: bold;
            color: #667eea;
        }
        .payment-method {
            margin-bottom: 1.5rem;
        }
        .payment-method label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        .payment-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
        .payment-option {
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        .payment-option.active {
            border-color: #667eea;
            background: #f0f1ff;
        }
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-clear {
            width: 100%;
            padding: 0.75rem;
            background: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .empty-message {
            text-align: center;
            color: #999;
            padding: 2rem;
        }
        .empty-message i {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
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
                <a href="pos.php" class="menu-item active">
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
            <div class="pos-container">
                <div class="pos-section">
                    <h2>Available Products</h2>
                    <div class="search-box">
                        <input type="text" id="productSearch" placeholder="Search products...">
                        <button onclick="searchProducts()"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="product-list" id="productList">
                        <div class="empty-message">
                            <i class="fas fa-box"></i>
                            <p>Loading products...</p>
                        </div>
                    </div>
                </div>

                <div class="pos-section">
                    <h2>Shopping Cart</h2>
                    <div class="cart-items" id="cartItems">
                        <div class="empty-message">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Cart is empty</p>
                        </div>
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

                    <div class="payment-method">
                        <label>Payment Method</label>
                        <div class="payment-options">
                            <div class="payment-option active" onclick="setPayment('cash')">
                                <i class="fas fa-money-bill"></i> Cash
                            </div>
                            <div class="payment-option" onclick="setPayment('card')">
                                <i class="fas fa-credit-card"></i> Card
                            </div>
                            <div class="payment-option" onclick="setPayment('check')">
                                <i class="fas fa-check"></i> Check
                            </div>
                            <div class="payment-option" onclick="setPayment('transfer')">
                                <i class="fas fa-exchange-alt"></i> Transfer
                            </div>
                        </div>
                        <input type="hidden" id="paymentMethod" value="cash">
                    </div>

                    <button class="btn-checkout" onclick="checkout()">
                        <i class="fas fa-check-circle"></i> Complete Sale
                    </button>
                    <button class="btn-clear" onclick="clearCart()">Clear Cart</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        let cart = {};

        async function loadProducts() {
            try {
                const response = await fetch('php/get_products.php');
                const data = await response.json();
                
                const productList = document.getElementById('productList');
                
                if (data.status === 'success' && data.data.length > 0) {
                    let html = '';
                    data.data.forEach(product => {
                        if (product.quantity > 0) {
                            html += `<div class="product-card" onclick="addToCart(${product.id}, '${product.name}', ${product.price})">
                                <h4>${product.name}</h4>
                                <div class="product-price">$${parseFloat(product.price).toFixed(2)}</div>
                                <div class="product-stock">Stock: ${product.quantity}</div>
                            </div>`;
                        }
                    });
                    productList.innerHTML = html;
                } else {
                    productList.innerHTML = '<div class="empty-message"><i class="fas fa-box"></i><p>No products available</p></div>';
                }
            } catch (error) {
                console.error('Error loading products:', error);
            }
        }

        function addToCart(productId, productName, price) {
            if (cart[productId]) {
                cart[productId].quantity++;
            } else {
                cart[productId] = {
                    name: productName,
                    price: price,
                    quantity: 1
                };
            }
            updateCart();
        }

        function updateCart() {
            const cartItems = document.getElementById('cartItems');
            
            if (Object.keys(cart).length === 0) {
                cartItems.innerHTML = '<div class="empty-message"><i class="fas fa-shopping-cart"></i><p>Cart is empty</p></div>';
                updateSummary();
                return;
            }

            let html = '';
            let subtotal = 0;

            Object.entries(cart).forEach(([id, item]) => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                html += `<div class="cart-item">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-qty">$${item.price.toFixed(2)} × <input type="number" class="qty-input" value="${item.quantity}" min="1" onchange="updateQuantity(${id}, this.value)"></div>
                    </div>
                    <div class="cart-item-total">$${itemTotal.toFixed(2)}</div>
                    <button class="remove-btn" onclick="removeFromCart(${id})">Remove</button>
                </div>`;
            });

            cartItems.innerHTML = html;
            
            const tax = subtotal * 0.1;
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('tax').textContent = '$' + tax.toFixed(2);
            document.getElementById('total').textContent = '$' + total.toFixed(2);
        }

        function updateQuantity(id, newQty) {
            const qty = parseInt(newQty);
            if (qty > 0) {
                cart[id].quantity = qty;
            } else {
                delete cart[id];
            }
            updateCart();
        }

        function removeFromCart(id) {
            delete cart[id];
            updateCart();
        }

        function updateSummary() {
            document.getElementById('subtotal').textContent = '$0.00';
            document.getElementById('tax').textContent = '$0.00';
            document.getElementById('total').textContent = '$0.00';
        }

        function setPayment(method) {
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
            event.target.closest('.payment-option').classList.add('active');
            document.getElementById('paymentMethod').value = method;
        }

        async function checkout() {
            if (Object.keys(cart).length === 0) {
                showWarning('Cart is empty');
                return;
            }

            let success = true;
            for (const [productId, item] of Object.entries(cart)) {
                try {
                    const response = await fetch('php/add_sale.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            product_id: productId,
                            quantity_sold: item.quantity,
                            sale_price: item.price,
                            total_amount: (item.price * item.quantity).toFixed(2)
                        })
                    });

                    const data = await response.json();
                    if (data.status !== 'success') {
                        success = false;
                        showError(data.message);
                    }
                } catch (error) {
                    success = false;
                    console.error('Error:', error);
                    showError('An error occurred during checkout');
                }
            }

            if (success) {
                showSuccess('Sales recorded successfully! Payment method: ' + document.getElementById('paymentMethod').value);
                cart = {};
                updateCart();
                loadProducts();
            }
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear the cart?')) {
                cart = {};
                updateCart();
            }
        }

        function searchProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        }

        loadProducts();
    </script>
    <script src="js/toast.js"></script>
</body>
</html>
