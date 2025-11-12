<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
require_once '../classes/service_class.php';
require_once '../classes/booking_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    // Redirect to become a provider page
    header("Location: become_provider.php");
    exit();
}

// Get provider's services
$service_class = new Service();
$services = $service_class->get_services_by_provider($provider['provider_id']);

// Get provider's bookings
$booking_class = new Booking();
$bookings = $booking_class->get_provider_bookings($provider['provider_id']);
$statistics = $booking_class->get_provider_statistics($provider['provider_id']);

// Count active services
$active_services = 0;
if ($services) {
    foreach ($services as $service) {
        if ($service['service_status'] === 'active') {
            $active_services++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .stat-card h3 {
            font-size: 36px;
            font-weight: bold;
            margin: 0;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .verification-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .verified {
            background: #d4edda;
            color: #155724;
        }
        .pending {
            background: #fff3cd;
            color: #856404;
        }
        .service-item {
            border-bottom: 1px solid #eee;
            padding: 16px 0;
        }
        .service-item:last-child {
            border-bottom: none;
        }
        .quick-action-btn {
            display: block;
            width: 100%;
            padding: 16px;
            margin-bottom: 12px;
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="provider_dashboard.php" class="nav-link active">Dashboard</a>
                <a href="manage_services.php" class="nav-link">My Services</a>
                <a href="../index_tourlink.php" class="nav-link">View Site</a>
                <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <!-- Welcome Header -->
        <div class="dashboard-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Welcome back, <?php echo htmlspecialchars($provider['business_name'] ?: $_SESSION['user_name']); ?>!</h2>
                    <p class="text-muted mb-0">
                        <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($provider['region']); ?> |
                        <i class="fa fa-star"></i> Rating: <?php echo number_format($provider['average_rating'], 1); ?>/5.0 |
                        <span class="verification-badge <?php echo $provider['verification_status']; ?>">
                            <?php echo ucfirst($provider['verification_status']); ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add_service.php" class="btn btn-primary btn-lg">
                        <i class="fa fa-plus"></i> Add New Service
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $active_services; ?></h3>
                    <p>Active Services</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <h3><?php echo $statistics['total_bookings'] ?: 0; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                    <h3><?php echo $statistics['pending_bookings'] ?: 0; ?></h3>
                    <p>Pending Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <h3>GHS <?php echo number_format($statistics['total_earnings'] ?: 0, 2); ?></h3>
                    <p>Total Earnings</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Services -->
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Your Services</h4>
                        <a href="manage_services.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>

                    <?php if ($services && count($services) > 0): ?>
                        <?php foreach (array_slice($services, 0, 5) as $service): ?>
                        <div class="service-item">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($service['service_title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($service['category_name']); ?></small>
                                </div>
                                <div class="col-md-3">
                                    <strong>GHS <?php echo number_format($service['base_price'], 2); ?></strong>
                                    <br><small><?php echo htmlspecialchars($service['pricing_unit']); ?></small>
                                </div>
                                <div class="col-md-2 text-end">
                                    <span class="badge bg-<?php echo $service['service_status'] === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($service['service_status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">You haven't added any services yet.</p>
                            <a href="add_service.php" class="btn btn-primary">Add Your First Service</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Bookings -->
                <div class="dashboard-card">
                    <h4 class="mb-3">Recent Bookings</h4>

                    <?php if ($bookings && count($bookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ref</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars($booking['booking_reference']); ?></small></td>
                                        <td><?php echo htmlspecialchars($booking['service_title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($booking['service_date'])); ?></td>
                                        <td><strong>GHS <?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($booking['booking_status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No bookings yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5 class="mb-3">Quick Actions</h5>
                    <a href="add_service.php" class="quick-action-btn">
                        <i class="fa fa-plus-circle"></i> Add New Service
                    </a>
                    <a href="manage_services.php" class="quick-action-btn">
                        <i class="fa fa-list"></i> Manage Services
                    </a>
                    <a href="provider_profile.php" class="quick-action-btn">
                        <i class="fa fa-user-circle"></i> Edit Profile
                    </a>
                    <a href="bookings.php" class="quick-action-btn">
                        <i class="fa fa-calendar-check"></i> View All Bookings
                    </a>
                </div>

                <!-- Profile Completion -->
                <div class="dashboard-card">
                    <h5 class="mb-3">Profile Status</h5>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Profile Completion</span>
                            <span>
                                <?php
                                $completion = 50;
                                if ($provider['business_name']) $completion += 10;
                                if ($provider['years_of_experience']) $completion += 10;
                                if ($provider['languages_spoken']) $completion += 10;
                                if ($provider['verification_status'] === 'verified') $completion += 20;
                                echo $completion;
                                ?>%
                            </span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $completion; ?>%"></div>
                        </div>
                    </div>

                    <?php if ($provider['verification_status'] !== 'verified'): ?>
                    <div class="alert alert-warning">
                        <small><i class="fa fa-info-circle"></i> Complete verification to appear in search results</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
