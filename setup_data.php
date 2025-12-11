<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_management');

try {
    // Create connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Setting up sample data...</h2>";
    
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
    
    // Check if products table has data
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $productCount = $stmt->fetchColumn();
    
    if ($productCount == 0) {
        // Insert sample categories
        $categories = [
            ['Electronics', 'Electronic devices and gadgets'],
            ['Clothing', 'Apparel and fashion items'],
            ['Food', 'Food and beverages'],
            ['Books', 'Books and reading materials'],
            ['Furniture', 'Home and office furniture']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }
        echo "✓ Sample categories created<br>";
        
        // Insert sample units
        $units = [
            ['Piece', 'pcs'],
            ['Kilogram', 'kg'],
            ['Liter', 'l'],
            ['Box', 'box'],
            ['Pack', 'pack']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO units (name, abbreviation) VALUES (?, ?)");
        foreach ($units as $unit) {
            $stmt->execute($unit);
        }
        echo "✓ Sample units created<br>";
        
        // Insert sample products
        $products = [
            ['Laptop', 'High-performance laptop computer', 1, 1, 999.99, 850.00, 5, 2],
            ['T-Shirt', 'Cotton t-shirt', 2, 1, 19.99, 10.00, 50, 10],
            ['Coffee', 'Premium coffee beans', 3, 2, 12.99, 8.00, 100, 20],
            ['Python Book', 'Python programming guide', 4, 1, 39.99, 25.00, 30, 5],
            ['Office Chair', 'Ergonomic office chair', 5, 1, 199.99, 120.00, 15, 3]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, category_id, unit_id, price, cost, quantity, min_stock_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        echo "✓ Sample products created<br>";
    } else {
        echo "✓ Products already exist<br>";
    }
    
    echo "<hr>";
    echo "<h2>✓ Setup Complete!</h2>";
    echo "<p>You can now access the application:</p>";
    echo "<ul>";
    echo "<li><a href='login.html'>Login Page</a></li>";
    echo "</ul>";
    echo "<p><strong>Default admin login:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
    echo "<p><a href='setup_data.php'>Try again</a></p>";
}
?>
