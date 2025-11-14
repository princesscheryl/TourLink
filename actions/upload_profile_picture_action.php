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
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $response['status'] = 'error';
    $response['message'] = 'No file uploaded or upload error occurred';
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

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/profile_pictures/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    $response['status'] = 'error';
    $response['message'] = 'Failed to save uploaded file';
    echo json_encode($response);
    exit();
}

// Update database with file path
$user_class = new TourlinkUser();
$user = $user_class->get_user_by_id($_SESSION['user_id']);

// Delete old profile picture if exists
if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])) {
    unlink('../' . $user['profile_image']);
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
