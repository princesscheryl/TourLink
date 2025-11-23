<?php
/**
 * Create Booking Action
 * Handles booking form submission
 */

require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_once '../controllers/service_controller.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'booking_reference' => null
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to make a booking';
    $response['require_login'] = true;
    echo json_encode($response);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get form data
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$service_date = isset($_POST['service_date']) ? trim($_POST['service_date']) : '';
$service_time = isset($_POST['service_time']) ? trim($_POST['service_time']) : '';
$number_of_people = isset($_POST['number_of_people']) ? (int)$_POST['number_of_people'] : 1;
$service_duration = isset($_POST['service_duration']) ? (int)$_POST['service_duration'] : 1;
$special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';

// Ensure duration is at least 1
if ($service_duration < 1) {
    $service_duration = 1;
}

// Validate required fields
if ($service_id <= 0 || empty($service_date) || empty($service_time)) {
    $response['message'] = 'Please fill in all required fields';
    echo json_encode($response);
    exit;
}

// Validate date is in the future
$booking_date = strtotime($service_date);
if ($booking_date < strtotime('today')) {
    $response['message'] = 'Please select a future date';
    echo json_encode($response);
    exit;
}

// Get service details
$service = get_service_by_id_ctr($service_id);
if (!$service) {
    $response['message'] = 'Service not found';
    echo json_encode($response);
    exit;
}

// Check capacity
if ($service['max_capacity'] && $number_of_people > $service['max_capacity']) {
    $response['message'] = 'Number of people exceeds maximum capacity (' . $service['max_capacity'] . ')';
    echo json_encode($response);
    exit;
}

// Calculate pricing
$base_price = (float)$service['base_price'];
$pricing_unit = $service['pricing_unit'];

// Calculate total based on pricing unit
switch ($pricing_unit) {
    case 'per_person':
        $original_amount = $base_price * $number_of_people;
        break;
    case 'per_hour':
        // Price per hour × number of hours
        $original_amount = $base_price * $service_duration;
        break;
    case 'per_day':
        // Price per day × number of days
        $original_amount = $base_price * $service_duration;
        break;
    case 'flat_rate':
    default:
        $original_amount = $base_price;
        break;
}

// Platform commission (e.g., 10%)
$commission_rate = 0.10;
$discount_amount = 0; // No discount for now
$total_amount = $original_amount - $discount_amount;
$commission_amount = $total_amount * $commission_rate;
$provider_earnings = $total_amount - $commission_amount;

// Prepare booking data
$booking_data = [
    'service_id' => $service_id,
    'tourist_id' => $_SESSION['user_id'],
    'provider_id' => $service['provider_id'],
    'service_date' => $service_date,
    'service_time' => $service_time,
    'number_of_people' => $number_of_people,
    'service_duration' => $service_duration,
    'original_amount' => $original_amount,
    'discount_amount' => $discount_amount,
    'total_amount' => $total_amount,
    'commission_amount' => $commission_amount,
    'provider_earnings' => $provider_earnings,
    'special_requests' => $special_requests
];

// Create the booking
$booking_id = create_booking_ctr($booking_data);

if ($booking_id) {
    // Get the booking reference
    $booking = get_booking_by_id_ctr($booking_id);

    $response['success'] = true;
    $response['message'] = 'Booking request submitted successfully!';
    $response['booking_reference'] = $booking['booking_reference'];
    $response['booking_id'] = $booking_id;
} else {
    $response['message'] = 'Failed to create booking. Please try again.';
}

echo json_encode($response);
exit;
?>
