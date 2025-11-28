<?php
// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

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
$festival_id = isset($_POST['festival_id']) && $_POST['festival_id'] !== '' ? (int)$_POST['festival_id'] : null;

// Handle available regions
$available_regions = null;
if (isset($_POST['regions']) && is_array($_POST['regions'])) {
    $available_regions = json_encode($_POST['regions']);
}

// Handle service image uploads
$service_images = null;
if (isset($_FILES['service_images']) && !empty($_FILES['service_images']['name'][0])) {
    $uploaded_images = handleImageUploads($_FILES['service_images'], $provider_id);
    if (!empty($uploaded_images)) {
        $service_images = json_encode($uploaded_images);
    }
}

/**
 * Handle multiple image uploads
 * @param array $files - $_FILES array
 * @param int $provider_id
 * @return array - Array of uploaded file paths
 */
function handleImageUploads($files, $provider_id) {
    $uploaded_paths = [];
    $max_files = 5;
    $max_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    // Create upload directory if it doesn't exist
    $upload_dir = '../uploads/services/' . $provider_id . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Process each file
    $file_count = is_array($files['name']) ? count($files['name']) : 1;
    $file_count = min($file_count, $max_files);

    for ($i = 0; $i < $file_count; $i++) {
        // Get file info
        $file_name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $file_tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $file_size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
        $file_type = is_array($files['type']) ? $files['type'][$i] : $files['type'];
        $file_error = is_array($files['error']) ? $files['error'][$i] : $files['error'];

        // Skip if no file or error
        if ($file_error !== UPLOAD_ERR_OK || empty($file_name)) {
            continue;
        }

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            continue; // Skip invalid file types
        }

        // Validate file size
        if ($file_size > $max_size) {
            continue; // Skip files that are too large
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_filename = uniqid('service_' . $provider_id . '_', true) . '.' . $extension;
        $destination = $upload_dir . $new_filename;

        // Try to resize and optimize image
        $upload_success = false;

        // First try GD resize
        if (function_exists('imagecreatefromjpeg') && resizeImage($file_tmp, $destination, 1200, 800, 85)) {
            $upload_success = true;
        }
        // Fallback: just move the uploaded file without resizing
        elseif (move_uploaded_file($file_tmp, $destination)) {
            $upload_success = true;
        }

        if ($upload_success) {
            // Store relative path (from project root)
            $relative_path = 'uploads/services/' . $provider_id . '/' . $new_filename;
            $uploaded_paths[] = $relative_path;

            // Try to create thumbnail (optional, don't fail if it doesn't work)
            if (function_exists('imagecreatefromjpeg')) {
                $thumb_dir = $upload_dir . 'thumbs/';
                if (!file_exists($thumb_dir)) {
                    @mkdir($thumb_dir, 0755, true);
                }
                $thumb_destination = $thumb_dir . 'thumb_' . $new_filename;
                @resizeImage($destination, $thumb_destination, 400, 300, 80);
            }
        }
    }

    return $uploaded_paths;
}

/**
 * Resize and optimize image
 * @param string $source - Source file path
 * @param string $destination - Destination file path
 * @param int $max_width - Maximum width
 * @param int $max_height - Maximum height
 * @param int $quality - JPEG quality (1-100)
 * @return bool - Success status
 */
function resizeImage($source, $destination, $max_width, $max_height, $quality = 85) {
    // Get image info
    $image_info = getimagesize($source);
    if ($image_info === false) {
        return false;
    }

    list($orig_width, $orig_height, $image_type) = $image_info;

    // Load image based on type
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    if ($image === false) {
        return false;
    }

    // Calculate new dimensions
    $ratio = min($max_width / $orig_width, $max_height / $orig_height);

    // Don't upscale images
    if ($ratio > 1) {
        $ratio = 1;
    }

    $new_width = round($orig_width * $ratio);
    $new_height = round($orig_height * $ratio);

    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency for PNG and WEBP
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_WEBP) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }

    // Resize
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);

    // Save image
    $success = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($new_image, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($new_image, $destination, 9);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($new_image, $destination, $quality);
            break;
    }

    // Free memory
    imagedestroy($image);
    imagedestroy($new_image);

    return $success;
}

// Validate required fields
if (empty($service_title) || empty($service_description) || $category_id <= 0 || $base_price <= 0 || empty($service_location)) {
    $_SESSION['error'] = "Please fill in all required fields";
    header("Location: ../admin/add_service.php");
    exit();
}

// Add service to database
try {
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
        $service_images,
        $festival_id
    );

    if ($service_id) {
        $_SESSION['success'] = "Service added successfully! It's now live and visible to tourists.";
        header("Location: ../admin/manage_services.php");
    } else {
        $_SESSION['error'] = "Failed to add service. Please try again.";
        header("Location: ../admin/add_service.php");
    }
} catch (Throwable $e) {
    // Display detailed error instead of blank page
    echo "<!DOCTYPE html><html><head><title>Service Creation Error</title>";
    echo "<style>body{font-family:monospace;padding:40px;background:#1a1a1a;color:#ff6b6b;}</style></head><body>";
    echo "<h1>🔴 Error Creating Service</h1>";
    echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<hr><h3>Form Data Received:</h3>";
    echo "<pre>";
    echo "Provider ID: $provider_id\n";
    echo "Category ID: $category_id\n";
    echo "Title: $service_title\n";
    echo "Description: " . substr($service_description, 0, 100) . "...\n";
    echo "Price: $base_price\n";
    echo "Location: $service_location\n";
    echo "Images: " . ($service_images ? 'YES' : 'NO') . "\n";
    echo "</pre>";
    echo "<hr><p><a href='../admin/add_service.php'>← Back to Add Service</a></p>";
    echo "</body></html>";
    exit();
}
exit();
?>
