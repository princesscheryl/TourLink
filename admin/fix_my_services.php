<?php
/**
 * Quick Fix: Update your services to be visible on browse page
 * Run this once to fix existing services, then delete this file
 */

require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
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

$db = new db_connection();
$db->db_connect();

// Fix services for this provider - set both statuses correctly
$update_sql = "UPDATE tl_services 
               SET service_status = 'active', 
                   availability_status = 'available' 
               WHERE provider_id = ? 
               AND (service_status != 'active' OR availability_status != 'available')";

$stmt = $db->db->prepare($update_sql);
$stmt->bind_param("i", $provider['provider_id']);
$result = $stmt->execute();
$affected = $stmt->affected_rows;

// Get all services for display
$check_sql = "SELECT service_id, service_title, service_status, availability_status 
              FROM tl_services 
              WHERE provider_id = ? 
              ORDER BY date_created DESC";
$check_stmt = $db->db->prepare($check_sql);
$check_stmt->bind_param("i", $provider['provider_id']);
$check_stmt->execute();
$services = $check_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Service Status</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2d6a4f; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        .status-active { color: #28a745; font-weight: 600; }
        .status-pending { color: #ffc107; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Service Status Fix</h1>
        
        <?php if ($result): ?>
            <div class="success">
                <strong>✓ Success!</strong> Updated <?php echo $affected; ?> service(s) to active and available status.
            </div>
        <?php else: ?>
            <div style="color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 20px 0;">
                <strong>Error:</strong> <?php echo $db->db->error; ?>
            </div>
        <?php endif; ?>
        
        <h2>Your Services Status:</h2>
        <table>
            <thead>
                <tr>
                    <th>Service ID</th>
                    <th>Service Title</th>
                    <th>Service Status</th>
                    <th>Availability Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo $service['service_id']; ?></td>
                        <td><?php echo htmlspecialchars($service['service_title']); ?></td>
                        <td class="<?php echo $service['service_status'] === 'active' ? 'status-active' : 'status-pending'; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $service['service_status'])); ?>
                        </td>
                        <td class="<?php echo $service['availability_status'] === 'available' ? 'status-active' : ''; ?>">
                            <?php echo ucfirst($service['availability_status']); ?>
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
            <strong>Note:</strong> For security, please delete this file (fix_my_services.php) after use.
        </p>
    </div>
</body>
</html>

