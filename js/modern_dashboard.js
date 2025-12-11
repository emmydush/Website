// Modern Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Update current date and time
    function updateDateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
    }
    
    // Initial update
    updateDateTime();
    
    // Update every minute
    setInterval(updateDateTime, 60000);
    
    // Initialize Toast notification system
    const toast = new Toast({ duration: 4000 });
    
    // User menu dropdown functionality
    const userMenu = document.getElementById('userMenu');
    const userDropdown = document.getElementById('userDropdown');
    
    userMenu.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userMenu.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });
    
    // Notification badge click handler
    const notificationBadge = document.querySelector('.notifications');
    if (notificationBadge) {
        notificationBadge.addEventListener('click', function(e) {
            e.stopPropagation();
            loadNotifications();
        });
    }
    
    // Sidebar menu item click handler
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach((item, index) => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(8px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(0) scale(1)';
            }
        });
        
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Get the menu item text
            const menuItemText = this.querySelector('span').textContent;

            // Handle logout separately
            if (this.classList.contains('logout')) {
                showConfirm('Are you sure you want to logout?', () => {
                    toast.show('Logging out...', 'info', {
                        title: 'See you soon!'
                    });
                    setTimeout(() => {
                        window.location.href = 'logout.php';
                    }, 1000);
                });
                return; // Stop further processing
            }

            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.pointerEvents = 'none';
            ripple.className = 'ripple';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);

            // Remove active class from all items
            menuItems.forEach(i => i.classList.remove('active'));

            // Add active class to clicked item
            this.classList.add('active');

            // Navigation mapping
            const navigationMap = {
                'Home': 'modern_dashboard.php',
                'Products': 'products.php',
                'Categories': 'categories.php',
                'Units': 'units.php',
                'Sales': 'sales.php',
                'Point of Sale': 'pos.php',
                'Credit Sales': 'credit_sales.php',
                'Customers': 'customers.php',
                'Reports': 'reports.php',
                'Settings': 'settings.php'
            };

            const targetPage = navigationMap[menuItemText];
            if (targetPage) {
                window.location.href = targetPage;
            }
        });
    });
    
    // Search bar functionality
    const searchBar = document.querySelector('.search-bar input');
    searchBar.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value.trim();
            if (searchTerm) {
                alert(`Searching for: ${searchTerm}`);
                // In a real application, you would perform the search
                this.value = '';
            }
        }
    });
    
    // Initialize charts with real data
    initCharts();
});

// Function to load and display notifications
function loadNotifications() {
    fetch('php/stock_alerts.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.alerts.length > 0) {
                // Create notification dropdown
                createNotificationDropdown(data.alerts);
            } else {
                // Show info message if no alerts
                const toast = new Toast();
                toast.info('No new notifications', {
                    title: 'All Clear'
                });
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            const toast = new Toast();
            toast.error('Failed to load notifications', {
                title: 'Error'
            });
        });
}

// Function to create notification dropdown
function createNotificationDropdown(alerts) {
    // Remove existing dropdown if present
    const existingDropdown = document.querySelector('.notification-dropdown');
    if (existingDropdown) {
        existingDropdown.remove();
    }
    
    // Create dropdown container
    const dropdown = document.createElement('div');
    dropdown.className = 'notification-dropdown';
    
    // Create dropdown header
    const header = document.createElement('div');
    header.className = 'notification-header';
    header.innerHTML = `
        <h3>Notifications (${alerts.length})</h3>
        <button class="close-dropdown">&times;</button>
    `;
    
    // Create dropdown content
    const content = document.createElement('div');
    content.className = 'notification-content';
    
    // Add alerts to content
    alerts.forEach(alert => {
        const alertElement = document.createElement('div');
        alertElement.className = `notification-item ${alert.type}`;
        
        // Set icon based on alert type
        let iconClass = '';
        if (alert.type === 'out_of_stock') {
            iconClass = 'fas fa-times-circle';
        } else if (alert.type === 'critical_low') {
            iconClass = 'fas fa-exclamation-triangle';
        } else {
            iconClass = 'fas fa-exclamation-circle';
        }
        
        alertElement.innerHTML = `
            <i class="${iconClass}"></i>
            <div class="notification-text">${alert.message}</div>
        `;
        
        content.appendChild(alertElement);
    });
    
    // Add header and content to dropdown
    dropdown.appendChild(header);
    dropdown.appendChild(content);
    
    // Add dropdown to notifications container
    const notificationsContainer = document.querySelector('.notifications');
    notificationsContainer.appendChild(dropdown);
    
    // Add close button event
    const closeBtn = dropdown.querySelector('.close-dropdown');
    closeBtn.addEventListener('click', function() {
        dropdown.remove();
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!notificationsContainer.contains(e.target)) {
            dropdown.remove();
            document.removeEventListener('click', closeDropdown);
        }
    });
}

