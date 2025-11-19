<?php
require_once '../settings/core.php';
require_once '../controllers/review_controller.php';
require_once '../classes/booking_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/all_services.php");
    exit();
}

// Get and sanitize form data
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_title = isset($_POST['review_title']) ? trim($_POST['review_title']) : '';
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

// Validate required fields
if ($service_id <= 0 || $booking_id <= 0 || $rating < 1 || $rating > 5 || empty($review_title) || empty($review_text)) {
    $_SESSION['error'] = "Please fill in all required fields and select a rating (1-5 stars)";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

$tourist_id = $_SESSION['user_id'];

// Verify booking exists and belongs to user
$booking_class = new Booking();
$booking = $booking_class->get_booking_by_id($booking_id);

if (!$booking) {
    $_SESSION['error'] = "Invalid booking";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Check if booking belongs to the user
if ($booking['tourist_id'] != $tourist_id) {
    $_SESSION['error'] = "You can only review your own bookings";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Check if booking is completed
if ($booking['booking_status'] != 'completed') {
    $_SESSION['error'] = "You can only review completed bookings";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Check if booking is for this service
if ($booking['service_id'] != $service_id) {
    $_SESSION['error'] = "Booking does not match service";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Check if user has already reviewed this booking
if (has_reviewed_booking_ctr($booking_id, $tourist_id)) {
    $_SESSION['error'] = "You have already reviewed this booking";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Submit the review
$review_id = submit_review_ctr($service_id, $tourist_id, $booking_id, $rating, $review_title, $review_text);

if ($review_id) {
    $_SESSION['success'] = "Thank you for your review! Your feedback helps other tourists.";
    header("Location: ../view/single_service.php?id=$service_id#reviews");
} else {
    $_SESSION['error'] = "Failed to submit review. Please try again.";
    header("Location: ../view/single_service.php?id=$service_id");
}

exit();
?>
