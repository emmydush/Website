# Inventory Management System

A modern, responsive inventory management system built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features

- User Authentication (Login/Register)
- Dashboard with statistics
- Product Management (Add, Edit, Delete)
- Category and Supplier Management
- Real-time Stock Alerts
- Responsive Design
- Modern UI/UX with Animations

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Styling**: CSS3 with modern gradients and animations
- **Icons**: Font Awesome

## Installation

1. Clone this repository to your local server (XAMPP, WAMP, LAMP, etc.)
2. Create a MySQL database named `inventory_management`
3. Import the `database_schema.sql` file to create the necessary tables
4. Update the database credentials in `php/db_connect.php` if needed
5. Run `php/sample_data.php` to insert sample data
6. Access the application through your web browser

## File Structure

```
├── css/
│   └── style.css          # Main stylesheet
├── js/
│   ├── login.js           # Login functionality
│   ├── register.js        # Registration functionality
│   └── dashboard.js       # Dashboard functionality
├── php/
│   ├── db_connect.php     # Database connection
│   ├── login.php          # Login processing
│   ├── register.php       # Registration processing
│   ├── get_products.php   # Fetch products
│   ├── add_product.php    # Add new product
│   ├── update_product.php # Update product
│   ├── delete_product.php # Delete product
│   ├── stock_alerts.php   # Stock alerts
│   └── sample_data.php    # Sample data insertion
├── database_schema.sql    # Database schema
└── *.html                 # HTML pages
```

## Database Schema

The system uses the following tables:
- `users` - User accounts
- `categories` - Product categories
- `suppliers` - Product suppliers
- `products` - Product inventory
- `transactions` - Stock movement history
- `sales` - Sales records

## Default Login

After running the sample data script, you can log in with:
- Username: `admin`
- Password: `admin123`

## Key Features

### Modern UI/UX Design
- Glassmorphism design with blurred backgrounds
- Smooth animations and transitions
- Responsive layout for all device sizes
- Intuitive navigation

### Inventory Management
- Real-time stock tracking
- Low stock alerts
- Product categorization
- Supplier information

### Security
- Password hashing
- Prepared statements to prevent SQL injection
- Session management

## Customization

You can customize the system by modifying:
- `css/style.css` - Colors, fonts, and styling
- `js/*.js` - Frontend functionality
- `php/*.php` - Backend logic

## License

This project is open source and available under the MIT License.