<?php
// tourlink Server Diagnostic Script
// This file helps diagnose issues on the deployed server

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>TourLink Server Diagnostic</h1>";
echo "<hr>";

// 1. Check PHP version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 7.0 or higher<br>";
echo "<hr>";

// 2. Check if db_cred.php exists
echo "<h2>2. Database Credentials File</h2>";
$db_cred_path = __DIR__ . '/settings/db_cred.php';
if (file_exists($db_cred_path)) {
    echo "✓ db_cred.php EXISTS<br>";
    require_once $db_cred_path;

    // Check if constants are defined
    echo "<h3>Database Configuration:</h3>";
    echo "SERVER: " . (defined('SERVER') ? SERVER : 'NOT DEFINED') . "<br>";
    echo "USERNAME: " . (defined('USERNAME') ? USERNAME : 'NOT DEFINED') . "<br>";
    echo "PASSWD: " . (defined('PASSWD') ? (PASSWD ? '***SET***' : 'EMPTY') : 'NOT DEFINED') . "<br>";
    echo "DATABASE: " . (defined('DATABASE') ? DATABASE : 'NOT DEFINED') . "<br>";
} else {
    echo "✗ db_cred.php DOES NOT EXIST<br>";
    echo "<strong>This is likely the problem!</strong><br>";
    echo "Expected location: " . $db_cred_path . "<br>";
}
echo "<hr>";

// 3. Test database connection
echo "<h2>3. Database Connection Test</h2>";
if (defined('SERVER') && defined('USERNAME') && defined('PASSWD') && defined('DATABASE')) {
    $conn = mysqli_connect(SERVER, USERNAME, PASSWD, DATABASE);

    if (mysqli_connect_errno()) {
        echo "✗ Connection FAILED<br>";
        echo "Error: " . mysqli_connect_error() . "<br>";
        echo "Error Code: " . mysqli_connect_errno() . "<br>";
    } else {
        echo "✓ Connection SUCCESSFUL<br>";

        // Test if required tables exist
        echo "<h3>Checking for tables:</h3>";
        $tables_to_check = ['products', 'categories', 'brands', 'customer', 'orders'];
        foreach ($tables_to_check as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if ($result && mysqli_num_rows($result) > 0) {
                echo "✓ Table '$table' exists<br>";
            } else {
                echo "✗ Table '$table' NOT found<br>";
            }
        }

        mysqli_close($conn);
    }
} else {
    echo "Cannot test connection - database constants not defined<br>";
}
echo "<hr>";

// 4. Check file permissions
echo "<h2>4. File Permissions</h2>";
$important_files = [
    'index.php',
    'settings/core.php',
    'settings/db_class.php',
    'settings/db_cred.php',
    'controllers/product_controller.php',
    'classes/product_class.php'
];

foreach ($important_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $perms = substr(sprintf('%o', fileperms($full_path)), -4);
        echo "✓ $file - Permissions: $perms<br>";
    } else {
        echo "✗ $file - FILE NOT FOUND<br>";
    }
}
echo "<hr>";

// 5. Check required PHP extensions
echo "<h2>5. Required PHP Extensions</h2>";
$required_extensions = ['mysqli', 'session', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext - LOADED<br>";
    } else {
        echo "✗ $ext - NOT LOADED<br>";
    }
}
echo "<hr>";

echo "<h2>Diagnostic Complete</h2>";
echo "<p>If db_cred.php is missing, you need to create it on the server with the correct credentials.</p>";
?>
