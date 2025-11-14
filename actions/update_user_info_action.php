<?php
header('Content-Type: application/json');
session_start();

$response = array();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You must be logged in to update your profile';
    echo json_encode($response);
    exit();
}

require_once '../classes/tourlink_user_class.php';

// Get form data
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

$errors = array();

// Validate required fields
if (empty($first_name)) {
    $errors[] = 'First name is required';
}
if (empty($last_name)) {
    $errors[] = 'Last name is required';
}

if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = $errors[0];
    echo json_encode($response);
    exit();
}

// Update user information
$user_class = new TourlinkUser();
$user_data = array(
    'first_name' => $first_name,
    'last_name' => $last_name,
    'phone' => $phone
);

$updated = $user_class->update_user($_SESSION['user_id'], $user_data);

if ($updated) {
    // Update session data
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;

    $response['status'] = 'success';
    $response['message'] = 'Profile updated successfully!';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to update profile. Please try again.';
}

echo json_encode($response);
?>
