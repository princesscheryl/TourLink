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
$business_description = isset($_POST['business_description']) ? trim($_POST['business_description']) : '';
$business_location = isset($_POST['business_location']) ? trim($_POST['business_location']) : '';
$regions = isset($_POST['regions']) && is_array($_POST['regions']) ? $_POST['regions'] : [];

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
if (empty($business_name) || strlen($business_name) > 100) {
    $errors[] = 'Business name is required and must be less than 100 characters';
}
if (empty($business_description) || strlen($business_description) > 500) {
    $errors[] = 'Business description is required and must be less than 500 characters';
}
if (empty($business_location) || strlen($business_location) > 100) {
    $errors[] = 'Business location is required and must be less than 100 characters';
}
if (empty($regions)) {
    $errors[] = 'Please select at least one region you operate in';
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
        $regions_json = json_encode($regions);

        $provider_id = $provider_class->create_provider(
            $user_id,
            $business_name,
            $business_description,
            $business_location,
            $regions_json
        );

        if ($provider_id) {
            $response['status'] = 'success';
            $response['message'] = 'Provider registration successful! Your account is pending verification and payment of GHS 20 onboarding fee.';
            $response['user_id'] = $user_id;
            $response['provider_id'] = $provider_id;
            // TODO: Redirect to payment page when ready
            $response['redirect'] = '../login/login.php';
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
