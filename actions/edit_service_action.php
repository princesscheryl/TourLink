<?php
session_start();
require_once '../classes/service_class.php';
require_once '../classes/service_provider_class.php';
require_once '../classes/hosted_upload_class.php';

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

$upload_error_log = [];

if (!empty($_FILES['service_images']['name'][0])) {
    $new_images = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB

    foreach ($_FILES['service_images']['tmp_name'] as $key => $tmp_name) {
        $upload_error = $_FILES['service_images']['error'][$key];
        $original_name = $_FILES['service_images']['name'][$key];

        // Skip empty entries
        if (empty($original_name) || $upload_error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($upload_error === UPLOAD_ERR_OK) {
            // Use finfo for proper MIME detection (more reliable than $_FILES['type'])
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);

            if (!in_array($mime_type, $allowed_types)) {
                $upload_error_log[] = "Invalid file type for $original_name";
                continue;
            }

            // Check file size
            $file_size = $_FILES['service_images']['size'][$key];
            if ($file_size > $max_size) {
                $upload_error_log[] = "File too large: $original_name";
                continue;
            }

            // Upload to hosted service
            $upload_result = HostedUpload::uploadFile($tmp_name, $original_name);
            
            if ($upload_result['success']) {
                $new_images[] = $upload_result['url'];
            } else {
                $upload_error_log[] = "Failed to upload: $original_name - " . $upload_result['message'];
            }
        } else {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File too large',
                UPLOAD_ERR_FORM_SIZE => 'File too large',
                UPLOAD_ERR_PARTIAL => 'Partial upload',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error',
                UPLOAD_ERR_CANT_WRITE => 'Server error',
                UPLOAD_ERR_EXTENSION => 'File type blocked'
            ];
            $upload_error_log[] = ($error_messages[$upload_error] ?? 'Upload failed') . ": $original_name";
        }
    }

    if (!empty($new_images)) {
        // Delete old images (only if they're local files, not hosted URLs)
        foreach ($service_images as $old_img) {
            // Only try to delete local files
            if (!HostedUpload::isHostedUrl($old_img)) {
                $old_path = strpos($old_img, 'uploads/') === 0 ? '../' . $old_img : '../uploads/services/' . $old_img;
                if (file_exists($old_path)) {
                    @unlink($old_path);
                }
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
    if (!empty($upload_error_log)) {
        $_SESSION['warning'] = 'Service updated, but some images could not be uploaded: ' . implode(', ', $upload_error_log);
    } else {
        $_SESSION['success'] = 'Service updated successfully';
    }
} else {
    $_SESSION['error'] = 'Failed to update service. Please try again.';
}

header("Location: ../admin/manage_services.php");
exit();
?>
