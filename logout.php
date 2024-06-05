<?php
// Start the session
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to the home page or login page
header("Location: index.php");
exit();
?>
