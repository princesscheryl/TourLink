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

// Get cart count
$cart_count = get_cart_count_ctr($customer_id, $ip_address);

$response['success'] = true;
$response['cart_count'] = $cart_count;

echo json_encode($response);
?>
