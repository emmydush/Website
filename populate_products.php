<?php
require_once 'php/db_connect.php';

try {
    // Check if products exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample categories if they don't exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
        $stmt->execute();
        $catCount = $stmt->fetchColumn();
        
        if ($catCount == 0) {
            $categories = [
                ['Electronics', 'Electronic devices and accessories'],
                ['Furniture', 'Home and office furniture'],
                ['Stationery', 'Office supplies and stationery'],
                ['Clothing', 'Apparel and fashion items'],
                ['Food & Beverages', 'Edible products and drinks']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            foreach ($categories as $category) {
                $stmt->execute($category);
            }
            echo "Inserted sample categories<br>";
        }
        
        // Insert sample suppliers if they don't exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers");
        $stmt->execute();
        $supCount = $stmt->fetchColumn();
        
        if ($supCount == 0) {
            $suppliers = [
                ['Tech Solutions Inc.', 'John Smith', 'john@techsolutions.com', '123-456-7890', '123 Tech Street, Silicon Valley'],
                ['Furniture World', 'Sarah Johnson', 'sarah@furnitureworld.com', '234-567-8901', '456 Furniture Ave, Design District'],
                ['Office Supplies Co.', 'Mike Brown', 'mike@officesupplies.com', '345-678-9012', '789 Office Blvd, Business Park']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
            foreach ($suppliers as $supplier) {
                $stmt->execute($supplier);
            }
            echo "Inserted sample suppliers<br>";
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
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, category_id, supplier_id, price, quantity, min_stock_level, barcode) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($products as $product) {
            $stmt->execute($product);
        }
        
        echo "Inserted " . count($products) . " sample products<br>";
        echo "<p><a href='products.php'>View Products</a></p>";
    } else {
        echo "Database already contains $count products<br>";
        echo "<p><a href='products.php'>View Products</a></p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>