<?php
/**
 * Validate Discount Code for Booking
 * Validates discount codes before booking payment
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/discount_controller.php';

$response = [
    'status' => 'error',
    'message' => ''
];

// Check authentication
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to use discount codes';
    echo json_encode($response);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$discount_code = isset($input['discount_code']) ? trim($input['discount_code']) : '';
$booking_amount = isset($input['booking_amount']) ? floatval($input['booking_amount']) : 0;
$service_id = isset($input['service_id']) ? (int)$input['service_id'] : 0;

if (empty($discount_code)) {
    $response['message'] = 'Please enter a discount code';
    echo json_encode($response);
    exit();
}

if ($booking_amount <= 0) {
    $response['message'] = 'Invalid booking amount';
    echo json_encode($response);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // Validate discount code
    $discount = validate_discount_code_ctr($discount_code, $user_id, $booking_amount);

    if (!$discount) {
        $response['message'] = 'Invalid, expired, or ineligible discount code';
        echo json_encode($response);
        exit();
    }

    // Check if code applies to this service
    if ($discount['applicable_to'] !== 'all') {
        require_once '../controllers/service_controller.php';
        $service = get_service_by_id_ctr($service_id);
        
        if (!$service) {
            $response['message'] = 'Service not found';
            echo json_encode($response);
            exit();
        }

        $applicable_ids = json_decode($discount['applicable_ids'] ?? '[]', true);
        
        if ($discount['applicable_to'] === 'categories' && !in_array($service['category_id'], $applicable_ids)) {
            $response['message'] = 'This discount code does not apply to this service category';
            echo json_encode($response);
            exit();
        }
        
        if ($discount['applicable_to'] === 'providers' && !in_array($service['provider_id'], $applicable_ids)) {
            $response['message'] = 'This discount code does not apply to this provider';
            echo json_encode($response);
            exit();
        }
    }

    // Calculate discount amount
    $discount_amount = calculate_discount_ctr($discount, $booking_amount);
    $final_amount = $booking_amount - $discount_amount;

    // Ensure final amount is not negative
    if ($final_amount < 0.10) {
        $final_amount = 0.10;
        $discount_amount = $booking_amount - $final_amount;
    }

    // Return success response
    $response = [
        'status' => 'success',
        'message' => 'Discount code is valid',
        'discount_code' => strtoupper($discount_code),
        'discount_type' => $discount['discount_type'],
        'discount_value' => $discount['discount_value'],
        'discount_amount' => number_format($discount_amount, 2),
        'original_amount' => number_format($booking_amount, 2),
        'final_amount' => number_format($final_amount, 2),
        'currency' => 'GHS'
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Discount validation error: " . $e->getMessage());
    $response['message'] = 'An error occurred while validating the discount code';
    echo json_encode($response);
}
?>

