<?php
/**
 * Initiate Premium Subscription Payment via Paystack
 * Monthly subscription: GH₵150
 */

session_start();
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

// Create subscription record
$db = new db_connection();
if (!$db->db_connect()) {
    die('Error: Could not connect to database');
}

// Check if already has active subscription
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

if (!$stmt) {
    die('Error: Could not prepare premium listing insert - ' . $db->db->error);
}

$stmt->bind_param("isssds", $provider_id, $start_date, $end_date, $next_billing_date, $amount, $payment_reference);

if ($stmt->execute()) {
    $premium_listing_id = $db->db->insert_id;

    if (!$premium_listing_id) {
        die('Error: Could not get premium listing ID');
    }

    // Create subscription payment record
    $billing_period_start = $start_date;
    $billing_period_end = $end_date;

    $payment_stmt = $db->db->prepare("
        INSERT INTO tl_subscription_payments
        (premium_listing_id, provider_id, subscription_tier, amount, billing_period_start, billing_period_end, payment_status, transaction_reference)
        VALUES (?, ?, 'Premium', ?, ?, ?, 'pending', ?)
    ");

    if (!$payment_stmt) {
        die('Error: Could not prepare subscription payment insert - ' . $db->db->error);
    }

    $payment_stmt->bind_param("iidsss", $premium_listing_id, $provider_id, $amount, $billing_period_start, $billing_period_end, $payment_reference);

    if (!$payment_stmt->execute()) {
        die('Error: Could not create subscription payment - ' . $payment_stmt->error);
    }

    $subscription_payment_id = $db->db->insert_id;

    if (!$subscription_payment_id) {
        die('Error: Could not get subscription payment ID');
    }

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

    // Initialize Paystack transaction
    echo "Initializing Paystack payment...<br>";
    echo "Amount: " . $amount . "<br>";
    echo "Email: " . $provider_email . "<br>";
    echo "Reference: " . $payment_reference . "<br>";

    $paystack_response = paystack_initialize_transaction($amount, $provider_email, $payment_reference, $metadata);

    echo "Paystack response: <pre>" . print_r($paystack_response, true) . "</pre>";

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
} else {
    error_log("Failed to create premium listing for provider $provider_id: " . $stmt->error);
    die('Error: Failed to create premium listing - ' . $stmt->error);
}
?>
