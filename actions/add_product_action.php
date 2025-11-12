<?php
session_start();
require_once __DIR__.'/../controllers/product_controller.php';

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
$cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;
$brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
$title = isset($_POST['product_title']) ? trim($_POST['product_title']) : '';
$price = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;
$desc = isset($_POST['product_desc']) ? trim($_POST['product_desc']) : '';
$keywords = isset($_POST['product_keywords']) ? trim($_POST['product_keywords']) : '';
$image_path = isset($_POST['product_image']) ? trim($_POST['product_image']) : '';

// Validate input
if ($cat_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid category']);
    exit();
}

if ($brand_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid brand']);
    exit();
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Product title is required']);
    exit();
}

if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Product price must be greater than 0']);
    exit();
}

// Add product
$product_id = add_product_ctr($cat_id, $brand_id, $title, $price, $desc, $image_path, $keywords);

if ($product_id) {
    echo json_encode(['success' => true, 'message' => 'Product added successfully', 'product_id' => $product_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product']);
}
?>
