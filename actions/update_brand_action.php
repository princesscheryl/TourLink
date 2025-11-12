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

// Get data from POST
$brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';

// Validate input
if ($brand_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid brand ID']);
    exit();
}

if (empty($brand_name)) {
    echo json_encode(['success' => false, 'message' => 'Brand name is required']);
    exit();
}

// Update brand
$updated = update_brand_ctr($brand_id, $brand_name);

if ($updated) {
    echo json_encode(['success' => true, 'message' => 'Brand updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Brand with this name already exists or update failed']);
}
?>
