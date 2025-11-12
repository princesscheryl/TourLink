<?php
// ============================================
// Database Credentials Template for SERVER
// ============================================
// IMPORTANT: Copy this file to settings/db_cred.php on your server
// and update with your actual server database credentials
//
// DO NOT commit the actual db_cred.php to git (it's in .gitignore)
// ============================================

if (!defined("SERVER")) {
    // Update this with your server's database host
    // Usually "localhost" but could be different on shared hosting
    define("SERVER", "localhost");
}

if (!defined("USERNAME")) {
    // Update with your database username
    // On the lab server this is typically: princess.donkor
    define("USERNAME", "princess.donkor");
}

if (!defined("PASSWD")) {
    // Update with your database password
    define("PASSWD", "your_database_password_here");
}

if (!defined("DATABASE")) {
    // Update with your database name
    // Based on schema: ecommerce_2025A_princess_donkor
    // OR if you created a different database, use that name
    define("DATABASE", "ecommerce_2025A_princess_donkor");
}

// ============================================
// SETUP INSTRUCTIONS:
// ============================================
// 1. SSH into your server: ssh -C princess.donkor@169.239.251.102 -p 422
// 2. Navigate to tourlink: cd ~/public_html/tourlink
// 3. Create the file: nano settings/db_cred.php
// 4. Copy the content from this template
// 5. Update the credentials with your actual values
// 6. Save and exit (Ctrl+X, then Y, then Enter)
// 7. Set permissions: chmod 600 settings/db_cred.php
// ============================================
?>
