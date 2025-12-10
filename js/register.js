document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const role = document.getElementById('role').value;
        
        // Simple validation
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }
        
        if (password.length < 6) {
            alert('Password must be at least 6 characters long!');
            return;
        }
        
        // Send data to server
        fetch('php/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                username: username,
                email: email,
                password: password,
                role: role
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                // Redirect to login page
                window.location.href = 'login.html';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during registration');
        });
    });
});