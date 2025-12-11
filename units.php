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
    <title>Units - Inventory Management</title>
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
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
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
        .btn-edit {
            background: #4CAF50;
            color: white;
        }
        .btn-delete {
            background: #f44336;
            color: white;
        }
        .units-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        .units-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        .units-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .units-table tr:hover {
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
                <a href="units.php" class="menu-item active">
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
                    <h2>Units Management</h2>
                    <button class="btn btn-primary" id="addUnitBtn">
                        <i class="fas fa-plus"></i> Add Unit
                    </button>
                </div>

                <div id="unitsContainer">
                    <div class="empty-state">
                        <i class="fas fa-ruler"></i>
                        <p>Loading units...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="unitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Unit</h2>
            </div>
            <form id="unitForm">
                <div class="modal-body">
                    <input type="hidden" id="unitId">
                    <div class="form-group">
                        <label for="unitName">Unit Name *</label>
                        <input type="text" id="unitName" placeholder="Enter unit name (e.g., Kilogram)" required>
                    </div>
                    <div class="form-group">
                        <label for="unitAbbreviation">Abbreviation *</label>
                        <input type="text" id="unitAbbreviation" placeholder="Enter abbreviation (e.g., kg)" required>
                    </div>
                    <div class="form-group">
                        <label for="unitDescription">Description</label>
                        <textarea id="unitDescription" placeholder="Enter unit description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('unitModal');
        const addUnitBtn = document.getElementById('addUnitBtn');
        const closeModal = document.getElementById('closeModal');
        const unitForm = document.getElementById('unitForm');

        addUnitBtn.addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Add Unit';
            unitForm.reset();
            document.getElementById('unitId').value = '';
            modal.classList.add('show');
        });

        closeModal.addEventListener('click', () => {
            modal.classList.remove('show');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });

        unitForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const id = document.getElementById('unitId').value;
            const name = document.getElementById('unitName').value;
            const abbreviation = document.getElementById('unitAbbreviation').value;
            const description = document.getElementById('unitDescription').value;
            
            const endpoint = id ? 'php/update_unit.php' : 'php/add_unit.php';
            const body = new URLSearchParams({
                name: name,
                abbreviation: abbreviation,
                description: description
            });
            
            if (id) {
                body.append('id', id);
            }
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: body
                });
                
                const data = await response.json();

                if (data.status === 'success') {
                    showSuccess(data.message);
                    modal.classList.remove('show');
                    loadUnits();
                } else {
                    showError(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred');
            }
        });

        async function loadUnits() {
            try {
                const response = await fetch('php/get_units.php');
                const data = await response.json();
                
                const container = document.getElementById('unitsContainer');
                
                if (data.status === 'success' && data.data.length > 0) {
                    let html = '<table class="units-table"><thead><tr><th>ID</th><th>Name</th><th>Abbreviation</th><th>Description</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
                    
                    data.data.forEach(unit => {
                        const createdDate = new Date(unit.created_at).toLocaleDateString();
                        html += `<tr>
                            <td>${unit.id}</td>
                            <td>${unit.name}</td>
                            <td>${unit.abbreviation}</td>
                            <td>${unit.description || '-'}</td>
                            <td>${createdDate}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-edit" onclick="editUnit(${unit.id}, '${unit.name}', '${unit.abbreviation}', '${(unit.description || '').replace(/'/g, "\\'")}')">Edit</button>
                                    <button class="btn btn-sm btn-delete" onclick="deleteUnit(${unit.id})">Delete</button>
                                </div>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="empty-state"><i class="fas fa-ruler"></i><p>No units found. Add your first unit!</p></div>';
                }
            } catch (error) {
                console.error('Error loading units:', error);
                document.getElementById('unitsContainer').innerHTML = '<div class="empty-state"><p>Error loading units</p></div>';
            }
        }

        function editUnit(id, name, abbreviation, description) {
            document.getElementById('modalTitle').textContent = 'Edit Unit';
            document.getElementById('unitId').value = id;
            document.getElementById('unitName').value = name;
            document.getElementById('unitAbbreviation').value = abbreviation;
            document.getElementById('unitDescription').value = description;
            modal.classList.add('show');
        }

        async function deleteUnit(id) {
            if (!confirm('Are you sure you want to delete this unit?')) {
                return;
            }

            try {
                const response = await fetch('php/delete_unit.php', {
                    method: 'POST',
                    body: new URLSearchParams({ id: id })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showSuccess(data.message);
                    loadUnits();
                } else {
                    showError(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred');
            }
        }

        loadUnits();
    </script>
    <script src="js/toast.js"></script>
</body>
</html>
