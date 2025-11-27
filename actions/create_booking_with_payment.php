<?php
/**
 * Create Booking With Payment Action
 * Creates booking with guest details and redirects to payment
 */

require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_once '../controllers/service_controller.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'booking_id' => null
];

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    $response['message'] = 'Please log in to make a booking';
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

// Guest details
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

// Additional booking details
$booking_for = isset($_POST['booking_for']) ? $_POST['booking_for'] : 'main_guest';
$travelling_for_work = isset($_POST['travelling_for_work']) ? $_POST['travelling_for_work'] : 'no';
$special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
$arrival_time = isset($_POST['arrival_time']) ? trim($_POST['arrival_time']) : '';

// Validate required fields
if ($service_id <= 0) {
    $response['message'] = 'Invalid service';
    echo json_encode($response);
    exit;
}

if (empty($service_date) || empty($service_time)) {
    $response['message'] = 'Please provide service date and time';
    echo json_encode($response);
    exit;
}

if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    $response['message'] = 'Please fill in all required personal details';
    echo json_encode($response);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please provide a valid email address';
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

switch ($pricing_unit) {
    case 'per_person':
        $original_amount = $base_price * $number_of_people;
        break;
    case 'per_hour':
        $original_amount = $base_price * $service_duration;
        break;
    case 'per_day':
        $original_amount = $base_price * $service_duration;
        break;
    case 'flat_rate':
    default:
        $original_amount = $base_price;
        break;
}

// Platform commission (10%)
$commission_rate = 0.10;
$discount_amount = 0;
$total_amount = $original_amount - $discount_amount;
$commission_amount = $total_amount * $commission_rate;
$provider_earnings = $total_amount - $commission_amount;

// Prepare guest details as JSON
$guest_details = json_encode([
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'phone' => $phone,
    'booking_for' => $booking_for,
    'travelling_for_work' => $travelling_for_work,
    'arrival_time' => $arrival_time
]);

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
    'special_requests' => $special_requests,
    'guest_details' => $guest_details,
    'booking_status' => 'pending',  // Pending payment
    'payment_status' => 'pending'
];

// Create the booking
$booking_id = create_booking_ctr($booking_data);

if ($booking_id) {
    // Log the booking creation
    error_log("Booking created: ID=$booking_id, Tourist={$_SESSION['user_id']}, Service=$service_id, Amount=$total_amount");

    $response['success'] = true;
    $response['message'] = 'Booking created successfully. Please proceed to payment.';
    $response['booking_id'] = $booking_id;
} else {
    $response['message'] = 'Failed to create booking. Please try again.';
}

echo json_encode($response);
exit;
?>
