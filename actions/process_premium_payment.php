<?php
/**
 * Process Premium Subscription Payment
 * Activates premium listing after successful payment
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

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$subscription_id = isset($input['subscription_id']) ? (int)$input['subscription_id'] : 0;
$payment_method = isset($input['payment_method']) ? $input['payment_method'] : '';
$amount = isset($input['amount']) ? floatval($input['amount']) : 0;

if (!$subscription_id || !$payment_method || $amount <= 0) {
    $response['message'] = 'Invalid payment data';
    echo json_encode($response);
    exit();
}

$provider_id = $_SESSION['provider_id'];
$db = new db_connection();
$db->db_connect();

// Begin transaction
mysqli_begin_transaction($db->db);

try {
    // Get subscription details
    $stmt = $db->db->prepare("
        SELECT sp.*, pl.premium_listing_id, pl.provider_id
        FROM tl_subscription_payments sp
        JOIN tl_premium_listings pl ON sp.premium_listing_id = pl.premium_listing_id
        WHERE sp.subscription_payment_id = ?
        AND pl.provider_id = ?
        AND sp.payment_status = 'pending'
        FOR UPDATE
    ");

    $stmt->bind_param("ii", $subscription_id, $provider_id);
    $stmt->execute();
    $subscription = $stmt->get_result()->fetch_assoc();

    if (!$subscription) {
        throw new Exception('Subscription not found or already paid');
    }

    $premium_listing_id = $subscription['premium_listing_id'];
    $transaction_reference = 'PAY-' . date('YmdHis') . '-' . $subscription_id;

    // Update subscription payment status
    $update_payment = $db->db->prepare("
        UPDATE tl_subscription_payments
        SET payment_status = 'paid',
            payment_date = NOW(),
            transaction_reference = ?
        WHERE subscription_payment_id = ?
    ");

    $update_payment->bind_param("si", $transaction_reference, $subscription_id);
    if (!$update_payment->execute()) {
        throw new Exception('Failed to update payment status');
    }

    // Activate premium listing
    $update_listing = $db->db->prepare("
        UPDATE tl_premium_listings
        SET status = 'active',
            payment_reference = ?
        WHERE premium_listing_id = ?
    ");

    $update_listing->bind_param("si", $transaction_reference, $premium_listing_id);
    if (!$update_listing->execute()) {
        throw new Exception('Failed to activate premium listing');
    }

    // Mark all provider's services as premium
    $update_services = $db->db->prepare("
        UPDATE tl_services s
        JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
        SET s.is_premium = 1
        WHERE sp.provider_id = ?
    ");

    $update_services->bind_param("i", $provider_id);
    $update_services->execute();

    // Log the transaction
    error_log("Premium subscription activated: Provider=$provider_id, Subscription=$subscription_id, Amount=$amount");

    // Commit transaction
    mysqli_commit($db->db);

    $response['success'] = true;
    $response['message'] = 'Payment successful! Your premium subscription is now active.';
    $response['transaction_reference'] = $transaction_reference;

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($db->db);
    error_log("Premium payment error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
