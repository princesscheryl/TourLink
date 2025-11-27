<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';
require_once '../controllers/booking_controller.php';
require_once '../controllers/discount_controller.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login as a tourist to make payments'
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$booking_id = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;
$email = isset($input['email']) ? trim($input['email']) : '';
$discount_code = isset($input['discount_code']) ? trim($input['discount_code']) : null;

if (!$booking_id || !$email) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing booking ID or email'
    ]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email address'
    ]);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // Get booking details
    $booking = get_booking_by_id_ctr($booking_id);

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    // Verify booking belongs to this user
    if ($booking['tourist_id'] != $user_id) {
        throw new Exception('Unauthorized access to this booking');
    }

    // Check if already paid
    if ($booking['payment_status'] === 'escrow' || $booking['payment_status'] === 'released') {
        throw new Exception('This booking has already been paid');
    }

    $total_amount = floatval($booking['total_amount']);
    $discount_amount = 0;
    $discount_id = null;

    // Apply discount code if provided
    if ($discount_code) {
        $discount = validate_discount_code_ctr($discount_code, $user_id, $total_amount);

        if ($discount) {
            $discount_id = $discount['discount_id'];
            $discount_amount = calculate_discount_ctr($discount, $total_amount);
            $total_amount -= $discount_amount;

            // Make sure we don't go below zero
            if ($total_amount < 0) {
                $total_amount = 0;
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid or expired discount code'
            ]);
            exit();
        }
    }

    // Minimum payment amount (10 pesewas = GHS 0.10)
    if ($total_amount < 0.10) {
        throw new Exception('Payment amount too small');
    }

    // Generate unique reference
    $reference = generate_payment_reference($user_id, $booking_id);

    // Store payment session data
    $_SESSION['paystack_ref'] = $reference;
    $_SESSION['paystack_booking_id'] = $booking_id;
    $_SESSION['paystack_amount'] = $total_amount;
    $_SESSION['paystack_discount_code'] = $discount_code;
    $_SESSION['paystack_discount_id'] = $discount_id;
    $_SESSION['paystack_discount_amount'] = $discount_amount;
    $_SESSION['paystack_timestamp'] = time();

    // Prepare metadata
    $metadata = [
        'booking_id' => $booking_id,
        'tourist_id' => $user_id,
        'service_title' => $booking['service_title'],
        'discount_code' => $discount_code,
        'original_amount' => $booking['total_amount'],
        'discount_amount' => $discount_amount
    ];

    // Initialize Paystack transaction
    $paystack_response = paystack_initialize_transaction($total_amount, $email, $reference, $metadata);

    if (!$paystack_response) {
        throw new Exception('No response from payment gateway');
    }

    if (isset($paystack_response['status']) && $paystack_response['status'] === true) {
        error_log("Payment initialized - Booking: $booking_id, Reference: $reference, Amount: GHS $total_amount");

        echo json_encode([
            'status' => 'success',
            'authorization_url' => $paystack_response['data']['authorization_url'],
            'reference' => $reference,
            'access_code' => $paystack_response['data']['access_code'],
            'amount' => number_format($total_amount, 2),
            'discount_applied' => $discount_amount > 0,
            'discount_amount' => number_format($discount_amount, 2),
            'message' => 'Redirecting to payment gateway...'
        ]);
    } else {
        $error_message = $paystack_response['message'] ?? 'Payment gateway error';
        throw new Exception($error_message);
    }

} catch (Exception $e) {
    error_log("Payment initialization failed: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
