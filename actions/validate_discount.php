<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_once '../controllers/discount_controller.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login as a tourist to apply discounts'
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$booking_id = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;
$discount_code = isset($input['discount_code']) ? trim($input['discount_code']) : '';
$amount = isset($input['amount']) ? floatval($input['amount']) : 0;

if (!$booking_id || !$discount_code || $amount <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required information'
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

    // Validate discount code
    $discount = validate_discount_code_ctr($discount_code, $user_id, $amount);

    if (!$discount) {
        throw new Exception('Invalid or expired discount code');
    }

    // Calculate discount amount
    $discount_amount = calculate_discount_ctr($discount, $amount);
    $final_amount = $amount - $discount_amount;

    // Make sure final amount doesn't go below minimum
    if ($final_amount < 0.10) {
        $final_amount = 0.10;
        $discount_amount = $amount - $final_amount;
    }

    // Build success response
    $response = [
        'status' => 'success',
        'message' => 'Discount code is valid',
        'discount_code' => strtoupper($discount_code),
        'discount_type' => $discount['discount_type'],
        'discount_value' => $discount['discount_value'],
        'discount_amount' => number_format($discount_amount, 2),
        'original_amount' => number_format($amount, 2),
        'final_amount' => number_format($final_amount, 2),
        'currency' => 'GHS'
    ];

    // Log successful validation
    error_log("Discount validated - Code: $discount_code, User: $user_id, Booking: $booking_id, Discount: GHS $discount_amount");

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Discount validation error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
