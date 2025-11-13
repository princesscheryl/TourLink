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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Navigation */
        .main-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .nav-left, .nav-right {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2d6a4f;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logo:hover {
            color: #1b4332;
        }

        .logo-dot {
            color: #ffd700;
            font-size: 2rem;
        }

        .nav-link {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
            position: relative;
        }

        .nav-link:hover {
            color: #2d6a4f;
        }

        .nav-link.active {
            color: #2d6a4f;
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -23px;
            left: 0;
            right: 0;
            height: 3px;
            background: #2d6a4f;
        }

        .btn-nav {
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-nav-logout {
            background: #dc3545;
            color: white;
        }

        .btn-nav-logout:hover {
            background: #c82333;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 100px auto 60px;
            padding: 0 30px;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-weight: 800;
            color: #1a1a1a;
            margin: 0;
        }

        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: #f8f9fa;
        }

        .table th {
            font-weight: 700;
            color: #1b4332;
            border-bottom: 3px solid #2d6a4f;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }

        .table td strong {
            color: #1a1a1a;
            font-weight: 700;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .bg-success {
            background: #2d6a4f !important;
        }

        .bg-warning {
            background: #ffc107 !important;
            color: #000 !important;
        }

        /* Buttons */
        .btn-primary {
            background: #2d6a4f;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #1b4332;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
        }

        .btn-info {
            background: #17a2b8;
            border: none;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        .btn-warning {
            background: #ffc107;
            border: none;
            color: #000;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Empty State */
        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 80px 40px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        .empty-state i {
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 30px;
        }

        /* Responsive */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
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

    <div class="main-container">
        <div class="page-header">
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
            <div class="table-container">
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
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-box-open fa-3x mb-3"></i>
                <h4>No services yet</h4>
                <p>Start adding your tourism services to appear in search results</p>
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
