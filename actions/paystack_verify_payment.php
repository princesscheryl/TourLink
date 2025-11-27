<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Set up global error handler to catch all errors and return as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile:$errline");
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'status' => 'error',
        'message' => "Server error: $errstr",
        'debug' => [
            'file' => basename($errfile),
            'line' => $errline
        ]
    ]);
    exit();
});

header('Content-Type: application/json');

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once '../settings/core.php';
    require_once '../settings/paystack_config.php';
    require_once '../controllers/booking_controller.php';
    require_once '../controllers/discount_controller.php';
    require_once '../settings/db_class.php';
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit();
}

// Get verification data
$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : null;

if (!$reference) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No payment reference provided'
    ]);
    exit();
}

// Verify reference matches session
if (!isset($_SESSION['paystack_ref']) || $_SESSION['paystack_ref'] !== $reference) {
    error_log("Reference mismatch - Expected: " . ($_SESSION['paystack_ref'] ?? 'none') . ", Got: $reference");
}

try {
    $user_id = $_SESSION['user_id'];

    // Determine payment type - check if this is a premium subscription or booking
    $is_premium_subscription = isset($_SESSION['pending_premium_subscription']);
    $is_new_booking = isset($_SESSION['pending_booking']);  // New Paystack flow
    $is_old_booking = isset($_SESSION['paystack_booking_id']);  // Old flow

    if ($is_premium_subscription) {
        // Handle premium subscription payment
        process_premium_subscription_verification($reference);
        exit();
    }

    if ($is_new_booking) {
        // Handle new Paystack booking flow (creates booking after payment)
        process_new_booking_verification($reference);
        exit();
    }

    if (!$is_old_booking) {
        throw new Exception('Payment information not found in session');
    }

    // Continue with booking payment processing
    $booking_id = $_SESSION['paystack_booking_id'];
    $expected_amount = $_SESSION['paystack_amount'] ?? 0;
    $discount_code = $_SESSION['paystack_discount_code'] ?? null;
    $discount_id = $_SESSION['paystack_discount_id'] ?? null;
    $discount_amount = $_SESSION['paystack_discount_amount'] ?? 0;

    // Verify transaction with Paystack
    $verification_response = paystack_verify_transaction($reference);

    if (!$verification_response || !isset($verification_response['status'])) {
        throw new Exception('No response from payment gateway');
    }

    if ($verification_response['status'] !== true) {
        $error_msg = $verification_response['message'] ?? 'Payment verification failed';
        throw new Exception($error_msg);
    }

    // Extract transaction data
    $transaction_data = $verification_response['data'] ?? [];
    $payment_status = $transaction_data['status'] ?? null;
    $amount_paid = isset($transaction_data['amount']) ? $transaction_data['amount'] / 100 : 0;
    $customer_email = $transaction_data['customer']['email'] ?? '';
    $authorization = $transaction_data['authorization'] ?? [];
    $authorization_code = $authorization['authorization_code'] ?? '';
    $payment_channel = $authorization['channel'] ?? 'card';

    // Validate payment status
    if ($payment_status !== 'success') {
        throw new Exception('Payment was not successful. Status: ' . ucfirst($payment_status));
    }

    // Verify amount matches (with 1 pesewa tolerance)
    if (abs($amount_paid - $expected_amount) > 0.01) {
        error_log("Amount mismatch - Expected: GHS $expected_amount, Paid: GHS $amount_paid");
        throw new Exception('Payment amount mismatch');
    }

    // Get booking details
    $booking = get_booking_by_id_ctr($booking_id);

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    // Begin database transaction
    $db = new db_connection();
    $conn = $db->db_conn();
    mysqli_begin_transaction($conn);

    try {
        // Update booking payment status
        $update_booking_sql = "UPDATE tl_bookings SET
                               payment_status = 'escrow',
                               discount_amount = ?,
                               total_amount = ?
                               WHERE booking_id = ?";
        $stmt = $conn->prepare($update_booking_sql);
        $final_amount = $amount_paid;
        $stmt->bind_param("ddi", $discount_amount, $final_amount, $booking_id);

        if (!$stmt->execute()) {
            throw new Exception('Failed to update booking status');
        }

        // Record payment
        $payment_sql = "INSERT INTO tl_payments
                       (booking_id, tourist_id, amount, currency, payment_method,
                        transaction_ref, authorization_code, payment_channel,
                        discount_code, discount_amount, payment_date)
                       VALUES (?, ?, ?, 'GHS', 'paystack', ?, ?, ?, ?, ?, NOW())";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param(
            "iidsssd",
            $booking_id,
            $user_id,
            $amount_paid,
            $reference,
            $authorization_code,
            $payment_channel,
            $discount_code,
            $discount_amount
        );

        if (!$payment_stmt->execute()) {
            throw new Exception('Failed to record payment');
        }

        $payment_id = $conn->insert_id;

        // Record discount usage if discount was applied
        if ($discount_id && $discount_amount > 0) {
            $record_result = record_discount_usage_ctr($discount_id, $user_id, $booking_id, $discount_amount);
            if (!$record_result) {
                error_log("Warning: Failed to record discount usage for discount_id: $discount_id");
            }
        }

        // Commit transaction
        mysqli_commit($conn);
        error_log("Payment verified and recorded - Booking: $booking_id, Payment: $payment_id, Reference: $reference");

        // Clear session payment data
        unset($_SESSION['paystack_ref']);
        unset($_SESSION['paystack_booking_id']);
        unset($_SESSION['paystack_amount']);
        unset($_SESSION['paystack_discount_code']);
        unset($_SESSION['paystack_discount_id']);
        unset($_SESSION['paystack_discount_amount']);
        unset($_SESSION['paystack_timestamp']);

        // Return success response
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'message' => 'Payment successful! Your booking is confirmed.',
            'booking_id' => $booking_id,
            'booking_reference' => $booking['booking_reference'],
            'service_title' => $booking['service_title'],
            'booking_date' => date('F j, Y', strtotime($booking['booking_date'])),
            'amount_paid' => number_format($amount_paid, 2),
            'discount_applied' => $discount_amount > 0,
            'discount_amount' => number_format($discount_amount, 2),
            'original_amount' => number_format($booking['total_amount'], 2),
            'currency' => 'GHS',
            'payment_reference' => $reference,
            'payment_method' => ucfirst($payment_channel),
            'customer_email' => $customer_email
        ]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Database error during payment verification: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("Payment verification error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Process new booking payment verification (Paystack flow)
 * Creates booking after successful payment
 */
function process_new_booking_verification($reference) {
    // Get booking data from session
    if (!isset($_SESSION['pending_booking'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Booking data not found in session'
        ]);
        return;
    }

    $booking_data = $_SESSION['pending_booking'];

    // Verify transaction with Paystack
    $verification_response = paystack_verify_transaction($reference);

    if (!$verification_response || !isset($verification_response['status'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No response from payment gateway'
        ]);
        return;
    }

    if ($verification_response['status'] !== true) {
        $error_msg = $verification_response['message'] ?? 'Payment verification failed';
        echo json_encode([
            'status' => 'error',
            'message' => $error_msg
        ]);
        return;
    }

    // Extract transaction data
    $transaction_data = $verification_response['data'] ?? [];
    $payment_status = $transaction_data['status'] ?? null;
    $amount_paid = isset($transaction_data['amount']) ? $transaction_data['amount'] / 100 : 0;
    $customer_email = $transaction_data['customer']['email'] ?? '';
    $authorization = $transaction_data['authorization'] ?? [];
    $authorization_code = $authorization['authorization_code'] ?? '';
    $payment_channel = $authorization['channel'] ?? 'card';

    // Validate payment status
    if ($payment_status !== 'success') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment was not successful. Status: ' . ucfirst($payment_status)
        ]);
        return;
    }

    // Verify amount matches
    if (abs($amount_paid - $booking_data['total_amount']) > 0.01) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment amount does not match booking total'
        ]);
        return;
    }

    // Begin database transaction
    $db = new db_connection();
    $conn = $db->db_conn();
    mysqli_begin_transaction($conn);

    try {
        // Generate booking reference
        $booking_reference = 'TLBK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Create booking in database
        require_once '../controllers/booking_controller.php';

        $booking_id = create_booking_ctr(
            $booking_data['service_id'],
            $_SESSION['user_id'],
            $booking_data['service_date'],
            $booking_data['service_time'],
            $booking_data['number_of_people'],
            $booking_data['total_amount'],
            'pending',  // booking_status - awaiting provider confirmation
            'paid',     // payment_status
            $booking_reference,
            $booking_data['discount_code'],
            json_encode($booking_data['guest_details'])
        );

        if (!$booking_id) {
            throw new Exception("Failed to create booking");
        }

        error_log("Booking created - ID: $booking_id, Reference: $booking_reference");

        // Record payment
        $payment_sql = "INSERT INTO tl_payments
                       (booking_id, tourist_id, amount, currency, payment_method,
                        transaction_ref, authorization_code, payment_channel, payment_date)
                       VALUES (?, ?, ?, 'GHS', 'paystack', ?, ?, ?, NOW())";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param(
            "iidsss",
            $booking_id,
            $_SESSION['user_id'],
            $amount_paid,
            $reference,
            $authorization_code,
            $payment_channel
        );

        if (!$payment_stmt->execute()) {
            throw new Exception("Failed to record payment");
        }

        $payment_id = $conn->insert_id;
        error_log("Payment recorded - ID: $payment_id");

        // Commit transaction
        mysqli_commit($conn);

        // Clear session booking data
        unset($_SESSION['pending_booking']);

        // Return success
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'payment_type' => 'booking',
            'message' => 'Payment successful! Your booking is pending provider confirmation.',
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference,
            'amount' => number_format($booking_data['total_amount'], 2),
            'payment_reference' => $reference
        ]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Database error during booking verification: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Process premium subscription payment verification
 */
