<?php
session_start();
require_once '../classes/service_class.php';
require_once '../classes/service_provider_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    header("Location: ../admin/become_provider.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/manage_services.php");
    exit();
}

$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;

// Verify ownership
$service_class = new Service();
$existing_service = $service_class->get_service_by_id($service_id);

if (!$existing_service || $existing_service['provider_id'] != $provider['provider_id']) {
    $_SESSION['error'] = 'Service not found or access denied';
    header("Location: ../admin/manage_services.php");
    exit();
}

// Get form data
$service_title = trim($_POST['service_title'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$service_description = trim($_POST['service_description'] ?? '');
$base_price = (float)($_POST['base_price'] ?? 0);
$pricing_unit = trim($_POST['pricing_unit'] ?? 'per_hour');
$service_location = trim($_POST['service_location'] ?? '');
$regions = $_POST['regions'] ?? [];
$max_capacity = !empty($_POST['max_capacity']) ? (int)$_POST['max_capacity'] : null;

// Validate required fields
if (empty($service_title) || empty($category_id) || empty($service_description) ||
    empty($base_price) || empty($service_location)) {
    $_SESSION['error'] = 'Please fill in all required fields';
    header("Location: ../admin/edit_service.php?id=" . $service_id);
    exit();
}

// Handle image uploads
$service_images = json_decode($existing_service['service_images'], true) ?: [];
$provider_id = $provider['provider_id'];

if (!empty($_FILES['service_images']['name'][0])) {
    $upload_dir = '../uploads/services/' . $provider_id . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $new_images = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

    foreach ($_FILES['service_images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['service_images']['error'][$key] === UPLOAD_ERR_OK) {
            $file_type = $_FILES['service_images']['type'][$key];

            if (!in_array($file_type, $allowed_types)) {
                continue;
            }

            $ext = strtolower(pathinfo($_FILES['service_images']['name'][$key], PATHINFO_EXTENSION));
            $filename = uniqid('service_' . $provider_id . '_') . '.' . $ext;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($tmp_name, $filepath)) {
                // Store relative path (consistent with add_service_action.php)
                $relative_path = 'uploads/services/' . $provider_id . '/' . $filename;
                $new_images[] = $relative_path;
            }
        }
    }

    if (!empty($new_images)) {
        // Delete old images
        foreach ($service_images as $old_img) {
            // Handle both old format (filename only) and new format (full path)
            $old_path = strpos($old_img, 'uploads/') === 0 ? '../' . $old_img : '../uploads/services/' . $old_img;
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }
        $service_images = $new_images;
    }
}

// Prepare update data as associative array
$update_data = [
    'service_title' => $service_title,
    'category_id' => $category_id,
    'service_description' => $service_description,
    'base_price' => $base_price,
    'pricing_unit' => $pricing_unit,
    'service_location' => $service_location,
    'available_regions' => json_encode($regions),
    'max_capacity' => $max_capacity,
    'service_images' => json_encode($service_images)
];

// Update service
$result = $service_class->update_service($service_id, $update_data);

if ($result) {
    $_SESSION['success'] = 'Service updated successfully';
} else {
    $_SESSION['error'] = 'Failed to update service. Please try again.';
}

header("Location: ../admin/manage_services.php");
exit();
?>
