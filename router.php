<?php
// Router for the Inventory Management System

// Get the requested path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// Route handling
switch ($path) {
    case '':
    case 'index.php':
    case 'modern_dashboard':
        include 'modern_dashboard.php';
        break;
        
    case 'products':
        include 'products.php';
        break;
        
    case 'categories':
        include 'categories.php';
        break;
        
    case 'units':
        include 'units.php';
        break;
        
    case 'suppliers':
        include 'suppliers.php';
        break;
        
    case 'customers':
        include 'customers.php';
        break;
        
    case 'sales':
        include 'sales.php';
        break;
        
    case 'credit_sales':
        include 'credit_sales.php';
        break;
        
    case 'expenses':
        include 'expenses.php';
        break;
        
    case 'purchases':
        include 'purchases.php';
        break;
        
    case 'reports':
        include 'reports.php';
        break;
        
    case 'advanced_reports':
        include 'advanced_reports.php';
        break;
        
    case 'settings':
        include 'settings.php';
        break;
        
    case 'login':
        include 'login.html';
        break;
        
    case 'register':
        include 'register.html';
        break;
        
    case 'logout':
        include 'logout.php';
        break;
        
    default:
        // Handle API requests
        if (strpos($path, 'php/') === 0) {
            // Allow direct access to PHP files in the php directory
            $file = basename($path);
            if (file_exists("php/$file")) {
                include "php/$file";
            } else {
                http_response_code(404);
                echo "API endpoint not found";
            }
        } else {
            // For any other path, show 404
            http_response_code(404);
            include '404.html'; // You might want to create this file
        }
        break;
}
?>