// Initialize charts using Chart.js with real data
function initCharts() {
    // Fetch real data from the server
    fetch('php/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update bar chart for Fast-Moving vs Slow-Moving Items
                const barCtx = document.getElementById('barChart').getContext('2d');
                const barLabels = data.charts.fast_moving_items.map(item => item.name);
                const barData = data.charts.fast_moving_items.map(item => parseInt(item.units_sold));
                
                const barChart = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: barLabels,
                        datasets: [{
                            label: 'Units Sold',
                            data: barData,
                            backgroundColor: [
                                'rgba(76, 175, 80, 0.7)',
                                'rgba(76, 175, 80, 0.7)',
                                'rgba(76, 175, 80, 0.7)',
                                'rgba(255, 82, 82, 0.7)',
                                'rgba(255, 82, 82, 0.7)',
                                'rgba(255, 82, 82, 0.7)'
                            ],
                            borderColor: [
                                'rgba(76, 175, 80, 1)',
                                'rgba(76, 175, 80, 1)',
                                'rgba(76, 175, 80, 1)',
                                'rgba(255, 82, 82, 1)',
                                'rgba(255, 82, 82, 1)',
                                'rgba(255, 82, 82, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Units Sold: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
                
                // Update pie chart for Category-wise Stock Distribution
                const pieCtx = document.getElementById('pieChart').getContext('2d');
                const pieLabels = data.charts.category_distribution.map(item => item.category);
                const pieData = data.charts.category_distribution.map(item => parseInt(item.product_count));
                
                // Generate colors dynamically
                const backgroundColors = pieLabels.map((_, index) => {
                    const colors = [
                        'rgba(33, 150, 243, 0.8)',
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(156, 39, 176, 0.8)',
                        'rgba(255, 82, 82, 0.8)',
                        'rgba(0, 188, 212, 0.8)'
                    ];
                    return colors[index % colors.length];
                });
                
                const borderColors = pieLabels.map((_, index) => {
                    const colors = [
                        'rgba(33, 150, 243, 1)',
                        'rgba(255, 152, 0, 1)',
                        'rgba(76, 175, 80, 1)',
                        'rgba(156, 39, 176, 1)',
                        'rgba(255, 82, 82, 1)',
                        'rgba(0, 188, 212, 1)'
                    ];
                    return colors[index % colors.length];
                });
                
                const pieChart = new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieData,
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.label}: ${context.parsed}`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                console.error('Error fetching dashboard data:', data.message);
                // Fallback to static charts if data fetch fails
                createStaticCharts();
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            // Fallback to static charts if data fetch fails
            createStaticCharts();
        });
}

// Create static charts as fallback
function createStaticCharts() {
    // Bar chart for Fast-Moving vs Slow-Moving Items
    const barCtx = document.getElementById('barChart').getContext('2d');
    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Product A', 'Product B', 'Product C', 'Product D', 'Product E', 'Product F'],
            datasets: [{
                label: 'Units Sold',
                data: [120, 85, 65, 45, 30, 15],
                backgroundColor: [
                    'rgba(76, 175, 80, 0.7)',
                    'rgba(76, 175, 80, 0.7)',
                    'rgba(76, 175, 80, 0.7)',
                    'rgba(255, 82, 82, 0.7)',
                    'rgba(255, 82, 82, 0.7)',
                    'rgba(255, 82, 82, 0.7)'
                ],
                borderColor: [
                    'rgba(76, 175, 80, 1)',
                    'rgba(76, 175, 80, 1)',
                    'rgba(76, 175, 80, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(255, 82, 82, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Units Sold: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Pie chart for Category-wise Stock Distribution
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Electronics', 'Clothing', 'Food & Beverages', 'Books', 'Home & Garden', 'Sports'],
            datasets: [{
                data: [30, 25, 20, 10, 10, 5],
                backgroundColor: [
                    'rgba(33, 150, 243, 0.8)',
                    'rgba(255, 152, 0, 0.8)',
                    'rgba(76, 175, 80, 0.8)',
                    'rgba(156, 39, 176, 0.8)',
                    'rgba(255, 82, 82, 0.8)',
                    'rgba(0, 188, 212, 0.8)'
                ],
                borderColor: [
                    'rgba(33, 150, 243, 1)',
                    'rgba(255, 152, 0, 1)',
                    'rgba(76, 175, 80, 1)',
                    'rgba(156, 39, 176, 1)',
                    'rgba(255, 82, 82, 1)',
                    'rgba(0, 188, 212, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.parsed}%`;
                        }
                    }
                }
            }
        }
    });
}