<?php
header('Content-Type: application/json');
session_start();

$response = array();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You must be logged in to perform this action';
    echo json_encode($response);
    exit();
}

require_once '../classes/service_provider_class.php';
require_once '../classes/booking_class.php';

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    $response['status'] = 'error';
    $response['message'] = 'Only providers can update booking status';
    echo json_encode($response);
    exit();
}

// Get form data
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate inputs
if ($booking_id <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid booking ID';
    echo json_encode($response);
    exit();
}

if (empty($status)) {
    $response['status'] = 'error';
    $response['message'] = 'Status is required';
    echo json_encode($response);
    exit();
}

// Verify booking belongs to this provider
$booking_class = new Booking();
$booking = $booking_class->get_booking_by_id($booking_id);

if (!$booking || $booking['provider_id'] != $provider['provider_id']) {
    $response['status'] = 'error';
    $response['message'] = 'Booking not found or you do not have permission to update it';
    echo json_encode($response);
    exit();
}

// Update booking status
$updated = false;
if ($status === 'completed') {
    $updated = $booking_class->complete_booking($booking_id);
} else {
    $updated = $booking_class->update_booking_status($booking_id, $status);
}

if ($updated) {
    $response['status'] = 'success';
    $response['message'] = 'Booking status updated successfully!';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to update booking status. Please try again.';
}

echo json_encode($response);
?>
