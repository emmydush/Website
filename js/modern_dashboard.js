// Modern Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.getElementById('mobileMenuToggle');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking on a menu item
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 767) {
                sidebar.classList.remove('active');
            }
        });
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 767) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickInsideToggle = menuToggle && menuToggle.contains(event.target);

            if (!isClickInsideSidebar && !isClickInsideToggle) {
                sidebar.classList.remove('active');
            }
        }
    });

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
    
    // Sidebar menu item click handler
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all items
            menuItems.forEach(i => i.classList.remove('active'));

            // Add active class to clicked item
            this.classList.add('active');

            // Get the menu item text
            const menuItemText = this.querySelector('span').textContent;

            // Handle logout separately
            if (this.classList.contains('logout')) {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'logout.php';
                }
            } else {
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

                // Debug: Log the navigation action
                console.log('Navigating to: ' + menuItemText);

                const targetPage = navigationMap[menuItemText];
                if (targetPage) {
                    window.location.href = targetPage;
                }
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
