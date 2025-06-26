<?php
session_start();

// Redirect to login if not authenticated as admin
if (!isset($_SESSION['account_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}
?>