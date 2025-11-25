<?php
/**
 * Admin Password Reset Utility
 * Run this once to create/reset the admin account
 * DELETE THIS FILE after use for security!
 */

require_once '../settings/db_class.php';

$db = new db_connection();
$db->db_connect();

// Admin credentials
$email = 'admin@tourlink.com';
$password = 'Admin@123';
$first_name = 'System';
$last_name = 'Admin';
$role = 'super_admin';

// Generate password hash
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$check = $db->db_fetch_one("SELECT * FROM tl_admins WHERE email = ?", [$email]);

if ($check) {
    // Update existing admin
    $stmt = $db->db->prepare("UPDATE tl_admins SET password = ?, first_name = ?, last_name = ?, role = ?, is_active = 1 WHERE email = ?");
    $stmt->bind_param("sssss", $password_hash, $first_name, $last_name, $role, $email);

    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✓ Admin password updated successfully!</h2>";
    } else {
        echo "<h2 style='color: red;'>✗ Failed to update password</h2>";
        echo "<p>Error: " . $stmt->error . "</p>";
    }
} else {
    // Create new admin
    $stmt = $db->db->prepare("INSERT INTO tl_admins (email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $email, $password_hash, $first_name, $last_name, $role);

    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✓ Admin account created successfully!</h2>";
    } else {
        echo "<h2 style='color: red;'>✗ Failed to create admin</h2>";
        echo "<p>Error: " . $stmt->error . "</p>";
    }
}

echo "<hr>";
echo "<h3>Admin Login Credentials:</h3>";
echo "<p><strong>URL:</strong> " . $_SERVER['HTTP_HOST'] . "/admin/platform_login.php</p>";
echo "<p><strong>Email:</strong> admin@tourlink.com</p>";
echo "<p><strong>Password:</strong> Admin@123</p>";
echo "<hr>";
echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file (reset_admin_password.php) immediately after use for security!</p>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2, h3 { margin-top: 0; }
        p { line-height: 1.6; }
        hr { margin: 20px 0; border: none; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
</body>
</html>
