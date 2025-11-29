<?php
// Start output buffering to prevent any premature output
ob_start();

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors, log them instead
ini_set('display_startup_errors', '0');
ini_set('log_errors', 1);

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'A fatal error occurred: ' . $error['message'],
            'debug' => [
                'file' => basename($error['file']),
                'line' => $error['line'],
                'type' => $error['type']
            ]
        ]);
        exit();
    }
});

// Set up global error handler to catch all errors and return as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile:$errline");
    // Don't output here, let the main code handle it
    return false; // Let PHP continue with normal error handling
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
        'message' => 'Session expired. Please login again.',
        'debug' => [
            'session_id' => session_id(),
            'session_data_present' => !empty($_SESSION),
            'session_keys' => array_keys($_SESSION)
        ]
    ]);
    exit();
}

// Get verification data
$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : null;

if (!$reference) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No payment reference provided',
        'debug' => [
            'input_received' => $input,
            'raw_input' => file_get_contents('php://input')
        ]
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

    // Debug: Log what session data we have
    error_log("Session debugging - User ID: $user_id");
    error_log("Is premium subscription: " . ($is_premium_subscription ? 'YES' : 'NO'));
    error_log("Is new booking: " . ($is_new_booking ? 'YES' : 'NO'));
    error_log("Is old booking: " . ($is_old_booking ? 'YES' : 'NO'));
    error_log("Session keys present: " . implode(', ', array_keys($_SESSION)));

    if ($is_premium_subscription) {
        error_log("Processing premium subscription verification");
        // Handle premium subscription payment
        process_premium_subscription_verification($reference);
        exit();
    }

    if ($is_new_booking) {
        error_log("Processing new booking verification");
        // Handle new Paystack booking flow (creates booking after payment)
        process_new_booking_verification($reference);
        exit();
    }

    if (!$is_old_booking) {
        // Add detailed debugging for missing session data
        echo json_encode([
            'status' => 'error',
            'verified' => false,
            'message' => 'Payment information not found in session. This usually means the session expired or was lost during payment.',
            'debug' => [
                'user_logged_in' => isset($_SESSION['user_id']),
                'session_keys' => array_keys($_SESSION),
                'is_premium' => $is_premium_subscription,
                'is_new_booking' => $is_new_booking,
                'is_old_booking' => $is_old_booking,
                'reference' => $reference,
                'suggestion' => 'The payment may have been successful but session was lost. Contact support with this reference number.'
            ]
        ]);
        exit();
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

        // Record payment - check which columns exist
        $check_cols = $conn->query("SHOW COLUMNS FROM tl_payments");
        $columns = [];
        while ($row = $check_cols->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        // Build INSERT statement based on available columns
        $payment_fields = ['booking_id', 'amount'];
        $payment_values = [$booking_id, $amount_paid];
        $payment_types = "id";
        
        // Add optional columns if they exist
        if (in_array('transaction_reference', $columns)) {
            $payment_fields[] = 'transaction_reference';
            $payment_values[] = $reference;
            $payment_types .= "s";
        } elseif (in_array('transaction_ref', $columns)) {
            $payment_fields[] = 'transaction_ref';
            $payment_values[] = $reference;
            $payment_types .= "s";
        }
        
        if (in_array('payment_method', $columns)) {
            $payment_fields[] = 'payment_method';
            $payment_values[] = 'card'; // Paystack default
            $payment_types .= "s";
        }
        
        if (in_array('payment_provider', $columns)) {
            $payment_fields[] = 'payment_provider';
            $payment_values[] = 'Paystack';
            $payment_types .= "s";
        }
        
        if (in_array('payment_status', $columns)) {
            $payment_fields[] = 'payment_status';
            $payment_values[] = 'successful';
            $payment_types .= "s";
        }
        
        // Store metadata if column exists
        if (in_array('transaction_metadata', $columns)) {
            $metadata = json_encode([
                'authorization_code' => $authorization_code,
                'payment_channel' => $payment_channel,
                'customer_email' => $customer_email,
                'discount_code' => $discount_code,
                'discount_amount' => $discount_amount
            ]);
            $payment_fields[] = 'transaction_metadata';
            $payment_values[] = $metadata;
            $payment_types .= "s";
        }
        
        $fields_str = implode(', ', $payment_fields);
        $placeholders = implode(', ', array_fill(0, count($payment_fields), '?'));
        
        $payment_sql = "INSERT INTO tl_payments ($fields_str) VALUES ($placeholders)";
        $payment_stmt = $conn->prepare($payment_sql);
        
        if (!$payment_stmt) {
            throw new Exception("Failed to prepare payment statement: " . $conn->error);
        }
        
        $payment_stmt->bind_param($payment_types, ...$payment_values);

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
    ob_end_clean(); // Clear any output buffer
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
    exit();
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
    if (!$db->db_connect()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed'
        ]);
        return;
    }
    
    $conn = $db->db;
    if (!$conn) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection object is invalid'
        ]);
        return;
    }
    
    mysqli_begin_transaction($conn);

    try {
        // Generate booking reference
        $booking_reference = 'TLBK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Create booking in database
        require_once '../controllers/booking_controller.php';
        
        // Check if function exists
        if (!function_exists('create_booking_ctr')) {
            throw new Exception("Booking controller function not found. Please check if booking_controller.php is properly loaded.");
        }

        // Get service details to get provider_id and calculate amounts
        require_once '../controllers/service_controller.php';
        $service = get_service_by_id_ctr($booking_data['service_id']);
        if (!$service) {
            throw new Exception("Service not found");
        }
        
        $provider_id = $service['provider_id'];
        $original_amount = $booking_data['total_amount'];
        $discount_amount = isset($booking_data['discount_amount']) ? $booking_data['discount_amount'] : 0;
        $commission_rate = 0.15; // 15% commission
        $commission_amount = $original_amount * $commission_rate;
        $provider_earnings = $original_amount - $commission_amount;
        
        // Prepare booking data array for the controller
        $booking_data_array = [
            'service_id' => $booking_data['service_id'],
            'tourist_id' => $_SESSION['user_id'],
            'provider_id' => $provider_id,
            'service_date' => $booking_data['service_date'],
            'service_time' => $booking_data['service_time'],
            'number_of_people' => $booking_data['number_of_people'],
            'service_duration' => $booking_data['service_duration'] ?? 1,
            'original_amount' => $original_amount,
            'discount_amount' => $discount_amount,
            'total_amount' => $original_amount - $discount_amount,
            'commission_amount' => $commission_amount,
            'provider_earnings' => $provider_earnings,
            'special_requests' => isset($booking_data['guest_details']) ? (is_string($booking_data['guest_details']) ? $booking_data['guest_details'] : json_encode($booking_data['guest_details'])) : null,
            'guest_details' => isset($booking_data['guest_details']) ? (is_string($booking_data['guest_details']) ? $booking_data['guest_details'] : json_encode($booking_data['guest_details'])) : null
        ];

        $booking_id = create_booking_ctr($booking_data_array);

        if (!$booking_id) {
            throw new Exception("Failed to create booking. The booking controller returned false or null.");
        }

        error_log("Booking created - ID: $booking_id, Reference: $booking_reference");

        // Record payment
        // Check which columns exist in tl_payments table
        $check_cols = $conn->query("SHOW COLUMNS FROM tl_payments");
        $columns = [];
        while ($row = $check_cols->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        // Build INSERT statement based on available columns
        $payment_fields = ['booking_id', 'amount'];
        $payment_values = [$booking_id, $amount_paid];
        $payment_types = "id";
        
        // Add optional columns if they exist (check for both possible column names)
        if (in_array('transaction_reference', $columns)) {
            $payment_fields[] = 'transaction_reference';
            $payment_values[] = $reference;
            $payment_types .= "s";
        } elseif (in_array('transaction_ref', $columns)) {
            $payment_fields[] = 'transaction_ref';
            $payment_values[] = $reference;
            $payment_types .= "s";
        }
        
        if (in_array('payment_method', $columns)) {
            $payment_fields[] = 'payment_method';
            $payment_values[] = 'card'; // Paystack default
            $payment_types .= "s";
        }
        
        if (in_array('payment_provider', $columns)) {
            $payment_fields[] = 'payment_provider';
            $payment_values[] = 'Paystack';
            $payment_types .= "s";
        }
        
        if (in_array('authorization_code', $columns)) {
            $payment_fields[] = 'authorization_code';
            $payment_values[] = $authorization_code;
            $payment_types .= "s";
        }
        
        if (in_array('payment_channel', $columns)) {
            $payment_fields[] = 'payment_channel';
            $payment_values[] = $payment_channel;
            $payment_types .= "s";
        }
        
        if (in_array('payment_status', $columns)) {
            $payment_fields[] = 'payment_status';
            $payment_values[] = 'successful';
            $payment_types .= "s";
        }
        
        // Store payment metadata if column exists
        if (in_array('transaction_metadata', $columns)) {
            $metadata = json_encode([
                'authorization_code' => $authorization_code,
                'payment_channel' => $payment_channel,
                'customer_email' => $customer_email
            ]);
            $payment_fields[] = 'transaction_metadata';
            $payment_values[] = $metadata;
            $payment_types .= "s";
        }
        
        $fields_str = implode(', ', $payment_fields);
        $placeholders = implode(', ', array_fill(0, count($payment_fields), '?'));
        
        $payment_sql = "INSERT INTO tl_payments ($fields_str) VALUES ($placeholders)";
        $payment_stmt = $conn->prepare($payment_sql);
        
        if (!$payment_stmt) {
            throw new Exception("Failed to prepare payment statement: " . $conn->error);
        }
        
        $payment_stmt->bind_param($payment_types, ...$payment_values);

        if (!$payment_stmt->execute()) {
            throw new Exception("Failed to record payment");
        }

        $payment_id = $conn->insert_id;
        error_log("Payment recorded - ID: $payment_id");

        // Record discount usage if discount code was applied
        if (!empty($booking_data['discount_code']) && isset($booking_data['discount_amount']) && $booking_data['discount_amount'] > 0) {
            require_once '../controllers/discount_controller.php';
            
            // Get discount details
            $discount = validate_discount_code_ctr($booking_data['discount_code'], $_SESSION['user_id'], $booking_data['original_amount'] ?? $booking_data['total_amount']);
            
            if ($discount) {
                // Record discount usage
                record_discount_usage_ctr(
                    $discount['discount_id'],
                    $_SESSION['user_id'],
                    $booking_id,
                    $booking_data['discount_amount']
                );
                
                // Update discount code usage count
                $update_discount = $conn->prepare("
                    UPDATE tl_discount_codes 
                    SET usage_count = usage_count + 1 
                    WHERE discount_id = ?
                ");
                $update_discount->bind_param("i", $discount['discount_id']);
                $update_discount->execute();
                $update_discount->close();
                
                error_log("Discount code used - Code: {$booking_data['discount_code']}, Booking: $booking_id, Amount: {$booking_data['discount_amount']}");
            }
        }

        // Commit transaction
        mysqli_commit($conn);

        // Clear session booking data
        unset($_SESSION['pending_booking']);

        // Log audit action
        require_once '../classes/audit_log_class.php';
        log_audit_action(
            'booking_created',
            'booking',
            $booking_id,
            "Booking created via Paystack payment. Reference: $reference",
            $_SESSION['user_id']
        );

        // Return success
        ob_end_clean(); // Clear any output buffer
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
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Database error during booking verification: " . $e->getMessage());
        ob_end_clean(); // Clear any output buffer
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'debug' => [
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ]
        ]);
        exit();
    }
}

