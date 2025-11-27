<?php
/**
 * Cancel Premium Subscription
 * Disables auto-renewal
 */

header('Content-Type: application/json');
session_start();
require_once '../settings/core.php';

$response = ['success' => false, 'message' => ''];

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    $response['message'] = 'Unauthorized';
    echo json_encode($response);
    exit();
}

$provider_id = $_SESSION['provider_id'];
$db = new db_connection();
$db->db_connect();

// Find active subscription
$stmt = $db->db->prepare("
    SELECT premium_listing_id FROM tl_premium_listings
    WHERE provider_id = ?
    AND status = 'active'
    AND end_date >= CURDATE()
    ORDER BY end_date DESC
    LIMIT 1
");

$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    $response['message'] = 'No active subscription found';
    echo json_encode($response);
    exit();
}

$premium_listing_id = $result['premium_listing_id'];

// Cancel subscription (disable auto-renewal)
$update = $db->db->prepare("
    UPDATE tl_premium_listings
    SET auto_renew = 0,
        cancelled_at = NOW()
    WHERE premium_listing_id = ?
");

$update->bind_param("i", $premium_listing_id);

if ($update->execute()) {
    error_log("Premium subscription cancelled: Provider=$provider_id, Listing=$premium_listing_id");
    $response['success'] = true;
    $response['message'] = 'Subscription cancelled. Your benefits will continue until the end of the current billing period.';
} else {
    $response['message'] = 'Failed to cancel subscription';
}

echo json_encode($response);
?>
