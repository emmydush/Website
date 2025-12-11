<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 1);

session_start();
require_once 'php/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.html');
    exit();
}

// Initialize all variables with safe defaults
$totalProducts = 0;
$lowStockItems = 0;
$outOfStockItems = 0;
$totalCategories = 0;
$todaysSales = 0.00;
$stockValue = 0.00;
$userName = $_SESSION['username'] ?? "User";
$userRole = $_SESSION['role'] ?? "Staff";

// Fetch dashboard statistics
try {
    // Get total products count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get low stock items count
    $stmt = $pdo->prepare("SELECT COUNT(*) as low_stock FROM products WHERE quantity <= min_stock_level AND quantity > 0");
    $stmt->execute();
    $lowStockItems = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];

    // Get out of stock items count
    $stmt = $pdo->prepare("SELECT COUNT(*) as out_of_stock FROM products WHERE quantity = 0");
    $stmt->execute();
    $outOfStockItems = $stmt->fetch(PDO::FETCH_ASSOC)['out_of_stock'];

    // Get total categories count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categories");
    $stmt->execute();
    $totalCategories = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get today's sales value (assuming sales table exists)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as today_sales 
        FROM sales 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    $todaysSales = $stmt->fetch(PDO::FETCH_ASSOC)['today_sales'];

    // Get total stock value
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(price * quantity), 0) as stock_value 
        FROM products
    ");
    $stmt->execute();
    $stockValue = $stmt->fetch(PDO::FETCH_ASSOC)['stock_value'];
    
    // Update user information if available in session
    if (isset($_SESSION['username'])) {
        $userName = $_SESSION['username'];
    }
    if (isset($_SESSION['role'])) {
        $userRole = $_SESSION['role'];
    }
} catch (PDOException $e) {
    // Variables already have default values, so we just log the error
    error_log("Dashboard stats error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Inventory Management Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/toast.css">
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
                <a href="#" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="#" class="menu-item">
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
                <a href="#" class="menu-item">
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
            <div class="welcome-section">
                <h1>Welcome back, <?php echo strtolower(htmlspecialchars($userName ?? 'User')); ?>!</h1>
                <p>Here's what's happening with your business today.</p>
            </div>

            <div class="search-container">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search products, sales, and customers...">
                </div>
            </div>

            <div class="stats-cards">
                <div class="card blue-gradient">
                    <div class="card-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Products</h3>
                        <p class="card-value"><?php echo $totalProducts ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="card pink-gradient">
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-content">
                        <h3>Low Stock Items</h3>
                        <p class="card-value"><?php echo $lowStockItems ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="card green-gradient">
                    <div class="card-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="card-content">
                        <h3>Out of Stock Items</h3>
                        <p class="card-value"><?php echo $outOfStockItems ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="card orange-gradient">
                    <div class="card-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Categories</h3>
                        <p class="card-value"><?php echo $totalCategories ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="card cyan-gradient">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="card-content">
                        <h3>Today's Sales</h3>
                        <p class="card-value">$<?php echo number_format($todaysSales ?? 0, 2); ?></p>
                    </div>
                </div>
                
                <div class="card purple-gradient">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-content">
                        <h3>Stock Value</h3>
                        <p class="card-value">$<?php echo number_format($stockValue ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="charts-section">
                <div class="chart-container">
                    <h2>Fast-Moving vs Slow-Moving Items</h2>
                    <canvas id="barChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h2>Category-wise Stock Distribution</h2>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/avatar.js"></script>
    <script src="js/toast.js"></script>
    <script src="js/modern_dashboard.js"></script>
</body>
</html>
