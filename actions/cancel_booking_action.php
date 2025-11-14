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
    $response['message'] = 'Only providers can cancel bookings';
    echo json_encode($response);
    exit();
}

// Get form data
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$cancelled_by = isset($_POST['cancelled_by']) ? trim($_POST['cancelled_by']) : '';
$cancellation_reason = isset($_POST['cancellation_reason']) ? trim($_POST['cancellation_reason']) : '';

// Validate inputs
if ($booking_id <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid booking ID';
    echo json_encode($response);
    exit();
}

if (empty($cancellation_reason)) {
    $response['status'] = 'error';
    $response['message'] = 'Cancellation reason is required';
    echo json_encode($response);
    exit();
}

// Verify booking belongs to this provider
$booking_class = new Booking();
$booking = $booking_class->get_booking_by_id($booking_id);

if (!$booking || $booking['provider_id'] != $provider['provider_id']) {
    $response['status'] = 'error';
    $response['message'] = 'Booking not found or you do not have permission to cancel it';
    echo json_encode($response);
    exit();
}

// Check if booking can be cancelled
if (in_array($booking['booking_status'], ['completed', 'cancelled'])) {
    $response['status'] = 'error';
    $response['message'] = 'This booking cannot be cancelled';
    echo json_encode($response);
    exit();
}

// Cancel booking
$cancelled = $booking_class->cancel_booking($booking_id, $cancelled_by, $cancellation_reason);

if ($cancelled) {
    $response['status'] = 'success';
    $response['message'] = 'Booking cancelled successfully!';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to cancel booking. Please try again.';
}

echo json_encode($response);
?>
