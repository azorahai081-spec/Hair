<?php
// Start the session to access its functions.
session_start();

// Unset all of the session variables.
$_SESSION = [];

// Destroy the session completely.
session_destroy();

// Redirect the user to the login page.
header('Location: login.php');
exit;
?>
