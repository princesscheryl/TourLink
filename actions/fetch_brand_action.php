<?php
session_start();
require_once __DIR__.'/../controllers/brand_controller.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Fetch all brands
$brands = get_all_brands_ctr();

if ($brands !== false) {
    echo json_encode(['success' => true, 'data' => $brands]);
} else {
    echo json_encode(['success' => true, 'data' => []]);
}
?>
