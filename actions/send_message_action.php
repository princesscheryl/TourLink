<?php
require_once '../settings/core.php';
require_once '../controllers/message_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to send messages']);
    exit();
}

// Get POST data
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$message_text = isset($_POST['message_text']) ? trim($_POST['message_text']) : '';
$service_id = isset($_POST['service_id']) && $_POST['service_id'] ? (int)$_POST['service_id'] : null;
$booking_id = isset($_POST['booking_id']) && $_POST['booking_id'] ? (int)$_POST['booking_id'] : null;

// Validate input
if (!$receiver_id) {
    echo json_encode(['success' => false, 'message' => 'Receiver ID is required']);
    exit();
}

if (empty($message_text)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

if (strlen($message_text) > 5000) {
    echo json_encode(['success' => false, 'message' => 'Message is too long (max 5000 characters)']);
    exit();
}

$sender_id = $_SESSION['user_id'];

// Prevent sending message to self
if ($sender_id == $receiver_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send message to yourself']);
    exit();
}

// Send message
$message_id = send_message_ctr($sender_id, $receiver_id, $message_text, $service_id, $booking_id);

if ($message_id) {
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'message_id' => $message_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
}

