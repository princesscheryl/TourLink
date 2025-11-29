<?php
/**
 * Initialize Paystack Payment for Booking
 * Handles payment initialization for service bookings
 */
header('Content-Type: application/json');
session_start();
require_once '../settings/core.php';
require_once '../settings/paystack_config.php';

error_log("=== PAYSTACK INITIALIZE BOOKING PAYMENT ===");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to complete payment'
    ]);
    exit();
}

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $service_id = isset($input['service_id']) ? intval($input['service_id']) : 0;
    $service_date = isset($input['service_date']) ? trim($input['service_date']) : '';
    $service_time = isset($input['service_time']) ? trim($input['service_time']) : '';
    $number_of_people = isset($input['number_of_people']) ? intval($input['number_of_people']) : 1;
    $total_amount = isset($input['total_amount']) ? floatval($input['total_amount']) : 0;
    $original_amount = isset($input['original_amount']) ? floatval($input['original_amount']) : $total_amount;
    $discount_amount = isset($input['discount_amount']) ? floatval($input['discount_amount']) : 0;
    $customer_email = isset($input['email']) ? trim($input['email']) : '';
    $discount_code = isset($input['discount_code']) ? trim($input['discount_code']) : '';
    $service_duration = isset($input['service_duration']) ? intval($input['service_duration']) : 1;
    $guest_details = isset($input['guest_details']) ? $input['guest_details'] : [];

// Validate required fields
if (!$service_id || !$service_date || !$total_amount || !$customer_email) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required booking information'
    ]);
    exit();
}

// Validate amount
if ($total_amount <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid booking amount'
    ]);
    exit();
}

// Validate email
if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email address'
    ]);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // Generate unique reference for this booking payment
    $reference = 'TLINK-BOOK-' . $service_id . '-' . $user_id . '-' . time();

    error_log("Initializing booking payment - User: $user_id, Service: $service_id, Amount: $total_amount GHS");

    // Store booking data in session for verification later
    $_SESSION['pending_booking'] = [
        'service_id' => $service_id,
        'service_date' => $service_date,
        'service_time' => $service_time,
        'number_of_people' => $number_of_people,
        'service_duration' => $service_duration,
        'total_amount' => $total_amount,
        'original_amount' => $original_amount,
        'discount_code' => $discount_code,
        'discount_amount' => $discount_amount,
        'guest_details' => $guest_details,
        'reference' => $reference,
        'timestamp' => time()
    ];
    
    error_log("Booking data stored in session - Original: $original_amount, Discount: $discount_amount, Total: $total_amount, Code: $discount_code");

    // Prepare metadata for Paystack
    $metadata = [
        'payment_type' => 'booking',
        'service_id' => $service_id,
        'user_id' => $user_id,
        'booking_date' => $service_date,
        'guests' => $number_of_people
    ];

    // Initialize Paystack transaction
    $paystack_response = paystack_initialize_transaction($total_amount, $customer_email, $reference, $metadata);

    if (!$paystack_response) {
        throw new Exception("No response from Paystack API");
    }

    if (isset($paystack_response['status']) && $paystack_response['status'] === true) {
        error_log("Paystack transaction initialized - Reference: $reference");

        echo json_encode([
            'status' => 'success',
            'authorization_url' => $paystack_response['data']['authorization_url'],
            'reference' => $reference,
            'access_code' => $paystack_response['data']['access_code'],
            'message' => 'Redirecting to secure payment gateway...'
        ]);
    } else {
        error_log("Paystack API error: " . json_encode($paystack_response));

        $error_message = $paystack_response['message'] ?? 'Payment gateway error';
        throw new Exception($error_message);
    }

} catch (Exception $e) {
    error_log("Error initializing booking payment: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to initialize payment: ' . $e->getMessage()
    ]);
}
?>
