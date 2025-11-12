<?php
// Include the database connection class
require_once(__DIR__ . '/../settings/db_class.php');

/**
 * Cart class for managing shopping cart operations
 * Handles adding, updating, removing items and retrieving cart data
 */
class Cart extends db_connection {

    /**
     * Add a product to the shopping cart
     * If product already exists, update quantity instead of creating duplicate
     *
     * @param int $product_id The ID of the product to add
     * @param int $customer_id The ID of the customer (0 for guest)
     * @param string $ip_address The IP address for guest carts
     * @param int $quantity The quantity to add
     * @return bool True on success, false on failure
     */
    public function addToCart($product_id, $customer_id, $ip_address, $quantity) {
        // First check if product already exists in cart
        $existing = $this->checkProductInCart($product_id, $customer_id, $ip_address);

        if ($existing) {
            // Product exists, update quantity
            $new_quantity = $existing['qty'] + $quantity;
            return $this->updateCartQuantity($existing['p_id'], $customer_id, $ip_address, $new_quantity);
        }

        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        // Product doesn't exist, add new entry
        $sql = "INSERT INTO cart (p_id, c_id, ip_add, qty) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iisi", $product_id, $customer_id, $ip_address, $quantity);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Check if a product already exists in the cart
     *
     * @param int $product_id The product ID to check
     * @param int $customer_id The customer ID
     * @param string $ip_address The IP address
     * @return array|null Cart item if exists, null otherwise
     */
    public function checkProductInCart($product_id, $customer_id, $ip_address) {
        // Connect to database
        if (!$this->db_connect()) {
            return null;
        }

        if ($customer_id > 0) {
            // Logged in user
            $sql = "SELECT * FROM cart WHERE p_id = ? AND c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $product_id, $customer_id);
        } else {
            // Guest user
            $sql = "SELECT * FROM cart WHERE p_id = ? AND ip_add = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("is", $product_id, $ip_address);
        }

        if (!$stmt) {
            return null;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $cart_item = $result->fetch_assoc();
        $stmt->close();

        return $cart_item;
    }

    /**
     * Update the quantity of a product in the cart
     *
     * @param int $product_id The product ID to update
     * @param int $customer_id The customer ID
     * @param string $ip_address The IP address
     * @param int $new_quantity The new quantity
     * @return bool True on success, false on failure
     */
    public function updateCartQuantity($product_id, $customer_id, $ip_address, $new_quantity) {
        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        if ($customer_id > 0) {
            // Logged in user
            $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $new_quantity, $product_id, $customer_id);
        } else {
            // Guest user
            $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iis", $new_quantity, $product_id, $ip_address);
        }

        if (!$stmt) {
            return false;
        }

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Remove a product from the cart
     *
     * @param int $product_id The product ID to remove
     * @param int $customer_id The customer ID
     * @param string $ip_address The IP address
     * @return bool True on success, false on failure
     */
    public function removeFromCart($product_id, $customer_id, $ip_address) {
        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        if ($customer_id > 0) {
            // Logged in user
            $sql = "DELETE FROM cart WHERE p_id = ? AND c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $product_id, $customer_id);
        } else {
            // Guest user
            $sql = "DELETE FROM cart WHERE p_id = ? AND ip_add = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("is", $product_id, $ip_address);
        }

        if (!$stmt) {
            return false;
        }

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Get all items in a user's cart with product details
     *
     * @param int $customer_id The customer ID
     * @param string $ip_address The IP address for guests
     * @return array Array of cart items with product details
     */
    public function getUserCart($customer_id, $ip_address) {
        // Connect to database
        if (!$this->db_connect()) {
            return array();
        }

        if ($customer_id > 0) {
            // Logged in user
            $sql = "SELECT c.p_id, c.qty, p.product_title, p.product_price, p.product_image,
                    p.product_desc, cat.cat_name, b.brand_name,
                    (c.qty * p.product_price) as subtotal
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    LEFT JOIN categories cat ON p.product_cat = cat.cat_id
                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                    WHERE c.c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            // Guest user
            $sql = "SELECT c.p_id, c.qty, p.product_title, p.product_price, p.product_image,
                    p.product_desc, cat.cat_name, b.brand_name,
                    (c.qty * p.product_price) as subtotal
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    LEFT JOIN categories cat ON p.product_cat = cat.cat_id
                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                    WHERE c.ip_add = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $ip_address);
        }

        if (!$stmt) {
            return array();
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $cart_items = array();

        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
        }

        $stmt->close();
        return $cart_items;
    }

    /**
     * Empty the entire cart for a user
     *
     * @param int $customer_id The customer ID
     * @param string $ip_address The IP address for guests
     * @return bool True on success, false on failure
     */
    public function emptyCart($customer_id, $ip_address) {
        // Connect to database
        if (!$this->db_connect()) {
            return false;
        }

        if ($customer_id > 0) {
            // Logged in user
            $sql = "DELETE FROM cart WHERE c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            // Guest user
            $sql = "DELETE FROM cart WHERE ip_add = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $ip_address);
        }

        if (!$stmt) {
            return false;
        }

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Get the total number of items in cart
     *
     * @param int $customer_id The customer ID
     * @param string $ip_address The IP address for guests
     * @return int Total count of items
     */
    public function getCartCount($customer_id, $ip_address) {
        // Connect to database
        if (!$this->db_connect()) {
            return 0;
        }

        if ($customer_id > 0) {
            $sql = "SELECT SUM(qty) as total FROM cart WHERE c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $customer_id);
        } else {
            $sql = "SELECT SUM(qty) as total FROM cart WHERE ip_add = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $ip_address);
        }

        if (!$stmt) {
            return 0;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['total'] ? $row['total'] : 0;
    }
}
?>
