<?php
session_start();

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

// Check if file was uploaded
if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'No file was uploaded']);
    exit();
}

// Check for upload errors
if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $_FILES['product_image']['error']]);
    exit();
}

// Get user ID and product ID
$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$file_type = $_FILES['product_image']['type'];

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed']);
    exit();
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB in bytes
if ($_FILES['product_image']['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
    exit();
}

// Define upload directory structure: uploads/u{user_id}/p{product_id}/
$base_upload_dir = __DIR__ . '/../uploads';
$user_dir = $base_upload_dir . '/u' . $user_id;
$product_dir = $user_dir . '/p' . $product_id;

// Verify that uploading to the correct location
// All uploads must be inside the uploads/ directory
if (strpos(realpath($base_upload_dir), realpath(__DIR__ . '/../uploads')) !== 0 && realpath($base_upload_dir) !== realpath(__DIR__ . '/../uploads')) {
    echo json_encode(['success' => false, 'message' => 'Invalid upload directory']);
    exit();
}

// Create directories if they don't exist (only inside uploads/)
if (!file_exists($base_upload_dir)) {
    if (!mkdir($base_upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create uploads directory']);
        exit();
    }
}

if (!file_exists($user_dir)) {
    if (!mkdir($user_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create user directory']);
        exit();
    }
}

if (!file_exists($product_dir)) {
    if (!mkdir($product_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create product directory']);
        exit();
    }
}

// Generate unique filename
$file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
$file_name = 'image_' . time() . '_' . uniqid() . '.' . $file_extension;
$file_path = $product_dir . '/' . $file_name;

// Verify the final path is still inside uploads/ directory
$real_product_dir = realpath($product_dir);
$real_upload_dir = realpath($base_upload_dir);

if ($real_product_dir === false || strpos($real_product_dir, $real_upload_dir) !== 0) {
    echo json_encode(['success' => false, 'message' => 'Security violation: Attempted upload outside authorized directory']);
    exit();
}

// Move uploaded file
if (move_uploaded_file($_FILES['product_image']['tmp_name'], $file_path)) {
    // Return relative path for storing in database
    $relative_path = 'uploads/u' . $user_id . '/p' . $product_id . '/' . $file_name;
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'file_path' => $relative_path
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?>
