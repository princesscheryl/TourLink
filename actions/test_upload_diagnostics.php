<?php
session_start();

// Diagnostic script to check upload configuration
$diagnostics = array();

// Check PHP upload settings
$diagnostics['php_settings'] = array(
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'file_uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
    'max_file_uploads' => ini_get('max_file_uploads')
);

// Check directory permissions
$base_dir = dirname(__DIR__);
$upload_dir = $base_dir . '/uploads/profile_pictures/';

$diagnostics['directory_info'] = array(
    'base_dir' => $base_dir,
    'upload_dir' => $upload_dir,
    'exists' => is_dir($upload_dir),
    'readable' => is_readable($upload_dir),
    'writable' => is_writable($upload_dir),
    'permissions' => is_dir($upload_dir) ? substr(sprintf('%o', fileperms($upload_dir)), -4) : 'N/A'
);

// Check user/ownership
$diagnostics['ownership'] = array(
    'apache_user' => posix_getpwuid(posix_geteuid())['name'],
    'dir_owner' => is_dir($upload_dir) ? posix_getpwuid(fileowner($upload_dir))['name'] : 'N/A',
    'dir_group' => is_dir($upload_dir) ? posix_getgrgid(filegroup($upload_dir))['name'] : 'N/A'
);

// Try to write a test file
$test_file = $upload_dir . 'test_' . time() . '.txt';
$write_test = @file_put_contents($test_file, 'test content');
$diagnostics['write_test'] = array(
    'success' => $write_test !== false,
    'file_created' => file_exists($test_file)
);

// Clean up test file
if (file_exists($test_file)) {
    @unlink($test_file);
}

header('Content-Type: application/json');
echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>
