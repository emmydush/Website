document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('login_username').value;
        const password = document.getElementById('login_password').value;
        
        // Simple validation
        if (username.trim() === '' || password.trim() === '') {
            alert('Please fill in all fields!');
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
                alert(data.message);
                // Redirect to dashboard
                window.location.href = 'dashboard.html';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during login');
        });
    });
});