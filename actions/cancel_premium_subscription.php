<?php
/**
 * Cancel Premium Subscription
 * Disables auto-renewal
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, but log them
ini_set('log_errors', 1);

// Start output buffering to prevent any unwanted output
ob_start();

// Set JSON header early
header('Content-Type: application/json');

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'A fatal error occurred: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'],
            'error' => $error['message']
        ]);
        exit();
    }
});

session_start();
require_once '../settings/core.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check authentication
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
        $response['message'] = 'Unauthorized. Please log in as a provider.';
        ob_end_clean();
        echo json_encode($response);
        exit();
    }

    if (!isset($_SESSION['provider_id'])) {
        $response['message'] = 'Provider ID not found in session.';
        ob_end_clean();
        echo json_encode($response);
        exit();
    }

    $provider_id = $_SESSION['provider_id'];
    $db = new db_connection();
    
    if (!$db->db_connect()) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }

    // Verify connection is actually valid
    if (!$db->db || !is_object($db->db)) {
        throw new Exception('Database connection object is invalid');
    }

    // Check if cancelled_at column exists
    $check_col = $db->db->query("SHOW COLUMNS FROM tl_premium_listings LIKE 'cancelled_at'");
    $has_cancelled_at = $check_col && $check_col->num_rows > 0;

    // Find active subscription
    $stmt = $db->db->prepare("
        SELECT premium_listing_id FROM tl_premium_listings
        WHERE provider_id = ?
        AND status = 'active'
        AND end_date >= CURDATE()
        ORDER BY end_date DESC
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $db->db->error);
    }

    $stmt->bind_param("i", $provider_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }

    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        $response['message'] = 'No active subscription found.';
        ob_end_clean();
        echo json_encode($response);
        exit();
    }

    $premium_listing_id = $result['premium_listing_id'];

    // Build UPDATE statement based on available columns
    if ($has_cancelled_at) {
        $update_sql = "
            UPDATE tl_premium_listings
            SET auto_renew = 0,
                cancelled_at = NOW()
            WHERE premium_listing_id = ?
        ";
    } else {
        // Fallback if cancelled_at column doesn't exist
        $update_sql = "
            UPDATE tl_premium_listings
            SET auto_renew = 0
            WHERE premium_listing_id = ?
        ";
    }

    $update = $db->db->prepare($update_sql);

    if (!$update) {
        throw new Exception('Failed to prepare update statement: ' . $db->db->error);
    }

    $update->bind_param("i", $premium_listing_id);

    if (!$update->execute()) {
        throw new Exception('Failed to execute update: ' . $update->error);
    }

    if ($update->affected_rows === 0) {
        $response['message'] = 'No subscription was updated. It may have already been cancelled.';
    } else {
        error_log("Premium subscription cancelled: Provider=$provider_id, Listing=$premium_listing_id");
        $response['success'] = true;
        $response['message'] = 'Subscription cancelled successfully. Your benefits will continue until the end of the current billing period.';
    }

    $update->close();

} catch (Exception $e) {
    error_log("Error cancelling premium subscription: " . $e->getMessage());
    $response['message'] = 'An error occurred while cancelling your subscription. Please try again or contact support.';
    $response['error'] = $e->getMessage(); // Include error in response for debugging
}

// Clean output buffer and send JSON response
ob_end_clean();
echo json_encode($response);
?>
