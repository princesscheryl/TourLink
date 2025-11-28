<?php
/**
 * Get User Details Endpoint
 * Returns user details as JSON for the admin modal
 */
// Start output buffering early to catch any unwanted output
ob_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Check if session is already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once 'includes_platform/auth_check.php';
    require_once '../settings/db_class.php';

    // Check admin access
    if (!function_exists('require_privilege')) {
        throw new Exception('Privilege functions not loaded');
    }
    require_privilege('view_users');

    // Get user ID from request
    $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$user_id) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        exit();
    }

    // Get user details
    $db = new db_connection();
    if (!$db->db_connect()) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit();
    }

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

    if (!$stmt) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to prepare statement: ' . ($db->db->error ?? 'Unknown error')
        ]);
        exit();
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to execute query: ' . ($stmt->error ?? 'Unknown error')
        ]);
        exit();
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit();
    }

    // Clean up any potential output before JSON
    ob_end_clean();
    header('Content-Type: application/json');

    // Return user data
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);

} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit();
} catch (Error $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit();
}
