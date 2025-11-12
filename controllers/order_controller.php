<?php
// Include the order class
require_once(__DIR__ . '/../classes/order_class.php');

/**
 * Create a new order
 *
 * @param int $customer_id Customer ID
 * @param int $invoice_no Invoice number
 * @param string $order_date Order date
 * @param string $order_status Order status
 * @return int|bool Order ID on success, false on failure
 */
function create_order_ctr($customer_id, $invoice_no, $order_date, $order_status) {
    $order = new Order();
    return $order->createOrder($customer_id, $invoice_no, $order_date, $order_status);
}

/**
 * Add order details for a product
 *
 * @param int $order_id Order ID
 * @param int $product_id Product ID
 * @param int $quantity Quantity ordered
 * @return bool True on success, false on failure
 */
function add_order_details_ctr($order_id, $product_id, $quantity) {
    $order = new Order();
    return $order->addOrderDetails($order_id, $product_id, $quantity);
}

/**
 * Record a payment transaction
 *
 * @param float $amount Payment amount
 * @param int $customer_id Customer ID
 * @param int $order_id Order ID
 * @param string $currency Currency code
 * @param string $payment_date Payment date
 * @return int|bool Payment ID on success, false on failure
 */
function record_payment_ctr($amount, $customer_id, $order_id, $currency, $payment_date) {
    $order = new Order();
    return $order->recordPayment($amount, $customer_id, $order_id, $currency, $payment_date);
}

/**
 * Get all orders for a customer
 *
 * @param int $customer_id Customer ID
 * @return array Array of orders
 */
function get_user_orders_ctr($customer_id) {
    $order = new Order();
    return $order->getUserOrders($customer_id);
}

/**
 * Get details of a specific order
 *
 * @param int $order_id Order ID
 * @return array Order details with items
 */
function get_order_details_ctr($order_id) {
    $order = new Order();
    return $order->getOrderDetails($order_id);
}

/**
 * Get order information by ID
 *
 * @param int $order_id Order ID
 * @return array|null Order information
 */
function get_order_by_id_ctr($order_id) {
    $order = new Order();
    return $order->getOrderById($order_id);
}

/**
 * Update the status of an order
 *
 * @param int $order_id Order ID
 * @param string $status New status
 * @return bool True on success, false on failure
 */
function update_order_status_ctr($order_id, $status) {
    $order = new Order();
    return $order->updateOrderStatus($order_id, $status);
}

/**
 * Generate a unique invoice number
 *
 * @return int Unique invoice number
 */
function generate_invoice_number_ctr() {
    $order = new Order();
    return $order->generateInvoiceNumber();
}
?>
