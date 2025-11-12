<?php
session_start();
require_once __DIR__.'/../controllers/brand_controller.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SESSION['user_role'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get brand ID from POST
$brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;

// Validate input
if ($brand_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid brand ID']);
    exit();
}

// Delete brand
$deleted = delete_brand_ctr($brand_id);

if ($deleted) {
    echo json_encode(['success' => true, 'message' => 'Brand deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete brand. It may be associated with products.']);
}
?>
