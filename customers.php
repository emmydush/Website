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
    <title>Customers - Inventory Management System</title>
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
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .customers-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        
        .customers-table th,
        .customers-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .customers-table th {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        .customers-table tr:hover {
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
            gap: 5px;
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
            max-width: 550px;
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
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
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
                <a href="categories.php" class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="units.php" class="menu-item">
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
                <a href="customers.php" class="menu-item active">
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
                <h1>Customers Management</h1>
                <button class="btn-primary" id="addCustomerBtn">
                    <i class="fas fa-plus"></i> Add New Customer
                </button>
            </div>

            <table class="customers-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Purchases</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">Loading...</td>
                    </tr>
                </tbody>
            </table>

            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-inbox"></i>
                <p>No customers found. Add one to get started!</p>
            </div>
        </main>
    </div>

    <!-- Add/Edit Customer Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Customer</h2>
                <span class="close">&times;</span>
            </div>
            <form id="customerForm">
                <input type="hidden" id="customerId" value="">
                <div class="form-group">
                    <label for="customerName">Customer Name *</label>
                    <input type="text" id="customerName" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="customerEmail">Email</label>
                        <input type="email" id="customerEmail">
                    </div>
                    <div class="form-group">
                        <label for="customerPhone">Phone</label>
                        <input type="tel" id="customerPhone">
                    </div>
                </div>
                <div class="form-group">
                    <label for="customerAddress">Address</label>
                    <textarea id="customerAddress" rows="2" placeholder="Optional: Customer address"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const addCustomerBtn = document.getElementById('addCustomerBtn');
        const customerModal = document.getElementById('customerModal');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const customerForm = document.getElementById('customerForm');
        const modalTitle = document.getElementById('modalTitle');
        const customersTableBody = document.getElementById('customersTableBody');
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

        // Load customers on page load
        document.addEventListener('DOMContentLoaded', loadCustomers);

        // Open modal for adding new customer
        addCustomerBtn.addEventListener('click', () => {
            customerForm.reset();
            document.getElementById('customerId').value = '';
            modalTitle.textContent = 'Add New Customer';
            customerModal.style.display = 'flex';
            document.getElementById('customerName').focus();
        });

        // Modal close handlers
        closeBtn.addEventListener('click', () => {
            customerModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            customerModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === customerModal) {
                customerModal.style.display = 'none';
            }
        });

        // Form submission
        customerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            saveCustomer();
        });

        // Load customers from server
        function loadCustomers() {
            fetch('php/get_customers.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayCustomers(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error loading customers:', error);
                });
        }

        // Display customers in table
        function displayCustomers(customers) {
            customersTableBody.innerHTML = '';

            if (customers.length === 0) {
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            customers.forEach(customer => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${htmlEscape(customer.name)}</td>
                    <td>${htmlEscape(customer.email || '-')}</td>
                    <td>${htmlEscape(customer.phone || '-')}</td>
                    <td>${customer.total_purchases || 0}</td>
                    <td class="action-buttons">
                        <button class="btn-edit" onclick="editCustomer(${customer.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete" onclick="deleteCustomer(${customer.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                customersTableBody.appendChild(row);
            });
        }

        // Save customer
        function saveCustomer() {
            const name = document.getElementById('customerName').value.trim();
            const email = document.getElementById('customerEmail').value.trim();
            const phone = document.getElementById('customerPhone').value.trim();
            const address = document.getElementById('customerAddress').value.trim();
            const customerId = document.getElementById('customerId').value;

            if (!name) {
                alert('Customer name is required');
                return;
            }

            const customerData = {
                name: name,
                email: email,
                phone: phone,
                address: address
            };

            const endpoint = customerId ? 'php/update_customer.php' : 'php/add_customer.php';

            if (customerId) {
                customerData.id = customerId;
            }

            const formData = new URLSearchParams();
            for (const [key, value] of Object.entries(customerData)) {
                formData.append(key, value);
            }

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    customerModal.style.display = 'none';
                    loadCustomers();
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        // Edit customer
        function editCustomer(customerId) {
            fetch('php/get_customers.php')
                .then(response => response.json())
                .then(data => {
                    const customer = data.data.find(c => c.id == customerId);
                    if (customer) {
                        document.getElementById('customerId').value = customer.id;
                        document.getElementById('customerName').value = customer.name;
                        document.getElementById('customerEmail').value = customer.email || '';
                        document.getElementById('customerPhone').value = customer.phone || '';
                        document.getElementById('customerAddress').value = customer.address || '';
                        modalTitle.textContent = 'Edit Customer';
                        customerModal.style.display = 'flex';
                    }
                });
        }

        // Delete customer
        function deleteCustomer(customerId) {
            if (confirm('Are you sure you want to delete this customer?')) {
                const formData = new URLSearchParams();
                formData.append('id', customerId);

                fetch('php/delete_customer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadCustomers();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }

        // Utility function
        function htmlEscape(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
