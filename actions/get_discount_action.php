<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/discount_controller.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Get discount ID from query parameter
$discount_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$discount_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Discount ID is required'
    ]);
    exit();
}

try {
    // Get discount details
    $discount = get_discount_by_id_ctr($discount_id);

    if ($discount) {
        echo json_encode([
            'status' => 'success',
            'data' => $discount
        ]);
    } else {
        throw new Exception('Discount code not found');
    }

} catch (Exception $e) {
    error_log("Get discount error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
