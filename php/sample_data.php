<?php
require_once 'db_connect.php';

// Insert sample categories
$categories = [
    ['Electronics', 'Electronic devices and accessories'],
    ['Furniture', 'Home and office furniture'],
    ['Stationery', 'Office supplies and stationery'],
    ['Clothing', 'Apparel and fashion items'],
    ['Food & Beverages', 'Edible products and drinks']
];

foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute($category);
}

// Insert sample suppliers
$suppliers = [
    ['Tech Solutions Inc.', 'John Smith', 'john@techsolutions.com', '123-456-7890', '123 Tech Street, Silicon Valley'],
    ['Furniture World', 'Sarah Johnson', 'sarah@furnitureworld.com', '234-567-8901', '456 Furniture Ave, Design District'],
    ['Office Supplies Co.', 'Mike Brown', 'mike@officesupplies.com', '345-678-9012', '789 Office Blvd, Business Park']
];

foreach ($suppliers as $supplier) {
    $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute($supplier);
}

// Insert sample products
$products = [
    ['Wireless Headphones', 'High-quality wireless headphones with noise cancellation', 1, 1, 129.99, 42, 10, 'WH001'],
    ['Bluetooth Speaker', 'Portable Bluetooth speaker with excellent sound quality', 1, 1, 89.99, 5, 15, 'BS001'],
    ['Office Chair', 'Ergonomic office chair with lumbar support', 2, 2, 199.99, 15, 5, 'OC001'],
    ['Desk Lamp', 'LED desk lamp with adjustable brightness', 2, 2, 39.99, 0, 8, 'DL001'],
    ['Notebook Set', 'Pack of 5 premium notebooks', 3, 3, 14.99, 120, 20, 'NS001'],
    ['Designer T-Shirt', 'Cotton t-shirt with modern design', 4, 2, 24.99, 75, 10, 'TS001']
];

foreach ($products as $product) {
    $stmt = $pdo->prepare("
        INSERT INTO products (name, description, category_id, supplier_id, price, quantity, min_stock_level, barcode) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute($product);
    
    // Log initial stock transaction
    $product_id = $pdo->lastInsertId();
    $transaction_stmt = $pdo->prepare("INSERT INTO transactions (product_id, type, quantity, reason) VALUES (?, 'in', ?, 'Initial stock')");
    $transaction_stmt->execute([$product_id, $product[5]]);
}

// Insert sample user
$username = 'admin';
$email = 'admin@example.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

$stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $email, $password, $role]);

echo "Sample data inserted successfully!";
?>