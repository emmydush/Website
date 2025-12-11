<?php
echo "PHP is working! Current time: " . date('Y-m-d H:i:s') . "\n";

// Check if we can connect to MySQL
$link = mysqli_connect("localhost", "root", "", "inventory_management");

if (!$link) {
    echo "MySQL connection failed: " . mysqli_connect_error() . "\n";
} else {
    echo "MySQL connection successful!\n";
    
    // Check if products table exists
    $result = mysqli_query($link, "SHOW TABLES LIKE 'products'");
    if (mysqli_num_rows($result) > 0) {
        echo "Products table exists\n";
        
        // Count products
        $result = mysqli_query($link, "SELECT COUNT(*) as count FROM products");
        $row = mysqli_fetch_assoc($result);
        echo "Number of products: " . $row['count'] . "\n";
    } else {
        echo "Products table does not exist\n";
    }
    
    mysqli_close($link);
}
?>