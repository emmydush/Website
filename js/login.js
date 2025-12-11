document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    // Initialize Toast notification system
    const toast = new Toast({ duration: 4000 });
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('login_username').value;
        const password = document.getElementById('login_password').value;
        
        // Simple validation
        if (username.trim() === '' || password.trim() === '') {
            toast.show('Please fill in all fields!', 'warning', {
                title: 'Validation Error'
            });
            return;
        }
        
        // Send data to server
        fetch('php/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                username: username,
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                toast.show('Login successful! Redirecting...', 'success', {
                    title: 'Welcome Back'
                });
                // Redirect to dashboard after a short delay
                setTimeout(() => {
                    window.location.href = 'modern_dashboard.php';
                }, 1500);
            } else {
                toast.show(data.message, 'error', {
                    title: 'Login Failed'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toast.show('An error occurred during login', 'error', {
                title: 'Connection Error'
            });
        });
    });
});