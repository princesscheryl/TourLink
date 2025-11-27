<?php
/**
 * Discount Code Controller
 * Controller functions for discount code operations
 */

require_once dirname(__FILE__) . '/../classes/discount_class.php';

/**
 * Validate discount code
 *
 * @param string $code
 * @param int $user_id
 * @param float $booking_amount
 * @return array|false
 */
function validate_discount_code_ctr($code, $user_id, $booking_amount)
{
    $discount = new Discount();
    return $discount->validate_discount_code($code, $user_id, $booking_amount);
}

/**
 * Calculate discount amount
 *
 * @param array $discount
 * @param float $booking_amount
 * @return float
 */
function calculate_discount_ctr($discount, $booking_amount)
{
    $discount_obj = new Discount();
    return $discount_obj->calculate_discount($discount, $booking_amount);
}

/**
 * Record discount usage
 *
 * @param int $discount_id
 * @param int $user_id
 * @param int $booking_id
 * @param float $discount_amount
 * @return bool
 */
function record_discount_usage_ctr($discount_id, $user_id, $booking_id, $discount_amount)
{
    $discount = new Discount();
    return $discount->record_discount_usage($discount_id, $user_id, $booking_id, $discount_amount);
}

/**
 * Get all discount codes
 *
 * @return array
 */
function get_all_discount_codes_ctr()
{
    $discount = new Discount();
    return $discount->get_all_discount_codes();
}

/**
 * Get discount code by ID
 *
 * @param int $discount_id
 * @return array|false
 */
function get_discount_by_id_ctr($discount_id)
{
    $discount = new Discount();
    return $discount->get_discount_by_id($discount_id);
}

/**
 * Create discount code
 *
 * @param string $code
 * @param string $discount_type
 * @param float $discount_value
 * @param float $min_booking_amount
 * @param float|null $max_discount_amount
 * @param int|null $usage_limit
 * @param int $per_user_limit
 * @param string $valid_from
 * @param string $valid_until
 * @param string|null $description
 * @return int|false
 */
function create_discount_code_ctr($code, $discount_type, $discount_value, $min_booking_amount = 0, $max_discount_amount = null, $usage_limit = null, $per_user_limit = 1, $valid_from, $valid_until, $description = null)
{
    $data = [
        'code' => $code,
        'discount_type' => $discount_type,
        'discount_value' => $discount_value,
        'min_booking_amount' => $min_booking_amount,
        'max_discount_amount' => $max_discount_amount,
        'usage_limit' => $usage_limit,
        'per_user_limit' => $per_user_limit,
        'valid_from' => $valid_from,
        'valid_until' => $valid_until,
        'applicable_to' => 'all',
        'applicable_ids' => null,
        'is_active' => 1,
        'created_by' => $_SESSION['admin_id'] ?? null,
        'description' => $description
    ];

    $discount = new Discount();
    return $discount->create_discount_code($data);
}

/**
 * Update discount code
 *
 * @param int $discount_id
 * @param string $code
 * @param string $discount_type
 * @param float $discount_value
 * @param float $min_booking_amount
 * @param float|null $max_discount_amount
 * @param int|null $usage_limit
 * @param int $per_user_limit
 * @param string $valid_from
 * @param string $valid_until
 * @param string|null $description
 * @return bool
 */
function update_discount_code_ctr($discount_id, $code, $discount_type, $discount_value, $min_booking_amount = 0, $max_discount_amount = null, $usage_limit = null, $per_user_limit = 1, $valid_from, $valid_until, $description = null)
{
    // Get existing discount to preserve some fields
    $discount_obj = new Discount();
    $existing = $discount_obj->get_discount_by_id($discount_id);

    $data = [
        'code' => $code,
        'discount_type' => $discount_type,
        'discount_value' => $discount_value,
        'min_booking_amount' => $min_booking_amount,
        'max_discount_amount' => $max_discount_amount,
        'usage_limit' => $usage_limit,
        'per_user_limit' => $per_user_limit,
        'valid_from' => $valid_from,
        'valid_until' => $valid_until,
        'applicable_to' => $existing['applicable_to'] ?? 'all',
        'applicable_ids' => $existing['applicable_ids'] ?? null,
        'is_active' => $existing['is_active'] ?? 1,
        'description' => $description
    ];

    return $discount_obj->update_discount_code($discount_id, $data);
}

/**
 * Toggle discount code active status
 *
 * @param int $discount_id
 * @param bool $is_active
 * @return bool
 */
function toggle_discount_status_ctr($discount_id, $is_active)
{
    $sql = "UPDATE tl_discount_codes SET is_active = ? WHERE discount_id = ?";

    $discount = new Discount();
    $conn = $discount->db_conn();
    $stmt = $conn->prepare($sql);
    $active_value = $is_active ? 1 : 0;
    $stmt->bind_param("ii", $active_value, $discount_id);

    return $stmt->execute();
}

/**
 * Delete discount code
 *
 * @param int $discount_id
 * @return bool
 */
function delete_discount_code_ctr($discount_id)
{
    $discount = new Discount();
    return $discount->delete_discount_code($discount_id);
}
?>
