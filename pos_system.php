<?php
session_start();
require_once 'php/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Fetch all products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userName = $_SESSION['username'] ?? "User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/emmanuel/css/modern_dashboard.css">
    <link rel="stylesheet" href="/emmanuel/css/toast.css">
    <link rel="stylesheet" href="/emmanuel/css/pos.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="nav-left">
            <div class="logo"><i class="fas fa-cube"></i> Smart Solution</div>
        </div>
        <div class="nav-right">
            <div class="date-time" id="currentDateTime"></div>
            <div class="notifications">
                <i class="fas fa-globe"></i>
            </div>
            <div class="user-menu" id="userMenu">
                <i class="fas fa-chevron-down"></i>
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
                    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=0D8ABC&color=fff&size=64';
                }
                ?>
                <img src="<?php echo $avatarUrl; ?>" alt="User" class="profile-image" id="profileImage">
                <div class="user-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($userName); ?></h3>
                    <p class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Staff'); ?></p>
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
                <a href="pos_system.php" class="menu-item active">
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
            <!-- POS Header -->
            <div class="pos-header">
                <div class="pos-title">
                    <h1>Point of Sale</h1>
                    <p>Smart Solution – Intelligent. Efficient. Simplified.</p>
                    <div class="pos-header-line"></div>
                </div>
            </div>

            <!-- POS Container -->
            <div class="pos-container">
                <!-- Left Section - Products -->
                <div class="pos-products">
                    <!-- Search Bar -->
                    <div class="search-section">
                        <div class="search-wrapper">
                            <div class="search-input-group">
                                <i class="fas fa-search"></i>
                                <input 
                                    type="text" 
                                    id="productSearch" 
                                    class="pos-search" 
                                    placeholder="Search products or scan barcode... (Enter barcode manually or Ctrl+B for scanner)"
                                >
                                <button class="scanner-btn" id="scannerBtn" title="Activate Scanner">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <p class="scanner-hint">
                                <i class="fas fa-info-circle"></i>
                                Scanner automatically stops after detecting a barcode
                            </p>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="products-grid" id="productsGrid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" data-product-price="<?php echo $product['price']; ?>" data-barcode="<?php echo htmlspecialchars($product['barcode'] ?? $product['id']); ?>">
                                <div class="product-image">
                                    <div class="product-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-price"><?php echo number_format($product['price'], 2); ?> FRW</p>
                                    <p class="product-stock">
                                        <?php echo number_format($product['quantity'], 2); ?> left
                                    </p>
                                </div>
                                <button class="add-btn add-to-cart-btn" onclick="addToCart(this)">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right Section - Shopping Cart -->
                <div class="shopping-cart">
                    <div class="cart-header">
                        <h2>Shopping Cart</h2>
                        <div class="cart-header-right">
                            <span class="cart-badge" id="cartBadge">0 items</span>
                            <button class="clear-cart-btn" id="clearCartBtn">
                                <i class="fas fa-trash"></i> Clear
                            </button>
                        </div>
                    </div>

                    <div class="cart-items" id="cartItems">
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <p>Your cart is empty</p>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">FRW0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (18%):</span>
                            <span id="tax">FRW0.00</span>
                        </div>
                        <div class="summary-row discount-row">
                            <span>Discount:</span>
                            <input type="number" id="discountInput" min="0" max="100" placeholder="%" class="discount-input">
                            <span id="discount">FRW0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="total">FRW0.00</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <button class="checkout-btn" id="checkoutBtn">
                        <i class="fas fa-credit-card"></i>
                        Checkout (Enter)
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script src="/emmanuel/js/avatar.js"></script>
    <script src="/emmanuel/js/toast.js"></script>
    <script src="/emmanuel/js/pos.js"></script>
</body>
</html>
