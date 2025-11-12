<?php
session_start();
header('Content-Type: application/json');

// Include necessary files
require_once(__DIR__ . '/../controllers/cart_controller.php');

// Initialize response array
$response = array();

// Get the current user ID (0 if not logged in)
$customer_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Get user's IP address for guest cart tracking
$ip_address = $_SERVER['REMOTE_ADDR'];

// Empty the cart
$result = empty_cart_ctr($customer_id, $ip_address);

if ($result) {
    $response['success'] = true;
    $response['message'] = 'Cart emptied successfully';
    $response['cart_count'] = 0;
} else {
    $response['success'] = false;
    $response['message'] = 'Failed to empty cart. Please try again.';
}

echo json_encode($response);
?>