/**
 * Process premium subscription payment verification
 */
function process_premium_subscription_verification($reference) {
    error_log("Starting premium subscription verification for reference: $reference");

    // FIRST: Try to get subscription data from DATABASE (more reliable than session)
    $db = new db_connection();
    $db->db_connect();

    $pending_stmt = $db->db->prepare("
        SELECT subscription_payment_id, provider_id, amount, start_date, end_date, payment_reference
        FROM tl_subscription_payments
        WHERE payment_reference = ?
        AND payment_status = 'pending'
        LIMIT 1
    ");

    $subscription_data = null;

    if ($pending_stmt) {
        $pending_stmt->bind_param("s", $reference);
        $pending_stmt->execute();
        $result = $pending_stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $subscription_data = [
                'subscription_payment_id' => $row['subscription_payment_id'],
                'provider_id' => $row['provider_id'],
                'amount' => floatval($row['amount']),
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'reference' => $row['payment_reference']
            ];
            error_log("Subscription data found in DATABASE: " . json_encode($subscription_data));
        }
    }

    // FALLBACK: If not in database, check session (backward compatibility)
    if (!$subscription_data && isset($_SESSION['pending_premium_subscription'])) {
        $subscription_data = $_SESSION['pending_premium_subscription'];
        error_log("Subscription data found in SESSION (fallback): " . json_encode($subscription_data));
    }

    // If still not found, fail
    if (!$subscription_data) {
        error_log("Premium verification failed: No pending subscription found in database or session");
        echo json_encode([
            'status' => 'error',
            'verified' => false,
            'message' => 'Subscription data not found. Payment reference: ' . $reference,
            'debug' => [
                'reference' => $reference,
                'session_has_data' => isset($_SESSION['pending_premium_subscription']),
                'user_logged_in' => isset($_SESSION['user_id'])
            ]
        ]);
        return;
    }

    error_log("Using subscription data: " . json_encode($subscription_data));

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
        $subscription_payment_id = $subscription_data['subscription_payment_id'];

        error_log("Inserting premium listing for provider $provider_id");

        // Check if subscription_id column exists
        $check_subscription_id_col = $conn->query("SHOW COLUMNS FROM tl_premium_listings LIKE 'subscription_id'");
        $has_subscription_id = $check_subscription_id_col && $check_subscription_id_col->num_rows > 0;
        
        // Check if next_billing_date column exists
        $check_next_billing_col = $conn->query("SHOW COLUMNS FROM tl_premium_listings LIKE 'next_billing_date'");
        $has_next_billing = $check_next_billing_col && $check_next_billing_col->num_rows > 0;
        
        // Check if is_subscription column exists
        $check_is_subscription_col = $conn->query("SHOW COLUMNS FROM tl_premium_listings LIKE 'is_subscription'");
        $has_is_subscription = $check_is_subscription_col && $check_is_subscription_col->num_rows > 0;

        // Build INSERT statement dynamically based on available columns
        $insert_columns = ['provider_id', 'start_date', 'end_date', 'amount_paid', 'status', 'auto_renew', 'payment_reference'];
        $insert_values = ['?', '?', '?', '?', "'active'", '1', '?'];
        $bind_types = "issds";
        
        if ($has_subscription_id) {
            $insert_columns[] = 'subscription_id';
            $insert_values[] = '?';
            $bind_types .= 'i';
        }
        
        if ($has_next_billing) {
            $insert_columns[] = 'next_billing_date';
            $insert_values[] = '?';
            $bind_types .= 's';
        }
        
        if ($has_is_subscription) {
            $insert_columns[] = 'is_subscription';
            $insert_values[] = '1';
        }

        $insert_sql = "INSERT INTO tl_premium_listings (" . implode(', ', $insert_columns) . ") VALUES (" . implode(', ', $insert_values) . ")";
        
        $insert_listing = $conn->prepare($insert_sql);

        if (!$insert_listing) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Failed to prepare premium listing insert: " . $conn->error);
        }

        // Bind parameters - build array with references for call_user_func_array
        $bind_params = [&$provider_id, &$start_date, &$end_date, &$subscription_amount, &$reference];
        if ($has_subscription_id) {
            $bind_params[] = &$subscription_payment_id;
        }
        if ($has_next_billing) {
            $bind_params[] = &$next_billing_date;
        }
        
        // Use call_user_func_array for dynamic parameter binding
        $bind_args = array_merge([$bind_types], $bind_params);
        call_user_func_array([$insert_listing, 'bind_param'], $bind_args);

        if (!$insert_listing->execute()) {
            error_log("Execute failed: " . $insert_listing->error);
            throw new Exception("Failed to create premium listing: " . $insert_listing->error);
        }

        $premium_listing_id = $conn->insert_id;
        error_log("Premium listing created with ID: $premium_listing_id");

        // UPDATE the existing pending payment record to mark as completed
        $update_payment = $conn->prepare("
            UPDATE tl_subscription_payments
            SET payment_status = 'paid',
                premium_listing_id = ?,
                payment_date = NOW()
            WHERE payment_reference = ?
            AND payment_status = 'pending'
        ");

        if (!$update_payment) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }

        $update_payment->bind_param("is", $premium_listing_id, $reference);

        if (!$update_payment->execute()) {
            throw new Exception("Failed to update subscription payment record: " . $update_payment->error);
        }

        if ($update_payment->affected_rows === 0) {
            error_log("Warning: No pending payment record was updated for reference $reference");
        } else {
            error_log("Successfully updated payment record for reference $reference");
        }

        // Mark all provider's services as premium (update both columns if they exist)
        $check_premium_col = $conn->query("SHOW COLUMNS FROM tl_services LIKE 'is_premium'");
        $has_is_premium = $check_premium_col && $check_premium_col->num_rows > 0;
        
        $check_premium_listing_col = $conn->query("SHOW COLUMNS FROM tl_services LIKE 'is_premium_listing'");
        $has_is_premium_listing = $check_premium_listing_col && $check_premium_listing_col->num_rows > 0;
        
        if ($has_is_premium || $has_is_premium_listing) {
            // Build UPDATE statement with available columns
            $update_columns = [];
            if ($has_is_premium) {
                $update_columns[] = "is_premium = 1";
            }
            if ($has_is_premium_listing) {
                $update_columns[] = "is_premium_listing = 1";
            }
            
            $update_sql = "UPDATE tl_services SET " . implode(", ", $update_columns) . " WHERE provider_id = ? AND service_status = 'active'";
            $update_services = $conn->prepare($update_sql);
            $update_services->bind_param("i", $provider_id);
            $update_services->execute();
            $updated_count = $update_services->affected_rows;
            error_log("Updated $updated_count services to premium for provider $provider_id");
            
            // Also check if provider has any services
            $check_services = $conn->prepare("SELECT COUNT(*) as service_count FROM tl_services WHERE provider_id = ? AND service_status = 'active'");
            $check_services->bind_param("i", $provider_id);
            $check_services->execute();
            $service_result = $check_services->get_result();
            $service_count = $service_result->fetch_assoc()['service_count'];
            error_log("Provider $provider_id has $service_count active services");
        } else {
            error_log("Warning: Neither is_premium nor is_premium_listing column exists in tl_services");
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
