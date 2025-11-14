<?php
header('Content-Type: application/json');
session_start();

$response = array();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You must be logged in to upload a profile picture';
    echo json_encode($response);
    exit();
}

require_once '../classes/tourlink_user_class.php';

// Check if file was uploaded
if (!isset($_FILES['profile_picture'])) {
    $response['status'] = 'error';
    $response['message'] = 'No file was selected';
    echo json_encode($response);
    exit();
}

// Check for upload errors
if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload directory',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload'
    ];
    $error_code = $_FILES['profile_picture']['error'];
    $response['status'] = 'error';
    $response['message'] = isset($error_messages[$error_code]) ? $error_messages[$error_code] : 'Unknown upload error';
    echo json_encode($response);
    exit();
}

$file = $_FILES['profile_picture'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
$file_type = $file['type'];

if (!in_array($file_type, $allowed_types)) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid file type. Only JPG, JPEG, and PNG are allowed';
    echo json_encode($response);
    exit();
}

// Validate file size (2MB max)
$max_size = 2 * 1024 * 1024; // 2MB in bytes
if ($file['size'] > $max_size) {
    $response['status'] = 'error';
    $response['message'] = 'File size exceeds 2MB limit';
    echo json_encode($response);
    exit();
}

// Get absolute path to uploads directory
$base_dir = dirname(__DIR__); // Get parent directory (tourlink)
$upload_dir = $base_dir . '/uploads/profile_pictures/';

// Set umask to allow full permissions
$old_umask = umask(0);

// Create uploads directory if it doesn't exist
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        umask($old_umask);
        $response['status'] = 'error';
        $response['message'] = 'Failed to create upload directory';
        echo json_encode($response);
        exit();
    }
    // Explicitly set permissions after creation
    @chmod($upload_dir, 0777);

    // Create .htaccess for security (prevent PHP execution in uploads)
    $htaccess_content = "# Prevent PHP execution in uploads directory\n";
    $htaccess_content .= "php_flag engine off\n";
    $htaccess_content .= "AddType text/plain .php .php3 .php4 .php5 .phtml .phps\n";
    @file_put_contents($upload_dir . '.htaccess', $htaccess_content);
}

// Restore original umask
umask($old_umask);

// Check if directory is writable
if (!is_writable($upload_dir)) {
    // Try to set permissions one more time
    @chmod($upload_dir, 0777);

    // If still not writable, provide detailed error
    if (!is_writable($upload_dir)) {
        $response['status'] = 'error';
        $response['message'] = 'Upload directory exists but is not writable. Directory: ' . $upload_dir;
        $response['debug'] = [
            'exists' => is_dir($upload_dir),
            'writable' => is_writable($upload_dir),
            'permissions' => substr(sprintf('%o', fileperms($upload_dir)), -4)
        ];
        echo json_encode($response);
        exit();
    }
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    $response['status'] = 'error';
    $response['message'] = 'Failed to save uploaded file. Please check directory permissions.';
    echo json_encode($response);
    exit();
}

// Update database with file path
$user_class = new TourlinkUser();
$user = $user_class->get_user_by_id($_SESSION['user_id']);

// Delete old profile picture if exists
if (!empty($user['profile_image'])) {
    $old_file_path = $base_dir . '/' . $user['profile_image'];
    if (file_exists($old_file_path)) {
        @unlink($old_file_path); // @ suppresses warnings if deletion fails
    }
}

// Save relative path to database
$db_path = 'uploads/profile_pictures/' . $new_filename;
$updated = $user_class->update_user($_SESSION['user_id'], array('profile_image' => $db_path));

if ($updated) {
    // Update session
    $_SESSION['profile_image'] = $db_path;

    $response['status'] = 'success';
    $response['message'] = 'Profile picture uploaded successfully!';
    $response['image_path'] = $db_path;
} else {
    // Delete uploaded file if database update failed
    unlink($upload_path);

    $response['status'] = 'error';
    $response['message'] = 'Failed to update profile picture in database';
}

echo json_encode($response);
?>
