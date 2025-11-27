<?php
/**
 * Initiate Premium Subscription Payment via Paystack
 * Monthly subscription: GH₵150
 */

session_start();
require_once '../settings/core.php';
require_once '../settings/paystack_config.php';

// Check if provider is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header('Location: ../login/login.php');
    exit();
}

$provider_id = $_SESSION['provider_id'];
$user_id = $_SESSION['user_id'];
$provider_email = $_SESSION['email'];

// Create subscription record
$db = new db_connection();
$db->db_connect();

// Check if already has active subscription
$check = $db->db->prepare("
    SELECT premium_listing_id FROM tl_premium_listings
    WHERE provider_id = ?
    AND status = 'active'
    AND end_date >= CURDATE()
");
$check->bind_param("i", $provider_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header('Location: ../admin/premium_subscription.php?error=already_subscribed');
    exit();
}

// Create new premium listing record
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+30 days'));
$next_billing_date = $end_date;
$amount = 150.00;
$payment_reference = 'PREM-' . date('YmdHis') . '-' . $provider_id;

$stmt = $db->db->prepare("
    INSERT INTO tl_premium_listings
    (provider_id, start_date, end_date, next_billing_date, amount_paid, status, auto_renew, is_subscription, payment_reference)
    VALUES (?, ?, ?, ?, ?, 'pending', 1, 1, ?)
");

$stmt->bind_param("isssds", $provider_id, $start_date, $end_date, $next_billing_date, $amount, $payment_reference);

if ($stmt->execute()) {
    $premium_listing_id = $db->db->insert_id;

    // Create subscription payment record
    $billing_period_start = $start_date;
    $billing_period_end = $end_date;

    $payment_stmt = $db->db->prepare("
        INSERT INTO tl_subscription_payments
        (premium_listing_id, provider_id, subscription_tier, amount, billing_period_start, billing_period_end, payment_status, transaction_reference)
        VALUES (?, ?, 'Premium', ?, ?, ?, 'pending', ?)
    ");

    $payment_stmt->bind_param("iidsss", $premium_listing_id, $provider_id, $amount, $billing_period_start, $billing_period_end, $payment_reference);
    $payment_stmt->execute();
    $subscription_payment_id = $db->db->insert_id;

    // Store premium subscription details in session for verification
    $_SESSION['pending_premium_subscription'] = [
        'premium_listing_id' => $premium_listing_id,
        'subscription_payment_id' => $subscription_payment_id,
        'provider_id' => $provider_id,
        'amount' => $amount,
        'reference' => $payment_reference,
        'timestamp' => time()
    ];

    // Initialize Paystack transaction
    $metadata = [
        'payment_type' => 'premium_subscription',
        'provider_id' => $provider_id,
        'premium_listing_id' => $premium_listing_id,
        'subscription_payment_id' => $subscription_payment_id
    ];

    $paystack_response = paystack_initialize_transaction($amount, $provider_email, $payment_reference, $metadata);

    if ($paystack_response && isset($paystack_response['status']) && $paystack_response['status'] === true) {
        // Redirect to Paystack payment gateway
        header('Location: ' . $paystack_response['data']['authorization_url']);
        exit();
    } else {
        // Paystack initialization failed
        error_log("Paystack initialization failed for provider $provider_id: " . json_encode($paystack_response));
        header('Location: ../admin/premium_subscription.php?error=payment_init_failed');
        exit();
    }
} else {
    error_log("Failed to create premium listing for provider $provider_id: " . $stmt->error);
    header('Location: ../admin/premium_subscription.php?error=failed');
    exit();
}
?>
