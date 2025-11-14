<?php
header('Content-Type: application/json');
session_start();

$response = array();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You must be logged in to perform this action';
    echo json_encode($response);
    exit();
}

require_once '../classes/tourlink_user_class.php';

$user_class = new TourlinkUser();
$user = $user_class->get_user_by_id($_SESSION['user_id']);

if (!$user) {
    $response['status'] = 'error';
    $response['message'] = 'User not found';
    echo json_encode($response);
    exit();
}

// Delete file if exists
if (!empty($user['profile_image'])) {
    $base_dir = dirname(__DIR__);
    $file_path = $base_dir . '/' . $user['profile_image'];
    if (file_exists($file_path)) {
        @unlink($file_path);
    }
}

// Update database
$updated = $user_class->update_user($_SESSION['user_id'], array('profile_image' => null));

if ($updated) {
    // Update session
    unset($_SESSION['profile_image']);

    $response['status'] = 'success';
    $response['message'] = 'Profile picture removed successfully!';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to remove profile picture';
}

echo json_encode($response);
?>
