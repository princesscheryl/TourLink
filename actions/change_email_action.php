<?php
/**
 * Change Email Action
 * Handles email change requests with password verification
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
$new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate inputs
if (empty($new_email) || empty($password)) {
    $response['message'] = 'All fields are required';
    echo json_encode($response);
    exit;
}

if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address';
    echo json_encode($response);
    exit;
}

$user_class = new TourlinkUser();

// Get current user
$user = $user_class->get_user_by_id($_SESSION['user_id']);
if (!$user) {
    $response['message'] = 'User not found';
    echo json_encode($response);
    exit;
}

// Get password hash for verification
require_once '../settings/db_class.php';
$db = new db_connection();
$db->db_connect();
$stmt = $db->db->prepare("SELECT password_hash FROM tl_users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pwd_result = $stmt->get_result()->fetch_assoc();

// Verify password
if (!password_verify($password, $pwd_result['password_hash'])) {
    $response['message'] = 'Password is incorrect';
    echo json_encode($response);
    exit;
}

// Check if email is the same
if (strtolower($new_email) === strtolower($user['email'])) {
    $response['message'] = 'New email is the same as current email';
    echo json_encode($response);
    exit;
}

// Check if email already exists
if ($user_class->email_exists($new_email)) {
    $response['message'] = 'This email is already in use';
    echo json_encode($response);
    exit;
}

// Update email
if ($user_class->update_email($_SESSION['user_id'], $new_email)) {
    $response['status'] = 'success';
    $response['message'] = 'Email updated successfully';
} else {
    $response['message'] = 'Failed to update email';
}

echo json_encode($response);
exit;
?>
