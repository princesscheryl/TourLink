<?php
/**
 * Update Booking Status Action
 * Handles booking status updates (approve, reject, complete, cancel)
 */

// Enable error reporting and output buffering
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

session_start();
header('Content-Type: application/json');

require_once '../controllers/booking_controller.php';
require_once '../classes/service_provider_class.php';

$response = [
    'status' => 'error',
    'message' => ''
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in';
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
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

// Validate required fields
if ($booking_id <= 0 || empty($status)) {
    $response['message'] = 'Invalid request';
    echo json_encode($response);
    exit;
}

// Get booking details
$booking = get_booking_by_id_ctr($booking_id);
if (!$booking) {
    $response['message'] = 'Booking not found';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'tourist';

// Determine if user is authorized
$is_tourist = ($booking['tourist_id'] == $user_id);
$is_provider = false;

if ($user_type === 'provider') {
    $provider_class = new ServiceProvider();
    $provider = $provider_class->get_provider_by_user_id($user_id);
    if ($provider && $provider['provider_id'] == $booking['provider_id']) {
        $is_provider = true;
    }
}

// Handle different status updates
switch ($status) {
    case 'confirmed':
        // Only provider can confirm
        if (!$is_provider) {
            $response['message'] = 'Only the service provider can confirm bookings';
            break;
        }
        if ($booking['booking_status'] !== 'pending') {
            $response['message'] = 'Only pending bookings can be confirmed';
            break;
        }
        if (update_booking_status_ctr($booking_id, 'confirmed')) {
            $response['status'] = 'success';
            $response['message'] = 'Booking confirmed successfully';
            $response['new_status'] = 'confirmed';
        } else {
            $response['message'] = 'Failed to confirm booking';
        }
        break;

    case 'rejected':
        // Only provider can reject
        if (!$is_provider) {
            $response['message'] = 'Only the service provider can reject bookings';
            break;
        }
        if ($booking['booking_status'] !== 'pending') {
            $response['message'] = 'Only pending bookings can be rejected';
            break;
        }
        if (cancel_booking_ctr($booking_id, 'provider', $reason ?: 'Rejected by provider')) {
            $response['status'] = 'success';
            $response['message'] = 'Booking rejected';
            $response['new_status'] = 'cancelled';
        } else {
            $response['message'] = 'Failed to reject booking';
        }
        break;

    case 'completed':
        // Only provider can mark as completed
        if (!$is_provider) {
            $response['message'] = 'Only the service provider can mark bookings as completed';
            break;
        }
        if (!in_array($booking['booking_status'], ['confirmed', 'in_progress'])) {
            $response['message'] = 'Only confirmed or in-progress bookings can be completed';
            break;
        }
        
        try {
            if (complete_booking_ctr($booking_id)) {
                $response['status'] = 'success';
                $response['message'] = 'Booking marked as completed';
                $response['new_status'] = 'completed';
            } else {
                $response['message'] = 'Failed to complete booking';
            }
        } catch (Exception $e) {
            error_log("Error completing booking: " . $e->getMessage());
            // Still mark as success if booking was updated, even if invoice generation failed
            $response['status'] = 'success';
            $response['message'] = 'Booking marked as completed' . (strpos($e->getMessage(), 'invoice') !== false ? ' (Invoice generation skipped)' : '');
            $response['new_status'] = 'completed';
        } catch (Error $e) {
            error_log("Fatal error completing booking: " . $e->getMessage());
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }
        break;

    case 'cancelled':
        // Both tourist and provider can cancel
        if (!$is_tourist && !$is_provider) {
            $response['message'] = 'You are not authorized to cancel this booking';
            break;
        }
        if (in_array($booking['booking_status'], ['completed', 'cancelled', 'refunded'])) {
            $response['message'] = 'This booking cannot be cancelled';
            break;
        }
        $cancelled_by = $is_tourist ? 'tourist' : 'provider';
        if (cancel_booking_ctr($booking_id, $cancelled_by, $reason ?: 'Cancelled by ' . $cancelled_by)) {
            $response['status'] = 'success';
            $response['message'] = 'Booking cancelled successfully';
            $response['new_status'] = 'cancelled';
        } else {
            $response['message'] = 'Failed to cancel booking';
        }
        break;

    case 'in_progress':
        // Provider marks booking as in progress
        if (!$is_provider) {
            $response['message'] = 'Only the service provider can start the service';
            break;
        }
        if ($booking['booking_status'] !== 'confirmed') {
            $response['message'] = 'Only confirmed bookings can be started';
            break;
        }
        if (update_booking_status_ctr($booking_id, 'in_progress')) {
            $response['status'] = 'success';
            $response['message'] = 'Service marked as in progress';
            $response['new_status'] = 'in_progress';
        } else {
            $response['message'] = 'Failed to update booking';
        }
        break;

    default:
        $response['message'] = 'Invalid status';
}

// Clean output buffer and send response
ob_end_clean();
echo json_encode($response);
exit;
?>
