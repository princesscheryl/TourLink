<?php
/**
 * Debug Script: Check service images in database
 * This will show you what's stored for each service
 */

require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
require_once '../classes/service_class.php';
require_once '../settings/db_class.php';

// Check if user is logged in and is a provider
if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    die("You must be a provider to run this script");
}

$service_class = new Service();
$services = $service_class->get_services_by_provider($provider['provider_id']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Service Images</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2d6a4f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        .service-row { background: #f8f9fa; }
        .image-data { font-family: monospace; font-size: 0.85em; max-width: 400px; word-break: break-all; }
        .json-valid { color: #28a745; }
        .json-invalid { color: #dc3545; }
        .image-preview { max-width: 100px; max-height: 100px; margin: 5px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Service Images Debug</h1>
        <p>This shows what's stored in the database for each service's images.</p>
        
        <table>
            <thead>
                <tr>
                    <th>Service ID</th>
                    <th>Service Title</th>
                    <th>Raw Data</th>
                    <th>JSON Valid?</th>
                    <th>Decoded Images</th>
                    <th>Image URLs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): 
                    $raw_data = $service['service_images'] ?? 'NULL';
                    $json_error = json_last_error();
                    $images = json_decode($raw_data, true);
                    $json_valid = (json_last_error() === JSON_ERROR_NONE);
                    $image_count = is_array($images) ? count($images) : 0;
                ?>
                    <tr class="service-row">
                        <td><strong><?php echo $service['service_id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($service['service_title']); ?></td>
                        <td class="image-data"><?php echo htmlspecialchars(substr($raw_data, 0, 100)); ?><?php echo strlen($raw_data) > 100 ? '...' : ''; ?></td>
                        <td class="<?php echo $json_valid ? 'json-valid' : 'json-invalid'; ?>">
                            <?php echo $json_valid ? '✓ Valid' : '✗ Invalid (' . json_last_error_msg() . ')'; ?>
                        </td>
                        <td>
                            <?php if ($json_valid && is_array($images)): ?>
                                <strong><?php echo $image_count; ?></strong> image(s)
                                <ul style="margin: 5px 0; padding-left: 20px; font-size: 0.85em;">
                                    <?php foreach ($images as $idx => $img): ?>
                                        <li><?php echo htmlspecialchars(substr($img, 0, 60)); ?><?php echo strlen($img) > 60 ? '...' : ''; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif (!empty($raw_data) && !$json_valid): ?>
                                <span style="color: #dc3545;">Not a valid JSON array</span>
                            <?php else: ?>
                                <span style="color: #999;">No images</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($json_valid && is_array($images)): 
                                require_once '../classes/hosted_upload_class.php';
                                foreach ($images as $img): 
                                    if (!empty($img)):
                                        $img_url = HostedUpload::getImageUrl($img, '../');
                            ?>
                                <div style="margin: 5px 0;">
                                    <a href="<?php echo htmlspecialchars($img_url); ?>" target="_blank" style="font-size: 0.8em; color: #0066cc;">
                                        <?php echo htmlspecialchars(substr($img_url, 0, 50)); ?>...
                                    </a>
                                    <br>
                                    <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Preview" class="image-preview" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span style="display:none; color: #dc3545; font-size: 0.8em;">Image not found</span>
                                </div>
                            <?php 
                                    endif;
                                endforeach;
                            endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 30px;">
            <a href="manage_services.php" style="background: #2d6a4f; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
                ← Back to Manage Services
            </a>
        </p>
        
        <p style="margin-top: 20px; color: #666; font-size: 0.9em;">
            <strong>Note:</strong> For security, please delete this file (debug_service_images.php) after use.
        </p>
    </div>
</body>
</html>

