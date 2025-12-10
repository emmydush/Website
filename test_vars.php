<?php
session_start();

// Initialize variables with default values
$userName = isset($_SESSION['username']) ? $_SESSION['username'] : "Emmanuel";
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : "Administrator";

echo "User Name: " . $userName . "<br>";
echo "User Role: " . $userRole . "<br>";

// Check if session variables are set
echo "Session user_id set: " . (isset($_SESSION['user_id']) ? "Yes" : "No") . "<br>";
echo "Session username set: " . (isset($_SESSION['username']) ? "Yes" : "No") . "<br>";
echo "Session role set: " . (isset($_SESSION['role']) ? "Yes" : "No") . "<br>";
?>