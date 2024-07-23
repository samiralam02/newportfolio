<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the "Remember Me" cookie if it exists
if (isset($_COOKIE['user_email'])) {
    setcookie('user_email', '', time() - 3600, "/"); // setting the expiration time to the past deletes the cookie
}

// Notify the client-side to log out from all tabs
echo json_encode(['status' => 'logged_out']);
?>
