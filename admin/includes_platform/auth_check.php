<?php
/**
 * Admin Authentication Check
 * Include this at the top of all admin pages
 */
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: platform_login.php");
    exit();
}

// Include privileges
require_once __DIR__ . '/admin_privileges.php';

// Store admin info for easy access
$current_admin = [
    'id' => $_SESSION['admin_id'],
    'name' => $_SESSION['admin_name'],
    'role' => $_SESSION['admin_role'],
    'email' => $_SESSION['admin_email'] ?? ''
];
