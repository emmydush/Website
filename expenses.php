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
    <title>Expenses - Inventory Management System</title>
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
        .delete-btn {
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
            .delete-btn {
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
            <h1 class="page-title">Expenses</h1>
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
                <a href="purchases.php" class="menu-item">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Purchases</span>
                </a>
                <a href="expenses.php" class="menu-item active">
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
                <h2>Expense Management</h2>
                <button class="btn-primary" id="addExpenseBtn"><i class="fas fa-plus"></i> Add New Expense</button>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Expenses (This Month)</h3>
                    <div class="stat-value" id="monthlyTotal">$0.00</div>
                </div>
                <div class="stat-card">
                    <h3>Total Expenses (This Year)</h3>
                    <div class="stat-value" id="yearlyTotal">$0.00</div>
                </div>
            </div>
            
            <div class="filter-box">
                <div class="filter-group">
                    <label for="categoryFilter">Category:</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
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
                <input type="text" id="searchInput" placeholder="Search expenses...">
            </div>
            
            <div class="table-container">
                <table id="expensesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="expensesTableBody">
                        <!-- Expense data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Add/Edit Expense Modal -->
        <div id="expenseModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Add New Expense</h2>
                    <span class="close">&times;</span>
                </div>
                <form id="expenseForm">
                    <input type="hidden" id="expenseId" value="">
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <input type="text" id="description" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount *</label>
                        <input type="number" id="amount" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="expenseDate">Date *</label>
                        <input type="date" id="expenseDate" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" list="categoriesList">
                        <datalist id="categoriesList">
                            <option value="Office Supplies">
                            <option value="Utilities">
                            <option value="Rent">
                            <option value="Salaries">
                            <option value="Marketing">
                            <option value="Maintenance">
                            <option value="Transportation">
                            <option value="Insurance">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" rows="3" placeholder="Optional: Add notes about this expense"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelBtn"><i class="fas fa-times"></i> Cancel</button>
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // DOM Elements
        const addExpenseBtn = document.getElementById('addExpenseBtn');
        const expenseModal = document.getElementById('expenseModal');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const expenseForm = document.getElementById('expenseForm');
        const modalTitle = document.getElementById('modalTitle');
        const expensesTableBody = document.getElementById('expensesTableBody');
        const searchInput = document.getElementById('searchInput');
        const applyFiltersBtn = document.getElementById('applyFilters');

        // Load expenses when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadExpenses();
        });

        // Open modal for adding new expense
        addExpenseBtn.addEventListener('click', () => {
            // Reset form
            expenseForm.reset();
            document.getElementById('expenseId').value = '';
            modalTitle.textContent = 'Add New Expense';
            expenseModal.style.display = 'flex';
            // Set today's date as default
            document.getElementById('expenseDate').value = new Date().toISOString().split('T')[0];
            // Focus on first field
            document.getElementById('description').focus();
        });

        // Close modal
        closeBtn.addEventListener('click', () => {
            expenseModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            expenseModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === expenseModal) {
                expenseModal.style.display = 'none';
            }
        });

        // Handle form submission
        expenseForm.addEventListener('submit', (e) => {
            e.preventDefault();
            saveExpense();
        });

        // Search functionality
        searchInput.addEventListener('input', function() {
            loadExpenses();
        });

        // Apply filters
        applyFiltersBtn.addEventListener('click', function() {
            loadExpenses();
        });

        // Function to load expenses
        function loadExpenses() {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            const params = new URLSearchParams({
                search: search,
                category: category,
                date_from: dateFrom,
                date_to: dateTo
            });
            
            fetch(`php/get_expenses.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('expensesTableBody');
                    tbody.innerHTML = '';
                    
                    if (data.status === 'success') {
                        // Update stats
                        document.getElementById('monthlyTotal').textContent = '$' + parseFloat(data.stats.monthly_total || 0).toFixed(2);
                        document.getElementById('yearlyTotal').textContent = '$' + parseFloat(data.stats.yearly_total || 0).toFixed(2);
                        
                        // Populate category filter
                        const categoryFilter = document.getElementById('categoryFilter');
                        const currentCategory = categoryFilter.value;
                        categoryFilter.innerHTML = '<option value="">All Categories</option>';
                        
                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category;
                            option.textContent = category;
                            categoryFilter.appendChild(option);
                        });
                        
                        categoryFilter.value = currentCategory;
                        
                        // Populate expenses table
                        data.expenses.forEach(expense => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${expense.id}</td>
                                <td>${expense.description}</td>
                                <td>${expense.category || ''}</td>
                                <td>$${parseFloat(expense.amount).toFixed(2)}</td>
                                <td>${expense.expense_date}</td>
                                <td class="actions">
                                    <button class="edit-btn" onclick="editExpense(${expense.id})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="delete-btn" onclick="deleteExpense(${expense.id})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading expenses:', error);
                });
        }
        
        // Function to save expense
        function saveExpense() {
            const id = document.getElementById('expenseId').value;
            const description = document.getElementById('description').value;
            const amount = document.getElementById('amount').value;
            const expenseDate = document.getElementById('expenseDate').value;
            const category = document.getElementById('category').value;
            const notes = document.getElementById('notes').value;
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('description', description);
            formData.append('amount', amount);
            formData.append('expense_date', expenseDate);
            formData.append('category', category);
            formData.append('notes', notes);
            
            const url = id ? 'php/update_expense.php' : 'php/add_expense.php';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('expenseForm').style.display = 'none';
                    loadExpenses();
                    alert('Expense saved successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving expense:', error);
                alert('Error saving expense');
            });
        }
        
        // Function to edit expense
        function editExpense(id) {
            fetch(`php/get_expense.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const expense = data.expense;
                        document.getElementById('expenseId').value = expense.id;
                        document.getElementById('description').value = expense.description;
                        document.getElementById('amount').value = expense.amount;
                        document.getElementById('expenseDate').value = expense.expense_date;
                        document.getElementById('category').value = expense.category || '';
                        document.getElementById('notes').value = expense.notes || '';
                        
                        document.getElementById('formTitle').textContent = 'Edit Expense';
                        document.getElementById('expenseForm').style.display = 'block';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading expense:', error);
                    alert('Error loading expense');
                });
        }
        
        // Function to delete expense
        function deleteExpense(id) {
            if (confirm('Are you sure you want to delete this expense?')) {
                fetch('php/delete_expense.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadExpenses();
                        alert('Expense deleted successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting expense:', error);
                    alert('Error deleting expense');
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
        
        // Load expenses on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];
            
            loadExpenses();
        });
    </script>
    <script src="js/avatar.js"></script>
</body>
</html>