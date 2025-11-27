<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/discount_controller.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['discount_id', 'code', 'discount_type', 'discount_value', 'valid_from', 'valid_until'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || trim($input[$field]) === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required field: ' . $field
        ]);
        exit();
    }
}

try {
    $discount_id = intval($input['discount_id']);
    $code = strtoupper(trim($input['code']));
    $discount_type = trim($input['discount_type']);
    $discount_value = floatval($input['discount_value']);
    $min_booking_amount = isset($input['min_booking_amount']) ? floatval($input['min_booking_amount']) : 0;
    $max_discount_amount = isset($input['max_discount_amount']) && $input['max_discount_amount'] !== '' ? floatval($input['max_discount_amount']) : null;
    $usage_limit = isset($input['usage_limit']) && $input['usage_limit'] !== '' ? intval($input['usage_limit']) : null;
    $per_user_limit = isset($input['per_user_limit']) ? intval($input['per_user_limit']) : 1;
    $valid_from = trim($input['valid_from']);
    $valid_until = trim($input['valid_until']);
    $description = isset($input['description']) ? trim($input['description']) : null;

    // Validate discount code format
    if (!preg_match('/^[A-Z0-9]+$/', $code)) {
        throw new Exception('Discount code must contain only uppercase letters and numbers');
    }

    // Validate discount type
    if (!in_array($discount_type, ['percentage', 'fixed'])) {
        throw new Exception('Invalid discount type');
    }

    // Validate discount value
    if ($discount_value <= 0) {
        throw new Exception('Discount value must be greater than 0');
    }

    if ($discount_type === 'percentage' && $discount_value > 100) {
        throw new Exception('Percentage discount cannot exceed 100%');
    }

    // Validate dates
    if (strtotime($valid_from) > strtotime($valid_until)) {
        throw new Exception('Valid from date must be before valid until date');
    }

    // Update discount
    $result = update_discount_code_ctr(
        $discount_id,
        $code,
        $discount_type,
        $discount_value,
        $min_booking_amount,
        $max_discount_amount,
        $usage_limit,
        $per_user_limit,
        $valid_from,
        $valid_until,
        $description
    );

    if ($result) {
        error_log("Discount code updated - ID: $discount_id, Code: $code, Updated by: " . $_SESSION['user_id']);

        echo json_encode([
            'status' => 'success',
            'message' => 'Discount code updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update discount code');
    }

} catch (Exception $e) {
    error_log("Discount update error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
