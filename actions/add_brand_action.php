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
$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';

// Validate input
if (empty($brand_name)) {
    echo json_encode(['success' => false, 'message' => 'Brand name is required']);
    exit();
}

// Add brand
$brand_id = add_brand_ctr($brand_name);

if ($brand_id) {
    echo json_encode(['success' => true, 'message' => 'Brand added successfully', 'brand_id' => $brand_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Brand with this name already exists']);
}
?>
