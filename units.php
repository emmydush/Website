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
    <title>Units - Inventory Management System</title>
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
        
        .units-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        
        .units-table th,
        .units-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .units-table th {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        .units-table tr:hover {
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
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 450px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
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
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
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
        
        .btn-cancel:hover {
            background-color: #d0d0d0;
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
                <a href="units.php" class="menu-item active">
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
                <h1>Units Management</h1>
                <button class="btn-primary" id="addUnitBtn">
                    <i class="fas fa-plus"></i> Add New Unit
                </button>
            </div>

            <table class="units-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Unit Name</th>
                        <th>Symbol</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="unitsTableBody">
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">Loading...</td>
                    </tr>
                </tbody>
            </table>

            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-inbox"></i>
                <p>No units found. Create one to get started!</p>
            </div>
        </main>
    </div>

    <!-- Add/Edit Unit Modal -->
    <div id="unitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Unit</h2>
                <span class="close">&times;</span>
            </div>
            <form id="unitForm">
                <input type="hidden" id="unitId" value="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="unitName">Unit Name *</label>
                        <input type="text" id="unitName" required>
                    </div>
                    <div class="form-group">
                        <label for="unitSymbol">Symbol *</label>
                        <input type="text" id="unitSymbol" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const addUnitBtn = document.getElementById('addUnitBtn');
        const unitModal = document.getElementById('unitModal');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const unitForm = document.getElementById('unitForm');
        const modalTitle = document.getElementById('modalTitle');
        const unitsTableBody = document.getElementById('unitsTableBody');
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

        // Load units on page load
        document.addEventListener('DOMContentLoaded', loadUnits);

        // Open modal for adding new unit
        addUnitBtn.addEventListener('click', () => {
            unitForm.reset();
            document.getElementById('unitId').value = '';
            modalTitle.textContent = 'Add New Unit';
            unitModal.style.display = 'flex';
            document.getElementById('unitName').focus();
        });

        // Modal close handlers
        closeBtn.addEventListener('click', () => {
            unitModal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            unitModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === unitModal) {
                unitModal.style.display = 'none';
            }
        });

        // Form submission
        unitForm.addEventListener('submit', (e) => {
            e.preventDefault();
            saveUnit();
        });

        // Load units from server
        function loadUnits() {
            fetch('php/get_units.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayUnits(data.data);
                    } else {
                        console.error('Error loading units:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                });
        }

        // Display units in table
        function displayUnits(units) {
            unitsTableBody.innerHTML = '';

            if (units.length === 0) {
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            units.forEach(unit => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${unit.id}</td>
                    <td>${htmlEscape(unit.name)}</td>
                    <td><strong>${htmlEscape(unit.symbol)}</strong></td>
                    <td class="action-buttons">
                        <button class="btn-edit" onclick="editUnit(${unit.id})" title="Edit Unit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete" onclick="deleteUnit(${unit.id})" title="Delete Unit">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                unitsTableBody.appendChild(row);
            });
        }

        // Save unit (add or update)
        function saveUnit() {
            const name = document.getElementById('unitName').value.trim();
            const symbol = document.getElementById('unitSymbol').value.trim();
            const unitId = document.getElementById('unitId').value;

            if (!name || !symbol) {
                alert('Unit name and symbol are required');
                return;
            }

            const unitData = {
                name: name,
                symbol: symbol
            };

            const endpoint = unitId ? 'php/update_unit.php' : 'php/add_unit.php';

            if (unitId) {
                unitData.id = unitId;
            }

            const formData = new URLSearchParams();
            for (const [key, value] of Object.entries(unitData)) {
                formData.append(key, value);
            }

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    unitModal.style.display = 'none';
                    loadUnits();
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the unit');
            });
        }

        // Edit unit
        function editUnit(unitId) {
            fetch('php/get_units.php')
                .then(response => response.json())
                .then(data => {
                    const unit = data.data.find(u => u.id == unitId);
                    if (unit) {
                        document.getElementById('unitId').value = unit.id;
                        document.getElementById('unitName').value = unit.name;
                        document.getElementById('unitSymbol').value = unit.symbol;
                        modalTitle.textContent = 'Edit Unit';
                        unitModal.style.display = 'flex';
                        document.getElementById('unitName').focus();
                    }
                });
        }

        // Delete unit
        function deleteUnit(unitId) {
            if (confirm('Are you sure you want to delete this unit?')) {
                const formData = new URLSearchParams();
                formData.append('id', unitId);

                fetch('php/delete_unit.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadUnits();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the unit');
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
