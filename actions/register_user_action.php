<?php
header('Content-Type: application/json');
session_start();

$response = array();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/tourlink_user_controller.php';

// Get form data
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : 'tourist';

$errors = array();

// Validate inputs
if (empty($first_name) || strlen($first_name) > 50) {
    $errors[] = 'First name is required and must be less than 50 characters';
}
if (empty($last_name) || strlen($last_name) > 50) {
    $errors[] = 'Last name is required and must be less than 50 characters';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = 'Please provide a valid email address';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long';
}
if (!empty($phone) && strlen($phone) > 20) {
    $errors[] = 'Phone number must be less than 20 characters';
}
if (!in_array($user_type, ['tourist', 'provider'])) {
    $errors[] = 'Invalid user type selected';
}

if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = $errors[0];
} else if (tourlink_email_exists_ctr($email)) {
    $response['status'] = 'error';
    $response['message'] = 'Email already exists. Please login or use a different email.';
} else {
    $user_id = register_tourlink_user_ctr($email, $password, $user_type, $first_name, $last_name, $phone);

    if ($user_id) {
        $response['status'] = 'success';

        if ($user_type === 'provider') {
            $response['message'] = 'Registration successful! Your account is pending verification.';
        } else {
            $response['message'] = 'Registration successful! You can now login.';
        }

        $response['user_id'] = $user_id;
        $response['redirect'] = '../login/login.php';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to register. Please try again.';
    }
}

echo json_encode($response);