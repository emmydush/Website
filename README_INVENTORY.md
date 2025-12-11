# Inventory Management System

## Overview
This is a comprehensive inventory management system with product tracking, sales management, and reporting capabilities.

## Setup Instructions

### 1. Start the Server
Run `start_server.bat` to start XAMPP on port 8080.

Access the system at: http://localhost:8080/emmanuel/

### 2. Database Initialization
If this is your first time using the system:

1. Visit http://localhost:8080/emmanuel/init_db.php to initialize the database
2. Use the default admin credentials:
   - Username: `admin`
   - Password: `admin123`

### 3. Populate Sample Data (Optional)
To add sample products, categories, and suppliers:
1. Visit http://localhost:8080/emmanuel/populate_products.php

### 4. Troubleshooting Products Not Visible

If products are not showing in the products table:

1. Check database connection:
   - Visit http://localhost:8080/emmanuel/db_test.php
   
2. Verify products exist:
   - Visit http://localhost:8080/emmanuel/debug_products.php
   
3. Manually populate products:
   - Visit http://localhost:8080/emmanuel/populate_products.php
   
4. Test product display:
   - Visit http://localhost:8080/emmanuel/final_test.html

## Key Features

- Product Management (Add, Edit, Delete)
- Category and Supplier Management
- Real-time Stock Tracking
- Barcode Scanning Support
- Sales and Credit Sales Tracking
- Comprehensive Reporting
- Responsive Design

## Main Pages

- Login: `/login.html`
- Dashboard: `/modern_dashboard.php`
- Products: `/products.php`
- Sales: `/sales.php`
- Reports: `/reports.php`

## Default Credentials

Admin User:
- Username: `admin`
- Password: `admin123`

## Technical Details

- Backend: PHP
- Database: MySQL
- Frontend: HTML, CSS, JavaScript
- Charts: Chart.js
- Icons: Font Awesome

## Support

For issues with products not displaying:
1. Ensure XAMPP is running
2. Check database connection in `php/db_connect.php`
3. Verify products exist in the database
4. Check browser console for JavaScript errors