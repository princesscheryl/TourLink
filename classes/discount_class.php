<?php
/**
 * Discount Code Class
 * Handles discount code operations for TourLink bookings
 */

require_once dirname(__FILE__) . '/../settings/db_class.php';

class Discount extends db_connection
{
    /**
     * Validate and get discount code details
     *
     * @param string $code Discount code
     * @param int $user_id User ID
     * @param float $booking_amount Booking amount
     * @return array|false Discount details if valid, false otherwise
     */
    public function validate_discount_code($code, $user_id, $booking_amount)
    {
        $code = strtoupper(trim($code));
        $today = date('Y-m-d');

        $sql = "SELECT * FROM tl_discount_codes
                WHERE code = ?
                AND is_active = 1
                AND valid_from <= ?
                AND valid_until >= ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("sss", $code, $today, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false; // Code not found or inactive
        }

        $discount = $result->fetch_assoc();

        // Check if usage limit reached
        if ($discount['usage_limit'] !== null && $discount['usage_count'] >= $discount['usage_limit']) {
            return false; // Code usage limit reached
        }

        // Check minimum booking amount
        if ($booking_amount < $discount['min_booking_amount']) {
            return false; // Booking amount too low
        }

        // Check per-user usage limit
        $user_usage = $this->get_user_discount_usage($discount['discount_id'], $user_id);
        if ($user_usage >= $discount['per_user_limit']) {
            return false; // User has reached their usage limit for this code
        }

        return $discount;
    }

    /**
     * Calculate discount amount
     *
     * @param array $discount Discount code details
     * @param float $booking_amount Booking amount
     * @return float Calculated discount amount
     */
    public function calculate_discount($discount, $booking_amount)
    {
        if ($discount['discount_type'] === 'percentage') {
            $discount_amount = ($booking_amount * $discount['discount_value']) / 100;

            // Apply max discount cap if set
            if ($discount['max_discount_amount'] !== null && $discount_amount > $discount['max_discount_amount']) {
                $discount_amount = $discount['max_discount_amount'];
            }
        } else {
            // Fixed amount discount
            $discount_amount = $discount['discount_value'];

            // Don't exceed booking amount
            if ($discount_amount > $booking_amount) {
                $discount_amount = $booking_amount;
            }
        }

        return round($discount_amount, 2);
    }

    /**
     * Get how many times a user has used a discount code
     *
     * @param int $discount_id
     * @param int $user_id
     * @return int Usage count
     */
    public function get_user_discount_usage($discount_id, $user_id)
    {
        $sql = "SELECT COUNT(*) as count FROM tl_discount_usage
                WHERE discount_id = ? AND user_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("ii", $discount_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return (int)$result['count'];
    }

    /**
     * Record discount code usage
     *
     * @param int $discount_id
     * @param int $user_id
     * @param int $booking_id
     * @param float $discount_amount
     * @return bool Success
     */
    public function record_discount_usage($discount_id, $user_id, $booking_id, $discount_amount)
    {
        $conn = $this->db_conn();

        // Begin transaction
        mysqli_begin_transaction($conn);

        try {
            // Insert usage record
            $sql = "INSERT INTO tl_discount_usage (discount_id, user_id, booking_id, discount_amount)
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $discount_id, $user_id, $booking_id, $discount_amount);

            if (!$stmt->execute()) {
                throw new Exception("Failed to record discount usage");
            }

            // Increment usage count
            $update_sql = "UPDATE tl_discount_codes SET usage_count = usage_count + 1
                          WHERE discount_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $discount_id);

            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update usage count");
            }

            // Commit transaction
            mysqli_commit($conn);
            return true;

        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($conn);
            error_log("Error recording discount usage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all active discount codes (admin)
     *
     * @return array List of discount codes
     */
    public function get_all_discount_codes()
    {
        $sql = "SELECT d.*, u.first_name, u.last_name
                FROM tl_discount_codes d
                LEFT JOIN tl_users u ON d.created_by = u.user_id
                ORDER BY d.date_created DESC";

        $result = $this->db_query($sql);
        return $result ? $this->db_fetch_all($sql) : [];
    }

    /**
     * Get discount code by ID
     *
     * @param int $discount_id
     * @return array|false
     */
    public function get_discount_by_id($discount_id)
    {
        $sql = "SELECT * FROM tl_discount_codes WHERE discount_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $discount_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

    /**
     * Create new discount code (admin only)
     *
     * @param array $data Discount code data
     * @return int|false Discount ID or false on failure
     */
    public function create_discount_code($data)
    {
        $sql = "INSERT INTO tl_discount_codes
                (code, discount_type, discount_value, min_booking_amount, max_discount_amount,
                 usage_limit, per_user_limit, valid_from, valid_until, applicable_to,
                 applicable_ids, is_active, created_by, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db_conn()->prepare($sql);
        $code = strtoupper(trim($data['code']));

        $stmt->bind_param(
            "ssdddiiisssiis",
            $code,
            $data['discount_type'],
            $data['discount_value'],
            $data['min_booking_amount'],
            $data['max_discount_amount'],
            $data['usage_limit'],
            $data['per_user_limit'],
            $data['valid_from'],
            $data['valid_until'],
            $data['applicable_to'],
            $data['applicable_ids'],
            $data['is_active'],
            $data['created_by'],
            $data['description']
        );

        if ($stmt->execute()) {
            return $this->db_conn()->insert_id;
        }

        return false;
    }

    /**
     * Update discount code
     *
     * @param int $discount_id
     * @param array $data Updated data
     * @return bool Success
     */
    public function update_discount_code($discount_id, $data)
    {
        $sql = "UPDATE tl_discount_codes SET
                code = ?, discount_type = ?, discount_value = ?,
                min_booking_amount = ?, max_discount_amount = ?,
                usage_limit = ?, per_user_limit = ?,
                valid_from = ?, valid_until = ?,
                applicable_to = ?, applicable_ids = ?,
                is_active = ?, description = ?
                WHERE discount_id = ?";

        $stmt = $this->db_conn()->prepare($sql);
        $code = strtoupper(trim($data['code']));

        $stmt->bind_param(
            "ssdddiiisssisi",
            $code,
            $data['discount_type'],
            $data['discount_value'],
            $data['min_booking_amount'],
            $data['max_discount_amount'],
            $data['usage_limit'],
            $data['per_user_limit'],
            $data['valid_from'],
            $data['valid_until'],
            $data['applicable_to'],
            $data['applicable_ids'],
            $data['is_active'],
            $data['description'],
            $discount_id
        );

        return $stmt->execute();
    }

    /**
     * Delete discount code
     *
     * @param int $discount_id
     * @return bool Success
     */
    public function delete_discount_code($discount_id)
    {
        $sql = "DELETE FROM tl_discount_codes WHERE discount_id = ?";
        $stmt = $this->db_conn()->prepare($sql);
        $stmt->bind_param("i", $discount_id);

        return $stmt->execute();
    }
}
?>
