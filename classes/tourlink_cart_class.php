<?php
require_once __DIR__.'/../settings/db_class.php';

/**
 * TourLink Cart Class
 * Handles shopping cart operations for tourism services (tl_cart table)
 */
class TourLinkCart extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Add service to cart
     * @param int $user_id
     * @param int $service_id
     * @param int $quantity
     * @param string $selected_date (YYYY-MM-DD)
     * @param string $selected_time (HH:MM:SS)
     * @param int $number_of_people
     * @return int|bool Cart ID on success, false on failure
     */
    public function add_to_cart($user_id, $service_id, $quantity = 1, $selected_date = null, $selected_time = null, $number_of_people = 1)
    {
        // Check if service already in cart for this user
        $existing = $this->check_cart_item($user_id, $service_id);

        if ($existing) {
            // Update existing cart item
            return $this->update_cart_quantity($existing['cart_id'], $existing['quantity'] + $quantity);
        }

        // Add new cart item
        $stmt = $this->db->prepare(
            "INSERT INTO tl_cart (user_id, service_id, quantity, selected_date, selected_time, number_of_people)
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iiissi", $user_id, $service_id, $quantity, $selected_date, $selected_time, $number_of_people);

        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Check if service is already in user's cart
     * @param int $user_id
     * @param int $service_id
     * @return array|bool Cart item details or false
     */
    private function check_cart_item($user_id, $service_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_cart WHERE user_id = ? AND service_id = ?");
        $stmt->bind_param("ii", $user_id, $service_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get all cart items for a user with service details
     * @param int $user_id
     * @return array|bool Array of cart items or false
     */
    public function get_cart_items($user_id)
    {
        $sql = "SELECT c.*,
                       s.service_title,
                       s.service_description,
                       s.base_price,
                       s.pricing_unit,
                       s.service_images,
                       s.max_capacity,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.provider_id
                FROM tl_cart c
                INNER JOIN tl_services s ON c.service_id = s.service_id
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                WHERE c.user_id = ?
                ORDER BY c.added_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get cart count for a user
     * @param int $user_id
     * @return int Number of items in cart
     */
    public function get_cart_count($user_id)
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as total_items FROM tl_cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total_items'] ? (int)$row['total_items'] : 0;
    }

    /**
     * Update cart item quantity
     * @param int $cart_id
     * @param int $quantity
     * @return bool True on success, false on failure
     */
    public function update_cart_quantity($cart_id, $quantity)
    {
        if ($quantity <= 0) {
            return $this->remove_from_cart($cart_id);
        }

        $stmt = $this->db->prepare("UPDATE tl_cart SET quantity = ? WHERE cart_id = ?");
        $stmt->bind_param("ii", $quantity, $cart_id);

        return $stmt->execute();
    }

    /**
     * Update cart item date and time
     * @param int $cart_id
     * @param string $selected_date
     * @param string $selected_time
     * @param int $number_of_people
     * @return bool True on success, false on failure
     */
    public function update_cart_details($cart_id, $selected_date, $selected_time, $number_of_people)
    {
        $stmt = $this->db->prepare("UPDATE tl_cart SET selected_date = ?, selected_time = ?, number_of_people = ? WHERE cart_id = ?");
        $stmt->bind_param("ssii", $selected_date, $selected_time, $number_of_people, $cart_id);

        return $stmt->execute();
    }

    /**
     * Remove item from cart
     * @param int $cart_id
     * @return bool True on success, false on failure
     */
    public function remove_from_cart($cart_id)
    {
        $stmt = $this->db->prepare("DELETE FROM tl_cart WHERE cart_id = ?");
        $stmt->bind_param("i", $cart_id);

        return $stmt->execute();
    }

    /**
     * Clear all cart items for a user
     * @param int $user_id
     * @return bool True on success, false on failure
     */
    public function clear_cart($user_id)
    {
        $stmt = $this->db->prepare("DELETE FROM tl_cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        return $stmt->execute();
    }

    /**
     * Calculate cart total for a user
     * @param int $user_id
     * @return float Total cart amount
     */
    public function get_cart_total($user_id)
    {
        $sql = "SELECT SUM(s.base_price * c.quantity * c.number_of_people) as total
                FROM tl_cart c
                INNER JOIN tl_services s ON c.service_id = s.service_id
                WHERE c.user_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ? (float)$row['total'] : 0.00;
    }

    /**
     * Verify cart item belongs to user
     * @param int $cart_id
     * @param int $user_id
     * @return bool True if belongs to user, false otherwise
     */
    public function verify_cart_ownership($cart_id, $user_id)
    {
        $stmt = $this->db->prepare("SELECT cart_id FROM tl_cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}
?>
