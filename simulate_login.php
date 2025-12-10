<?php
session_start();

// Simulate a login by setting session variables
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'Emmanuel';
$_SESSION['role'] = 'Administrator';

echo "Login simulation complete. Session variables set:<br>";
echo "user_id: " . $_SESSION['user_id'] . "<br>";
echo "username: " . $_SESSION['username'] . "<br>";
echo "role: " . $_SESSION['role'] . "<br>";
echo "<br><a href='modern_dashboard.php'>Go to Dashboard</a>";
?>