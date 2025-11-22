<?php
/**
 * Delete Account Action
 * Permanently deletes user account with password verification
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

// Get password
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($password)) {
    $response['message'] = 'Password is required';
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

// Delete the user account
if ($user_class->delete_user($_SESSION['user_id'])) {
    // Destroy session
    session_destroy();

    $response['status'] = 'success';
    $response['message'] = 'Account deleted successfully';
} else {
    $response['message'] = 'Failed to delete account. Please try again.';
}

echo json_encode($response);
exit;
?>
