<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';
require_once '../controllers/booking_controller.php';
require_once '../controllers/discount_controller.php';
require_once '../settings/db_class.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
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
    $booking_id = $_SESSION['paystack_booking_id'] ?? null;
    $expected_amount = $_SESSION['paystack_amount'] ?? 0;
    $discount_code = $_SESSION['paystack_discount_code'] ?? null;
    $discount_id = $_SESSION['paystack_discount_id'] ?? null;
    $discount_amount = $_SESSION['paystack_discount_amount'] ?? 0;

    if (!$booking_id) {
        throw new Exception('Booking information not found in session');
    }

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
?>
