<?php
// Start the session to access session variables
session_start();

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page or another page after logging out
header('Location: index.html');
exit();
?>
