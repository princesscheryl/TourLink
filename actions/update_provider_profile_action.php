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

require_once '../classes/service_provider_class.php';
require_once '../classes/tourlink_user_class.php';

// Get provider info
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    $response['status'] = 'error';
    $response['message'] = 'Provider profile not found';
    echo json_encode($response);
    exit();
}

// Get form data
$business_name = isset($_POST['business_name']) ? trim($_POST['business_name']) : '';
$business_registration_number = isset($_POST['business_registration_number']) ? trim($_POST['business_registration_number']) : null;
$region = isset($_POST['region']) ? trim($_POST['region']) : '';
$location_details = isset($_POST['location_details']) ? trim($_POST['location_details']) : null;
$years_of_experience = isset($_POST['years_of_experience']) && $_POST['years_of_experience'] !== '' ? (int)$_POST['years_of_experience'] : null;
$languages_spoken = isset($_POST['languages_spoken']) ? trim($_POST['languages_spoken']) : null;
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

$errors = array();

// Validate required fields
if (empty($business_name)) {
    $errors[] = 'Business name is required';
}
if (empty($region)) {
    $errors[] = 'Primary region is required';
}
if (empty($first_name)) {
    $errors[] = 'First name is required';
}
if (empty($last_name)) {
    $errors[] = 'Last name is required';
}
if (empty($phone)) {
    $errors[] = 'Phone number is required';
}

// Validate region
$valid_regions = ['Greater Accra', 'Central', 'Ashanti', 'Northern'];
if (!in_array($region, $valid_regions)) {
    $errors[] = 'Invalid region selected';
}

if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = $errors[0];
    echo json_encode($response);
    exit();
}

// Update provider profile
$provider_data = array(
    'business_name' => $business_name,
    'region' => $region
);

if ($business_registration_number !== null && $business_registration_number !== '') {
    $provider_data['business_registration_number'] = $business_registration_number;
}
if ($location_details !== null && $location_details !== '') {
    $provider_data['location_details'] = $location_details;
}
if ($years_of_experience !== null) {
    $provider_data['years_of_experience'] = $years_of_experience;
}
if ($languages_spoken !== null && $languages_spoken !== '') {
    $provider_data['languages_spoken'] = $languages_spoken;
}

$provider_updated = $provider_class->update_provider($provider['provider_id'], $provider_data);

// Update user info (first name, last name, phone)
$user_class = new TourlinkUser();
$user_data = array(
    'first_name' => $first_name,
    'last_name' => $last_name,
    'phone' => $phone
);
$user_updated = $user_class->update_user($_SESSION['user_id'], $user_data);

if ($provider_updated && $user_updated) {
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
