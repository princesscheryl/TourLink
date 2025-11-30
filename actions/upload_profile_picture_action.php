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
require_once '../classes/hosted_upload_class.php';

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

// Upload file to hosted uploads service
$upload_result = HostedUpload::uploadFile($file['tmp_name'], $file['name']);

if (!$upload_result['success']) {
    $response['status'] = 'error';
    $response['message'] = $upload_result['message'];
    echo json_encode($response);
    exit();
}

// Get the hosted file URL
$hosted_file_url = $upload_result['url'];

// Update database with hosted file URL
$user_class = new TourlinkUser();
$user = $user_class->get_user_by_id($_SESSION['user_id']);

// Delete old profile picture if exists (only if it's a local file)
if (!empty($user['profile_image'])) {
    // Only try to delete if it's a local path, not a hosted URL
    if (!HostedUpload::isHostedUrl($user['profile_image'])) {
        $base_dir = dirname(__DIR__);
        $old_file_path = $base_dir . '/' . $user['profile_image'];
        if (file_exists($old_file_path)) {
            @unlink($old_file_path); // @ suppresses warnings if deletion fails
        }
    }
}

// Save hosted URL to database
$updated = $user_class->update_user($_SESSION['user_id'], array('profile_image' => $hosted_file_url));

if ($updated) {
    // Update session
    $_SESSION['profile_image'] = $hosted_file_url;

    $response['status'] = 'success';
    $response['message'] = 'Profile picture uploaded successfully!';
    $response['image_path'] = $hosted_file_url;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to update profile picture in database';
}

echo json_encode($response);
?>
