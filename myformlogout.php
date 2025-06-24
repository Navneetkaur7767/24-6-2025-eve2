<?php
session_start(); // Start the session

// Unset all session variables
session_unset(); // Clear session variables

// Destroy the session
session_destroy(); // Destroy the session

// Redirect to the login page
header("Location: myformproject.php");
exit();
?>