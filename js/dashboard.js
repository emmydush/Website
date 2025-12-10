document.addEventListener('DOMContentLoaded', function() {
    // Add animation to stat cards when they come into view
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        // Add delay for staggered animation
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('slide-up');
    });

    // Add hover effects to action buttons
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.classList.add('pulse');
        });
        
        button.addEventListener('mouseleave', function() {
            this.classList.remove('pulse');
        });
    });

    // Add click events to edit and delete buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const productId = row.cells[0].textContent;
            alert(`Edit product with ID: ${productId}`);
            // In a real application, this would open an edit modal or redirect to edit page
        });
    });
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const productId = row.cells[0].textContent;
            
            // Confirm deletion
            if (confirm(`Are you sure you want to delete product with ID: ${productId}?`)) {
                // Send delete request to server
                fetch('php/delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        row.remove();
                        alert('Product deleted successfully!');
                        
                        // Add animation effect
                        row.style.transition = 'all 0.3s ease';
                        row.style.transform = 'translateX(100%)';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                        }, 300);
                    } else {
                        alert('Error deleting product: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the product');
                });
            }
        });
    });

    // Logout functionality
    const logoutBtn = document.querySelector('.logout-btn');
    logoutBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to logout?')) {
            // Add animation
            document.body.style.transition = 'opacity 0.5s ease';
            document.body.style.opacity = '0';
            
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 500);
        }
    });

    // Add product button functionality
    const addProductBtn = document.querySelector('.action-btn:first-child');
    addProductBtn.addEventListener('click', function() {
        // Add bounce animation
        this.classList.add('pulse');
        setTimeout(() => {
            this.classList.remove('pulse');
        }, 600);
        
        alert('Add product functionality would open here');
        // In a real application, this would open a modal or redirect to add product page
    });
    
    // Load products from server
    loadProducts();
    
    // Load stock alerts
    loadStockAlerts();
});

// Function to load products
function loadProducts() {
    fetch('php/get_products.php')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // In a real application, you would populate the table with the data
            console.log('Products loaded:', data.data);
        }
    })
    .catch(error => {
        console.error('Error loading products:', error);
    });
}

// Function to load stock alerts
function loadStockAlerts() {
    fetch('php/stock_alerts.php')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // In a real application, you would populate the alerts section
            console.log('Stock alerts loaded:', data.alerts);
            
            // Add animation to alerts
            const alertItems = document.querySelectorAll('.alert-item');
            alertItems.forEach((item, index) => {
                // Add delay for staggered animation
                item.style.animationDelay = `${index * 0.2}s`;
                item.classList.add('fade-in');
            });
        }
    })
    .catch(error => {
        console.error('Error loading stock alerts:', error);
    });
}