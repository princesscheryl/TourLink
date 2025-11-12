<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
require_once '../classes/service_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    header("Location: become_provider.php");
    exit();
}

// Get provider's services
$service_class = new Service();
$services = $service_class->get_services_by_provider($provider['provider_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="provider_dashboard.php" class="nav-link">Dashboard</a>
                <a href="manage_services.php" class="nav-link active">My Services</a>
                <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Services</h2>
            <a href="add_service.php" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add New Service
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($services && count($services) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($service['service_title']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($service['service_location']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($service['category_name']); ?></td>
                            <td>
                                <strong>GHS <?php echo number_format($service['base_price'], 2); ?></strong>
                                <br><small class="text-muted"><?php echo str_replace('_', ' ', $service['pricing_unit']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?php
                                    echo $service['service_status'] === 'active' ? 'success' :
                                         ($service['service_status'] === 'pending_approval' ? 'warning' : 'secondary');
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $service['service_status'])); ?>
                                </span>
                            </td>
                            <td><?php echo $service['views_count']; ?></td>
                            <td>
                                <a href="../view/single_service.php?id=<?php echo $service['service_id']; ?>"
                                   class="btn btn-sm btn-info" target="_blank">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="edit_service.php?id=<?php echo $service['service_id']; ?>"
                                   class="btn btn-sm btn-warning">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger"
                                        onclick="deleteService(<?php echo $service['service_id']; ?>)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa fa-box-open fa-3x text-muted mb-3"></i>
                <h4>No services yet</h4>
                <p class="text-muted">Start adding your tourism services to appear in search results</p>
                <a href="add_service.php" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Your First Service
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteService(serviceId) {
            if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
                window.location.href = '../actions/delete_service_action.php?id=' + serviceId;
            }
        }
    </script>
</body>
</html>
