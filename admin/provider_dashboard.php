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

        /* Profile Completion Banner */
        .profile-banner {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #ffd700;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.2);
        }

        .profile-banner h5 {
            color: #856404;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .profile-banner p {
            color: #856404;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .profile-banner ul {
            margin: 10px 0 15px 20px;
            color: #856404;
        }

        .profile-banner li {
            margin-bottom: 5px;
        }

        .profile-banner .btn {
            background: #2d6a4f;
            border: none;
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .profile-banner .btn:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        /* Dashboard Card */
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .dashboard-card h2 {
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .dashboard-card h4, .dashboard-card h5 {
            font-weight: 700;
            color: #1b4332;
        }

        /* Stat Cards */
        .stat-card {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 8px 0;
        }

        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-weight: 500;
        }

        .stat-card-alt1 {
            background: linear-gradient(135deg, #52796f 0%, #354f52 100%);
        }

        .stat-card-alt2 {
            background: linear-gradient(135deg, #74c69d 0%, #52b788 100%);
        }

        .stat-card-alt3 {
            background: linear-gradient(135deg, #95d5b2 0%, #74c69d 100%);
        }

        /* Verification Badge */
        .verification-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .verified {
            background: #d4edda;
            color: #155724;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        /* Service Items */
        .service-item {
            border-bottom: 1px solid #e9ecef;
            padding: 16px 0;
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-item h6 {
            font-weight: 700;
            color: #1a1a1a;
        }

        /* Quick Action Buttons */
        .quick-action-btn {
            display: block;
            width: 100%;
            padding: 16px;
            margin-bottom: 12px;
            background: white;
            border: 2px solid #2d6a4f;
            color: #2d6a4f;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            background: #2d6a4f;
            color: white;
            transform: translateX(5px);
        }

        .quick-action-btn i {
            margin-right: 8px;
        }

        /* Buttons */
        .btn-primary {
            background: #2d6a4f;
            border: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            border: 2px solid #2d6a4f;
            color: #2d6a4f;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-primary:hover {
            background: #2d6a4f;
            color: white;
            border-color: #2d6a4f;
        }

        /* Table */
        .table thead {
            background: #f8f9fa;
        }

        .table th {
            font-weight: 700;
            color: #1b4332;
            border-bottom: 2px solid #2d6a4f;
        }

        /* Progress Bar */
        .progress {
            height: 10px;
            border-radius: 5px;
            background: #e9ecef;
        }

        .progress-bar {
            background: #2d6a4f;
            border-radius: 5px;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 6px 12px;
        }

        .bg-success {
            background: #2d6a4f !important;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
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

    <div class="main-container">
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

        <!-- Profile Completion Banner -->
        <?php
        $missing_fields = [];
        if (empty($provider['languages_spoken'])) {
            $missing_fields[] = 'Languages Spoken';
        }
        if (empty($provider['years_of_experience'])) {
            $missing_fields[] = 'Years of Experience';
        }
        if (empty($provider['business_registration_number'])) {
            $missing_fields[] = 'Business Registration Number';
        }

        if (count($missing_fields) > 0):
        ?>
        <div class="profile-banner">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h5><i class="fa fa-exclamation-triangle"></i> Complete Your Profile</h5>
                    <p class="mb-2">Your profile is incomplete. Please add the following information to improve your visibility and credibility:</p>
                    <ul>
                        <?php foreach ($missing_fields as $field): ?>
                            <li><?php echo htmlspecialchars($field); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="provider_profile.php" class="btn">
                        <i class="fa fa-edit"></i> Update Profile
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; color: #856404; opacity: 0.7;"></button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3><?php echo $active_services; ?></h3>
                    <p>Active Services</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-card-alt1">
                    <h3><?php echo $statistics['total_bookings'] ?: 0; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-card-alt2">
                    <h3><?php echo $statistics['pending_bookings'] ?: 0; ?></h3>
                    <p>Pending Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-card-alt3">
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
                        <div class="empty-state">
                            <i class="fa fa-box-open fa-3x mb-3"></i>
                            <p>You haven't added any services yet.</p>
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
