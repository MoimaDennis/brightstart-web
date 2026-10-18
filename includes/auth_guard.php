<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
$currentUser  = htmlspecialchars($_SESSION['admin_name']);
$currentRole  = htmlspecialchars($_SESSION['admin_role']);
$currentEmail = htmlspecialchars($_SESSION['admin_email']);
?>