function process_premium_subscription_verification($reference) {
    error_log("Starting premium subscription verification for reference: $reference");

    // Get subscription data from session
    if (!isset($_SESSION['pending_premium_subscription'])) {
        error_log("Premium verification failed: No pending subscription in session");
        echo json_encode([
            'status' => 'error',
            'message' => 'Subscription data not found in session'
        ]);
        return;
    }

    $subscription_data = $_SESSION['pending_premium_subscription'];
    error_log("Subscription data found: " . json_encode($subscription_data));

    // Verify transaction with Paystack
    error_log("Calling Paystack verification API...");
    $verification_response = paystack_verify_transaction($reference);
    error_log("Paystack response: " . json_encode($verification_response));

    if (!$verification_response || !isset($verification_response['status'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No response from payment gateway'
        ]);
        return;
    }

    if ($verification_response['status'] !== true) {
        $error_msg = $verification_response['message'] ?? 'Payment verification failed';
        echo json_encode([
            'status' => 'error',
            'message' => $error_msg
        ]);
        return;
    }

    // Extract transaction data
    $transaction_data = $verification_response['data'] ?? [];
    $payment_status = $transaction_data['status'] ?? null;
    $amount_paid = isset($transaction_data['amount']) ? $transaction_data['amount'] / 100 : 0;

    // Validate payment status
    if ($payment_status !== 'success') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment was not successful. Status: ' . ucfirst($payment_status)
        ]);
        return;
    }

    // Verify amount matches
    if (abs($amount_paid - $subscription_data['amount']) > 0.01) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment amount does not match subscription cost'
        ]);
        return;
    }

    // Begin database transaction
    error_log("Starting database transaction for premium subscription");
    $db = new db_connection();
    $conn = $db->db_conn();

    if (!$conn) {
        error_log("Database connection failed");
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        return;
    }

    mysqli_begin_transaction($conn);

    try {
        $provider_id = $subscription_data['provider_id'];
        $start_date = $subscription_data['start_date'];
        $end_date = $subscription_data['end_date'];
        $next_billing_date = $end_date;
        $subscription_amount = $subscription_data['amount']; // 150.00

        error_log("Inserting premium listing for provider $provider_id");

        // Create premium listing record (using only base columns)
        $insert_listing = $conn->prepare("
            INSERT INTO tl_premium_listings
            (provider_id, start_date, end_date, amount_paid, status, auto_renew, payment_reference)
            VALUES (?, ?, ?, ?, 'active', 1, ?)
        ");

        if (!$insert_listing) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Failed to prepare premium listing insert: " . $conn->error);
        }

        $insert_listing->bind_param("issds", $provider_id, $start_date, $end_date, $subscription_amount, $reference);

        if (!$insert_listing->execute()) {
            error_log("Execute failed: " . $insert_listing->error);
            throw new Exception("Failed to create premium listing: " . $insert_listing->error);
        }

        $premium_listing_id = $conn->insert_id;
        error_log("Premium listing created with ID: $premium_listing_id");

        // Create subscription payment record (without premium_listing_id if column doesn't exist)
        // Check if premium_listing_id column exists
        $check_col = $conn->query("SHOW COLUMNS FROM tl_subscription_payments LIKE 'premium_listing_id'");

        if ($check_col->num_rows > 0) {
            // Column exists - use it
            $insert_payment = $conn->prepare("
                INSERT INTO tl_subscription_payments
                (premium_listing_id, provider_id, subscription_tier, amount, billing_period_start, billing_period_end, payment_status, payment_date, transaction_reference)
                VALUES (?, ?, 'Premium', ?, ?, ?, 'paid', NOW(), ?)
            ");
            $insert_payment->bind_param("iidsss", $premium_listing_id, $provider_id, $subscription_amount, $start_date, $end_date, $reference);
        } else {
            // Column doesn't exist - skip it
            $insert_payment = $conn->prepare("
                INSERT INTO tl_subscription_payments
                (provider_id, subscription_tier, amount, billing_period_start, billing_period_end, payment_status, payment_date, transaction_reference)
                VALUES (?, 'Premium', ?, ?, ?, 'paid', NOW(), ?)
            ");
            $insert_payment->bind_param("idsss", $provider_id, $subscription_amount, $start_date, $end_date, $reference);
        }

        if (!$insert_payment->execute()) {
            throw new Exception("Failed to create subscription payment record");
        }

        // Mark all provider's services as premium (if column exists)
        $check_premium_col = $conn->query("SHOW COLUMNS FROM tl_services LIKE 'is_premium'");
        if ($check_premium_col->num_rows > 0) {
            $update_services = $conn->prepare("
                UPDATE tl_services
                SET is_premium = 1
                WHERE provider_id = ?
            ");
            $update_services->bind_param("i", $provider_id);
            $update_services->execute();
        }

        error_log("Premium subscription activated - Provider: $provider_id, Listing: $premium_listing_id");

        // Commit transaction
        mysqli_commit($conn);

        // Clear session subscription data
        unset($_SESSION['pending_premium_subscription']);

        // Return success
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'payment_type' => 'premium_subscription',
            'message' => 'Premium subscription activated! Your services are now featured.',
            'premium_listing_id' => $premium_listing_id,
            'amount' => number_format($subscription_data['amount'], 2),
            'payment_reference' => $reference
        ]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Database error during premium subscription verification: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?>
