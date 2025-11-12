<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../classes/service_provider_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    $_SESSION['error'] = "You must be a registered provider to add services";
    header("Location: ../admin/become_provider.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/add_service.php");
    exit();
}

// Get and sanitize form data
$provider_id = $provider['provider_id'];
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$service_title = isset($_POST['service_title']) ? trim($_POST['service_title']) : '';
$service_description = isset($_POST['service_description']) ? trim($_POST['service_description']) : '';
$base_price = isset($_POST['base_price']) ? (float)$_POST['base_price'] : 0;
$pricing_unit = isset($_POST['pricing_unit']) ? trim($_POST['pricing_unit']) : 'per_person';
$service_location = isset($_POST['service_location']) ? trim($_POST['service_location']) : '';
$max_capacity = isset($_POST['max_capacity']) && $_POST['max_capacity'] !== '' ? (int)$_POST['max_capacity'] : null;

// Handle available regions
$available_regions = null;
if (isset($_POST['regions']) && is_array($_POST['regions'])) {
    $available_regions = json_encode($_POST['regions']);
}

// Handle service images (URLs)
$service_images = null;
if (isset($_POST['service_images']) && !empty(trim($_POST['service_images']))) {
    $image_urls = array_filter(array_map('trim', explode("\n", $_POST['service_images'])));
    if (!empty($image_urls)) {
        $service_images = json_encode($image_urls);
    }
}

// Validate required fields
if (empty($service_title) || empty($service_description) || $category_id <= 0 || $base_price <= 0 || empty($service_location)) {
    $_SESSION['error'] = "Please fill in all required fields";
    header("Location: ../admin/add_service.php");
    exit();
}

// Add service to database
$service_id = add_service_ctr(
    $provider_id,
    $category_id,
    $service_title,
    $service_description,
    $base_price,
    $pricing_unit,
    $service_location,
    $available_regions,
    $max_capacity,
    $service_images
);

if ($service_id) {
    $_SESSION['success'] = "Service added successfully! It's pending approval.";
    header("Location: ../admin/manage_services.php");
} else {
    $_SESSION['error'] = "Failed to add service. Please try again.";
    header("Location: ../admin/add_service.php");
}
exit();
?>
