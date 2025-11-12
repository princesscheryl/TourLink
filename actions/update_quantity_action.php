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

// Check if required parameters are provided
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    $response['success'] = false;
    $response['message'] = 'Product ID and quantity are required';
    echo json_encode($response);
    exit();
}

// Get and validate input
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

// Validate product ID
if ($product_id <= 0) {
    $response['success'] = false;
    $response['message'] = 'Invalid product ID';
    echo json_encode($response);
    exit();
}

// Validate quantity (must be at least 1)
if ($quantity < 1) {
    $response['success'] = false;
    $response['message'] = 'Quantity must be at least 1';
    echo json_encode($response);
    exit();
}

// Update cart item quantity
$result = update_cart_item_ctr($product_id, $customer_id, $ip_address, $quantity);

if ($result) {
    // Get updated cart count
    $cart_count = get_cart_count_ctr($customer_id, $ip_address);

    $response['success'] = true;
    $response['message'] = 'Cart updated successfully';
    $response['cart_count'] = $cart_count;
} else {
    $response['success'] = false;
    $response['message'] = 'Failed to update cart. Please try again.';
}

echo json_encode($response);
?>
