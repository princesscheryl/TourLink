<?php
/**
 * Update User Preferences Action
 * Handles saving user preferences (language, country, notifications)
 */

session_start();
header('Content-Type: application/json');

require_once '../classes/tourlink_user_class.php';

$response = ['status' => 'error', 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in';
    echo json_encode($response);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get form data
$preferred_language = isset($_POST['preferred_language']) ? trim($_POST['preferred_language']) : 'en';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';

// Validate language
$valid_languages = ['en', 'fr', 'es', 'tw', 'ga', 'de', 'pt'];
if (!in_array($preferred_language, $valid_languages)) {
    $preferred_language = 'en';
}

$user_class = new TourlinkUser();

// Update preferences
if ($user_class->update_preferences($_SESSION['user_id'], $preferred_language, $country)) {
    // Update session if needed
    $_SESSION['preferred_language'] = $preferred_language;

    $response['status'] = 'success';
    $response['message'] = 'Preferences saved successfully';
} else {
    $response['message'] = 'Failed to save preferences';
}

echo json_encode($response);
exit;
?>
