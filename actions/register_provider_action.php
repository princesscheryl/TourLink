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
require_once '../classes/service_provider_class.php';

// Get form data
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$user_type = 'provider'; // Always provider for this form

// Business information
$business_name = isset($_POST['business_name']) ? trim($_POST['business_name']) : '';
$business_location = isset($_POST['business_location']) ? trim($_POST['business_location']) : '';
$primary_region = isset($_POST['primary_region']) ? trim($_POST['primary_region']) : '';

$errors = array();

// Validate personal information
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
if (empty($phone) || strlen($phone) > 20) {
    $errors[] = 'Phone number is required and must be less than 20 characters';
}

// Validate business information
if (empty($business_name) || strlen($business_name) > 255) {
    $errors[] = 'Business name is required and must be less than 255 characters';
}
if (empty($business_location)) {
    $errors[] = 'Business location is required';
}
if (empty($primary_region)) {
    $errors[] = 'Please select your primary business region';
}

// Validate region matches database ENUM
$valid_regions = ['Greater Accra', 'Central', 'Ashanti', 'Northern'];
if (!in_array($primary_region, $valid_regions)) {
    $errors[] = 'Invalid region selected';
}

if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = $errors[0];
} else if (tourlink_email_exists_ctr($email)) {
    $response['status'] = 'error';
    $response['message'] = 'Email already exists. Please login or use a different email.';
} else {
    // Register the user first
    $user_id = register_tourlink_user_ctr($email, $password, $user_type, $first_name, $last_name, $phone);

    if ($user_id) {
        // Create provider profile
        $provider_class = new ServiceProvider();

        $provider_id = $provider_class->create_provider(
            $user_id,
            $business_name,
            null, // business_description not used
            $business_location,
            $primary_region
        );

        if ($provider_id) {
            // Auto-login the provider
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = 'provider';
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            $_SESSION['provider_id'] = $provider_id;

            $response['status'] = 'success';
            $response['message'] = 'Provider registration successful! Welcome to TourLink.';
            $response['user_id'] = $user_id;
            $response['provider_id'] = $provider_id;
            $response['redirect'] = '../admin/provider_dashboard.php';
        } else {
            // Rollback - delete user if provider creation failed
            $response['status'] = 'error';
            $response['message'] = 'Failed to create provider profile. Please try again.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to register. Please try again.';
    }
}

echo json_encode($response);
?>
