<?php
/**
 * Change Password Action
 * Handles password change requests
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
$current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

// Validate inputs
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $response['message'] = 'All fields are required';
    echo json_encode($response);
    exit;
}

if ($new_password !== $confirm_password) {
    $response['message'] = 'New passwords do not match';
    echo json_encode($response);
    exit;
}

if (strlen($new_password) < 8) {
    $response['message'] = 'New password must be at least 8 characters';
    echo json_encode($response);
    exit;
}

$user_class = new TourlinkUser();

// Get current user with password (need raw query)
require_once '../settings/db_class.php';
$db = new db_connection();
$db->db_connect();
$stmt = $db->db->prepare("SELECT password_hash FROM tl_users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    $response['message'] = 'User not found';
    echo json_encode($response);
    exit;
}

// Verify current password
if (!password_verify($current_password, $result['password_hash'])) {
    $response['message'] = 'Current password is incorrect';
    echo json_encode($response);
    exit;
}

// Update password (method already hashes)
if ($user_class->update_password($_SESSION['user_id'], $new_password)) {
    $response['status'] = 'success';
    $response['message'] = 'Password changed successfully';
} else {
    $response['message'] = 'Failed to update password';
}

echo json_encode($response);
exit;
?>
