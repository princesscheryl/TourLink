<?php
/**
 * Initiate Premium Subscription Payment via Paystack
 * Monthly subscription: GH₵150
 */

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_once '../settings/paystack_config.php';

// Check if provider is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header('Location: ../login/login.php');
    exit();
}

// Check if provider_id exists in session
if (!isset($_SESSION['provider_id'])) {
    die('Error: Provider ID not found in session. Please logout and login again.');
}

$provider_id = $_SESSION['provider_id'];
$user_id = $_SESSION['user_id'];
$provider_email = $_SESSION['email'];

// Check if already has active subscription
$db = new db_connection();
if (!$db->db_connect()) {
    die('Error: Could not connect to database');
}

$check = $db->db->prepare("
    SELECT premium_listing_id FROM tl_premium_listings
    WHERE provider_id = ?
    AND status = 'active'
    AND end_date >= CURDATE()
");

if (!$check) {
    die('Error: Could not prepare statement - ' . $db->db->error);
}

$check->bind_param("i", $provider_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header('Location: ../admin/premium_subscription.php?error=already_subscribed');
    exit();
}

// Generate payment reference
$payment_reference = 'PREM-' . date('YmdHis') . '-' . $provider_id;
$amount = 150.00;
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+30 days'));

// Store subscription details in DATABASE (not just session, because session can be lost)
// Insert into tl_subscription_payments with pending status
$insert_stmt = $db->db->prepare("
    INSERT INTO tl_subscription_payments
    (provider_id, amount, payment_reference, payment_status, subscription_type, start_date, end_date)
    VALUES (?, ?, ?, 'pending', 'premium_monthly', ?, ?)
");

if (!$insert_stmt) {
    die('Error: Could not prepare insert statement - ' . $db->db->error);
}

$insert_stmt->bind_param("idsss", $provider_id, $amount, $payment_reference, $start_date, $end_date);

if (!$insert_stmt->execute()) {
    die('Error: Could not insert pending payment - ' . $insert_stmt->error);
}

// Also store in session as backup (for immediate verification)
$_SESSION['pending_premium_subscription'] = [
    'provider_id' => $provider_id,
    'amount' => $amount,
    'start_date' => $start_date,
    'end_date' => $end_date,
    'reference' => $payment_reference,
    'timestamp' => time()
];

// Initialize Paystack transaction
$metadata = [
    'payment_type' => 'premium_subscription',
    'provider_id' => $provider_id
];

$paystack_response = paystack_initialize_transaction($amount, $provider_email, $payment_reference, $metadata);

if ($paystack_response && isset($paystack_response['status']) && $paystack_response['status'] === true) {
    // Redirect to Paystack payment gateway
    if (isset($paystack_response['data']['authorization_url'])) {
        header('Location: ' . $paystack_response['data']['authorization_url']);
        exit();
    } else {
        die('Error: No authorization URL in Paystack response');
    }
} else {
    // Paystack initialization failed
    $error_message = isset($paystack_response['message']) ? $paystack_response['message'] : 'Unknown error';
    error_log("Paystack initialization failed for provider $provider_id: " . json_encode($paystack_response));
    die('Error: Paystack initialization failed - ' . $error_message);
}
?>
