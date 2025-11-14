<?php
/**
 * Server Setup Script for Uploads Directory
 * Upload this file to your server and run it once via browser
 * URL: http://169.239.251.102:442/~princess.donkor/tourlink/setup_uploads_server.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>TourLink Server Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>TourLink Server Setup</h1>
    <p>Setting up uploads directory on server...</p>

<?php
$results = array();
$base_dir = __DIR__;
$uploads_dir = $base_dir . '/uploads';
$profile_pics_dir = $uploads_dir . '/profile_pictures';

// Create uploads directory
if (!is_dir($uploads_dir)) {
    if (mkdir($uploads_dir, 0755)) {
        $results[] = array('success', 'Created uploads directory');
    } else {
        $results[] = array('error', 'Failed to create uploads directory');
    }
} else {
    $results[] = array('info', 'Uploads directory already exists');
}

// Create profile_pictures subdirectory
if (!is_dir($profile_pics_dir)) {
    if (mkdir($profile_pics_dir, 0777)) {
        $results[] = array('success', 'Created profile_pictures directory');
        chmod($profile_pics_dir, 0777); // Ensure permissions
    } else {
        $results[] = array('error', 'Failed to create profile_pictures directory');
    }
} else {
    $results[] = array('info', 'Profile pictures directory already exists');
    chmod($profile_pics_dir, 0777);
    $results[] = array('success', 'Set permissions to 777');
}

// Create index.php for security
$index_file = $uploads_dir . '/index.php';
if (!file_exists($index_file)) {
    $content = "<?php\nhttp_response_code(403);\ndie('Access denied');\n?>";
    if (file_put_contents($index_file, $content)) {
        $results[] = array('success', 'Created security index.php');
    } else {
        $results[] = array('error', 'Failed to create index.php');
    }
}

// Create .htaccess
$htaccess_file = $profile_pics_dir . '/.htaccess';
if (!file_exists($htaccess_file)) {
    $content = "# Prevent PHP execution in uploads directory\nphp_flag engine off\nAddType text/plain .php .php3 .php4 .php5 .phtml .phps\n";
    if (file_put_contents($htaccess_file, $content)) {
        $results[] = array('success', 'Created .htaccess security file');
    } else {
        $results[] = array('error', 'Failed to create .htaccess');
    }
}

// Test write permissions
$test_file = $profile_pics_dir . '/test_' . time() . '.txt';
if (file_put_contents($test_file, 'test')) {
    $results[] = array('success', 'Write test successful - directory is writable!');
    unlink($test_file);
} else {
    $results[] = array('error', 'Write test failed - directory is NOT writable');
}

// Display results
foreach ($results as $result) {
    echo "<div class='{$result[0]}'>{$result[1]}</div>";
}

// Show directory info
echo "<h2>Directory Information</h2>";
echo "<pre>";
echo "Base Directory: " . $base_dir . "\n";
echo "Uploads Directory: " . $uploads_dir . "\n";
echo "Profile Pictures: " . $profile_pics_dir . "\n\n";

echo "Uploads Dir Exists: " . (is_dir($uploads_dir) ? 'Yes' : 'No') . "\n";
echo "Uploads Dir Writable: " . (is_writable($uploads_dir) ? 'Yes' : 'No') . "\n";
echo "Uploads Dir Permissions: " . (is_dir($uploads_dir) ? substr(sprintf('%o', fileperms($uploads_dir)), -4) : 'N/A') . "\n\n";

echo "Profile Pics Exists: " . (is_dir($profile_pics_dir) ? 'Yes' : 'No') . "\n";
echo "Profile Pics Writable: " . (is_writable($profile_pics_dir) ? 'Yes' : 'No') . "\n";
echo "Profile Pics Permissions: " . (is_dir($profile_pics_dir) ? substr(sprintf('%o', fileperms($profile_pics_dir)), -4) : 'N/A') . "\n\n";

if (function_exists('posix_geteuid')) {
    $user_info = posix_getpwuid(posix_geteuid());
    echo "PHP Running As: " . $user_info['name'] . "\n";
}

echo "</pre>";

echo "<div class='info'><strong>Next Steps:</strong><br>";
echo "1. If all checks passed, delete this file for security<br>";
echo "2. Try uploading a profile picture again<br>";
echo "3. If it still fails, check error logs or contact server admin</div>";
?>

</body>
</html>
