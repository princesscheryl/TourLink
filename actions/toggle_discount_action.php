<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/discount_controller.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$discount_id = isset($input['discount_id']) ? intval($input['discount_id']) : 0;
$is_active = isset($input['is_active']) ? (bool)$input['is_active'] : false;

if (!$discount_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Discount ID is required'
    ]);
    exit();
}

try {
    // Toggle discount active status
    $result = toggle_discount_status_ctr($discount_id, $is_active);

    if ($result) {
        $action = $is_active ? 'activated' : 'deactivated';
        error_log("Discount code $action - ID: $discount_id, By: " . $_SESSION['user_id']);

        echo json_encode([
            'status' => 'success',
            'message' => "Discount code has been $action successfully"
        ]);
    } else {
        throw new Exception('Failed to update discount status');
    }

} catch (Exception $e) {
    error_log("Toggle discount error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
