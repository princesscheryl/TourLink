<?php
/**
 * Get User Details Endpoint
 * Returns user details as JSON for the admin modal
 */
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

require_once 'includes_platform/auth_check.php';
require_once '../settings/db_class.php';

// Check admin access
require_privilege('view_users');

header('Content-Type: application/json');

// Get user ID from request
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit();
}

// Get user details
$db = new db_connection();
$db->db_connect();

// Fetch user with provider details if applicable
$stmt = $db->db->prepare("
    SELECT u.*, 
           sp.business_name, 
           sp.verification_status, 
           sp.total_earnings,
           sp.region,
           sp.phone as provider_phone,
           sp.business_registration_number,
           sp.years_of_experience,
           sp.languages_spoken
    FROM tl_users u
    LEFT JOIN tl_service_providers sp ON u.user_id = sp.user_id
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    exit();
}

// Clean up any potential output before JSON
ob_clean();

// Return user data
echo json_encode([
    'success' => true,
    'user' => $user
], JSON_PRETTY_PRINT);

