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
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../admin/manage_services.php");
    exit();
}

// Get service ID
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($service_id <= 0) {
    $_SESSION['error'] = "Invalid service ID";
    header("Location: ../admin/manage_services.php");
    exit();
}

// Verify service belongs to this provider
$service = get_service_by_id_ctr($service_id);

if (!$service || $service['provider_id'] != $provider['provider_id']) {
    $_SESSION['error'] = "You don't have permission to delete this service";
    header("Location: ../admin/manage_services.php");
    exit();
}

// Delete the service
if (delete_service_ctr($service_id)) {
    $_SESSION['success'] = "Service deleted successfully";
} else {
    $_SESSION['error'] = "Failed to delete service";
}

header("Location: ../admin/manage_services.php");
exit();
?>
