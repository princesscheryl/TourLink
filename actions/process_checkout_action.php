<?php
session_start();
header('Content-Type: application/json');

// Include necessary files
require_once(__DIR__ . '/../controllers/cart_controller.php');
require_once(__DIR__ . '/../controllers/order_controller.php');

// Initialize response array
$response = array();

// Check if user is logged in (only logged in users can checkout)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $response['success'] = false;
    $response['message'] = 'You must be logged in to checkout';
    echo json_encode($response);
    exit();
}

$customer_id = $_SESSION['user_id'];
$ip_address = $_SERVER['REMOTE_ADDR'];

// Get cart items
$cart_items = get_user_cart_ctr($customer_id, $ip_address);

// Check if cart is empty
if (empty($cart_items)) {
    $response['success'] = false;
    $response['message'] = 'Your cart is empty';
    echo json_encode($response);
    exit();
}

// Calculate total amount
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['subtotal'];
}

// Generate unique invoice number
$invoice_no = generate_invoice_number_ctr();

// Get current date
$current_date = date('Y-m-d');

// Start transaction by creating the order
$order_id = create_order_ctr($customer_id, $invoice_no, $current_date, 'Pending');

if (!$order_id) {
    $response['success'] = false;
    $response['message'] = 'Failed to create order. Please try again.';
    echo json_encode($response);
    exit();
}

// Add each cart item to order details
$all_items_added = true;
foreach ($cart_items as $item) {
    $result = add_order_details_ctr($order_id, $item['p_id'], $item['qty']);
    if (!$result) {
        $all_items_added = false;
        break;
    }
}

if (!$all_items_added) {
    $response['success'] = false;
    $response['message'] = 'Failed to process order items. Please try again.';
    echo json_encode($response);
    exit();
}

// Record the simulated payment
$currency = 'GHS'; // You can make this dynamic based on your needs
$payment_id = record_payment_ctr($total_amount, $customer_id, $order_id, $currency, $current_date);

if (!$payment_id) {
    $response['success'] = false;
    $response['message'] = 'Failed to process payment. Please try again.';
    echo json_encode($response);
    exit();
}

// Update order status to completed
$status_updated = update_order_status_ctr($order_id, 'Completed');

// Empty the cart after successful checkout
$cart_emptied = empty_cart_ctr($customer_id, $ip_address);

if (!$cart_emptied) {
    // Log this but don't fail the checkout
    error_log("Failed to empty cart for customer: $customer_id");
}

// Prepare success response
$response['success'] = true;
$response['message'] = 'Order placed successfully!';
$response['order_id'] = $order_id;
$response['invoice_no'] = $invoice_no;
$response['total_amount'] = number_format($total_amount, 2);
$response['currency'] = $currency;
$response['order_date'] = $current_date;

echo json_encode($response);
?>
