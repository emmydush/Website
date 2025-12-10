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
    <title>Categories - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/modern_dashboard.css">
    <link rel="stylesheet" href="css/responsive.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .category-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #6a11cb;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .category-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .category-desc {
            font-size: 14px;
            color: #777;
            margin-bottom: 15px;
            min-height: 40px;
        }
        
        .category-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
            margin-bottom: 15px;
        }
        
        .product-count {
            font-size: 14px;
            color: #666;
        }
        
        .category-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .btn-edit, .btn-delete {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
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
            max-width: 500px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
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
        
        .form-group input,
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
        .form-group textarea:focus {
            border-color: #6a11cb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn-cancel {
            background-color: #e0e0e0;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-cancel:hover {
            background-color: #d0d0d0;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }
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
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName ?? 'User'); ?>&background=0D8ABC&color=fff" alt="User" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($userName ?? 'User'); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <a href="#"><i class="fas fa-user"></i> Profile</a>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                <a href="modern_dashboard.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="products.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="categories.php" class="menu-item active">
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
                <a href="logout.php" class="menu-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Categories Management</h1>
                <button class="btn-primary" id="addCategoryBtn">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
            </div>

            <div id="categoriesContainer" class="categories-grid">
                <!-- Categories will be loaded here -->
            </div>

            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-inbox"></i>
                <p>No categories found. Create one to get started!</p>
            </div>
        </main>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Category</h2>
                <span class="close">&times;</span>
            </div>
            <form id="categoryForm">
                <input type="hidden" id="categoryId" value="">
                <div class="form-group">
                    <label for="categoryName">Category Name *</label>
                    <input type="text" id="categoryName" required>
                </div>
                <div class="form-group">
                    <label for="categoryDescription">Description</label>
                    <textarea id="categoryDescription" rows="3" placeholder="Optional: Add category description"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const addCategoryBtn = document.getElementById('addCategoryBtn');
        const categoryModal = document.getElementById('categoryModal');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const categoryForm = document.getElementById('categoryForm');
        const modalTitle = document.getElementById('modalTitle');
        const categoriesContainer = document.getElementById('categoriesContainer');
        const emptyState = document.getElementById('emptyState');
        const userMenu = document.getElementById('userMenu');
        const userDropdown = document.getElementById('userDropdown');

        // User menu functionality
        userMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });

        // Load categories on page load
        document.addEventListener('DOMContentLoaded', loadCategories);

        // Open modal for adding new category
        addCategoryBtn.addEventListener('click', () => {
            categoryForm.reset();
            document.getElementById('categoryId').value = '';
            modalTitle.textContent = 'Add New Category';
            categoryModal.style.display = 'flex';
            document.getElementById('categoryName').focus();
        });

        // Modal close handlers
        closeBtn.addEventListener('click', () => {
            categoryModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            categoryModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === categoryModal) {
                categoryModal.style.display = 'none';
            }
        });

        // Form submission
        categoryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            saveCategory();
        });

        // Load categories from server
        function loadCategories() {
            fetch('php/get_categories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayCategories(data.data);
                    } else {
                        console.error('Error loading categories:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                });
        }

        // Display categories
        function displayCategories(categories) {
            categoriesContainer.innerHTML = '';

            if (categories.length === 0) {
                categoriesContainer.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            categoriesContainer.style.display = 'grid';
            emptyState.style.display = 'none';

            categories.forEach(category => {
                const card = document.createElement('div');
                card.className = 'category-card';
                card.innerHTML = `
                    <div class="category-name">${htmlEscape(category.name)}</div>
                    <div class="category-desc">${htmlEscape(category.description || 'No description')}</div>
                    <div class="category-stats">
                        <span class="product-count">
                            <i class="fas fa-box"></i> ${category.product_count || 0} products
                        </span>
                    </div>
                    <div class="category-actions">
                        <button class="btn-edit" onclick="editCategory(${category.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete" onclick="deleteCategory(${category.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                categoriesContainer.appendChild(card);
            });
        }

        // Save category (add or update)
        function saveCategory() {
            const name = document.getElementById('categoryName').value.trim();
            const description = document.getElementById('categoryDescription').value.trim();
            const categoryId = document.getElementById('categoryId').value;

            if (!name) {
                alert('Category name is required');
                return;
            }

            const categoryData = {
                name: name,
                description: description
            };

            const endpoint = categoryId ? 'php/update_category.php' : 'php/add_category.php';

            if (categoryId) {
                categoryData.id = categoryId;
            }

            const formData = new URLSearchParams();
            for (const [key, value] of Object.entries(categoryData)) {
                formData.append(key, value);
            }

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    categoryModal.style.display = 'none';
                    loadCategories();
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the category');
            });
        }

        // Edit category
        function editCategory(categoryId) {
            fetch('php/get_categories.php')
                .then(response => response.json())
                .then(data => {
                    const category = data.data.find(c => c.id == categoryId);
                    if (category) {
                        document.getElementById('categoryId').value = category.id;
                        document.getElementById('categoryName').value = category.name;
                        document.getElementById('categoryDescription').value = category.description || '';
                        modalTitle.textContent = 'Edit Category';
                        categoryModal.style.display = 'flex';
                        document.getElementById('categoryName').focus();
                    }
                });
        }

        // Delete category
        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                const formData = new URLSearchParams();
                formData.append('id', categoryId);

                fetch('php/delete_category.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadCategories();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the category');
                });
            }
        }

        // Utility function to escape HTML
        function htmlEscape(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
