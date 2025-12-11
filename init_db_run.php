<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_management');

try {
    // Create connection to MySQL server (without specifying database)
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Creating Database and Tables...</h2>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "✓ Database created/verified<br>";
    
    // Now connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables manually
    $tables = [
        // Users table
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Categories table
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Units table
        "CREATE TABLE IF NOT EXISTS units (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            abbreviation VARCHAR(10) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Suppliers table
        "CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            contact_person VARCHAR(100),
            email VARCHAR(100),
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Products table
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            category_id INT,
            unit_id INT,
            supplier_id INT,
            price DECIMAL(10, 2) NOT NULL,
            cost DECIMAL(10, 2),
            quantity INT NOT NULL DEFAULT 0,
            min_stock_level INT DEFAULT 10,
            barcode VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
        )",
        
        // Transactions table
        "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT,
            type ENUM('in', 'out') NOT NULL,
            quantity INT NOT NULL,
            reason VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id)
        )",
        
        // Customers table
        "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(50),
            state VARCHAR(50),
            postal_code VARCHAR(20),
            country VARCHAR(50),
            customer_type ENUM('retail', 'wholesale') DEFAULT 'retail',
            balance DECIMAL(10, 2) DEFAULT 0.00,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Sales table
        "CREATE TABLE IF NOT EXISTS sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT,
            quantity_sold INT NOT NULL,
            sale_price DECIMAL(10, 2) NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            sold_by INT,
            sale_type ENUM('regular', 'credit') DEFAULT 'regular',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (sold_by) REFERENCES users(id)
        )",
        
        // Credit sales table
        "CREATE TABLE IF NOT EXISTS credit_sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            total_amount DECIMAL(10, 2) NOT NULL,
            amount_paid DECIMAL(10, 2) DEFAULT 0.00,
            balance_due DECIMAL(10, 2) NOT NULL,
            due_date DATE,
            status ENUM('pending', 'partial', 'paid', 'overdue') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id)
        )"
    ];
    
    foreach ($tables as $table) {
        try {
            $pdo->exec($table);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "Error creating table: " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "✓ All tables created<br>";
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@inventory.com', $hashedPassword, 'admin']);
        echo "✓ Admin user created<br>";
    } else {
        echo "✓ Admin user already exists<br>";
    }
    
    // Check if units exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM units");
    $stmt->execute();
    $unitCount = $stmt->fetchColumn();
    
    if ($unitCount == 0) {
        // Insert sample units
        $units = [
            ['Piece', 'pcs', 'Individual pieces'],
            ['Kilogram', 'kg', 'Weight in kilograms'],
            ['Liter', 'L', 'Volume in liters'],
            ['Box', 'box', 'Individual boxes'],
            ['Pack', 'pack', 'Packs of items'],
            ['Meter', 'm', 'Length in meters'],
            ['Dozen', 'dz', 'Group of twelve']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO units (name, abbreviation, description) VALUES (?, ?, ?)");
        foreach ($units as $unit) {
            $stmt->execute($unit);
        }
        echo "✓ Sample units created<br>";
    } else {
        echo "✓ Units already exist<br>";
    }
    
    // Check if categories exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $catCount = $stmt->fetchColumn();
    
    if ($catCount == 0) {
        // Insert sample categories
        $categories = [
            ['Electronics', 'Electronic devices and gadgets'],
            ['Clothing', 'Apparel and fashion items'],
            ['Food', 'Food and beverages'],
            ['Books', 'Books and reading materials'],
            ['Furniture', 'Home and office furniture'],
            ['Hardware', 'Tools and building materials']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }
        echo "✓ Sample categories created<br>";
    } else {
        echo "✓ Categories already exist<br>";
    }
    
    // Check if products exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $prodCount = $stmt->fetchColumn();
    
    if ($prodCount == 0) {
        // Insert sample products
        $products = [
            ['Laptop Computer', 'High-performance laptop computer', 1, 1, null, 999.99, 850.00, 5, 2],
            ['T-Shirt', 'Cotton t-shirt', 2, 1, null, 19.99, 10.00, 50, 10],
            ['Coffee Beans', 'Premium coffee beans', 3, 3, null, 12.99, 8.00, 100, 20],
            ['Programming Book', 'Python programming guide', 4, 1, null, 39.99, 25.00, 30, 5],
            ['Office Chair', 'Ergonomic office chair', 5, 1, null, 199.99, 120.00, 15, 3],
            ['Hammer', 'Steel hammer tool', 6, 1, null, 24.99, 15.00, 50, 10]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, category_id, unit_id, supplier_id, price, cost, quantity, min_stock_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        echo "✓ Sample products created<br>";
    } else {
        echo "✓ Products already exist<br>";
    }
    
    echo "<hr>";
    echo "<h2>✓ Setup Complete!</h2>";
    echo "<p><strong>Default admin login:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    echo "<p><a href='login.html' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>Go to Login Page</a></p>";
    
} catch(PDOException $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
    echo "<p><a href='javascript:location.reload()'>Try Again</a></p>";
}
?>
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@inventory.com', $hashedPassword, 'admin']);
        echo "✓ Admin user created<br>";
    } else {
        echo "✓ Admin user already exists<br>";
    }
    
    // Check if units exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM units");
    $stmt->execute();
    $unitCount = $stmt->fetchColumn();
    
    if ($unitCount == 0) {
        // Insert sample units
        $units = [
            ['Piece', 'pcs', 'Individual pieces'],
            ['Kilogram', 'kg', 'Weight in kilograms'],
            ['Liter', 'L', 'Volume in liters'],
            ['Box', 'box', 'Individual boxes'],
            ['Pack', 'pack', 'Packs of items'],
            ['Meter', 'm', 'Length in meters'],
            ['Dozen', 'dz', 'Group of twelve']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO units (name, abbreviation, description) VALUES (?, ?, ?)");
        foreach ($units as $unit) {
            $stmt->execute($unit);
        }
        echo "✓ Sample units created<br>";
    } else {
        echo "✓ Units already exist<br>";
    }
    
    // Check if categories exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $catCount = $stmt->fetchColumn();
    
    if ($catCount == 0) {
        // Insert sample categories
        $categories = [
            ['Electronics', 'Electronic devices and gadgets'],
            ['Clothing', 'Apparel and fashion items'],
            ['Food', 'Food and beverages'],
            ['Books', 'Books and reading materials'],
            ['Furniture', 'Home and office furniture'],
            ['Hardware', 'Tools and building materials']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }
        echo "✓ Sample categories created<br>";
    } else {
        echo "✓ Categories already exist<br>";
    }
    
    // Check if products exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $prodCount = $stmt->fetchColumn();
    
    if ($prodCount == 0) {
        // Insert sample products
        $products = [
            ['Laptop Computer', 'High-performance laptop computer', 1, null, 999.99, 50, 5],
            ['T-Shirt', 'Cotton t-shirt', 2, null, 19.99, 50, 100],
            ['Coffee Beans', 'Premium coffee beans', 3, null, 12.99, 100, 50],
            ['Programming Book', 'Python programming guide', 4, null, 39.99, 30, 20],
            ['Office Chair', 'Ergonomic office chair', 5, null, 199.99, 15, 10],
            ['Hammer', 'Steel hammer tool', 6, null, 24.99, 100, 50]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, category_id, supplier_id, price, quantity, min_stock_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        echo "✓ Sample products created<br>";
    } else {
        echo "✓ Products already exist<br>";
    }
    
    echo "<hr>";
    echo "<h2>✓ Setup Complete!</h2>";
    echo "<p><a href='login.html'>Go to Login Page</a></p>";
    echo "<p><strong>Default admin login:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
    echo "<p><a href='javascript:location.reload()'>Try Again</a></p>";
}
?>
