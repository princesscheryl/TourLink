<?php
// Include the cart class
require_once(__DIR__ . '/../classes/cart_class.php');

/**
 * Add a product to cart
 * Handles both logged in users and guests
 *
 * @param int $product_id Product ID to add
 * @param int $customer_id Customer ID (0 for guests)
 * @param string $ip_address IP address for guest tracking
 * @param int $quantity Quantity to add
 * @return bool True on success, false on failure
 */
function add_to_cart_ctr($product_id, $customer_id, $ip_address, $quantity) {
    $cart = new Cart();
    return $cart->addToCart($product_id, $customer_id, $ip_address, $quantity);
}

/**
 * Update the quantity of an item in the cart
 *
 * @param int $product_id Product ID to update
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @param int $new_quantity New quantity value
 * @return bool True on success, false on failure
 */
function update_cart_item_ctr($product_id, $customer_id, $ip_address, $new_quantity) {
    $cart = new Cart();
    return $cart->updateCartQuantity($product_id, $customer_id, $ip_address, $new_quantity);
}

/**
 * Remove an item from the cart
 *
 * @param int $product_id Product ID to remove
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @return bool True on success, false on failure
 */
function remove_from_cart_ctr($product_id, $customer_id, $ip_address) {
    $cart = new Cart();
    return $cart->removeFromCart($product_id, $customer_id, $ip_address);
}

/**
 * Get all items in user's cart
 *
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @return array Array of cart items with product details
 */
function get_user_cart_ctr($customer_id, $ip_address) {
    $cart = new Cart();
    return $cart->getUserCart($customer_id, $ip_address);
}

/**
 * Empty all items from cart
 *
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @return bool True on success, false on failure
 */
function empty_cart_ctr($customer_id, $ip_address) {
    $cart = new Cart();
    return $cart->emptyCart($customer_id, $ip_address);
}

/**
 * Get total number of items in cart
 *
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @return int Total count of items in cart
 */
function get_cart_count_ctr($customer_id, $ip_address) {
    $cart = new Cart();
    return $cart->getCartCount($customer_id, $ip_address);
}

/**
 * Check if a product already exists in cart
 *
 * @param int $product_id Product ID to check
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @return array|null Cart item if exists, null otherwise
 */
function check_product_in_cart_ctr($product_id, $customer_id, $ip_address) {
    $cart = new Cart();
    return $cart->checkProductInCart($product_id, $customer_id, $ip_address);
}
?>
