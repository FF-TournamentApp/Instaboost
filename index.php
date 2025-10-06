<?php
session_start();

// If user is logged in, send them to main dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit;
}

// Otherwise, send them to login/register page
header("Location: auth.php");
exit;
?>
