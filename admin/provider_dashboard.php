<?php
require_once '../settings/core.php';
require_once '../classes/service_class.php';
require_once '../classes/booking_class.php';
require_once '../classes/service_provider_class.php';

// Check if user is provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header('Location: ../login/login.php');
    exit();
}

// Load provider data
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    header('Location: ../login/login.php');
    exit();
}

// Get provider's services
$service_class = new Service();
$services = $service_class->get_services_by_provider($provider['provider_id']);

// Get provider's bookings
$booking_class = new Booking();
$bookings = $booking_class->get_provider_bookings($provider['provider_id']);
$statistics = $booking_class->get_provider_statistics($provider['provider_id']);

// Count services by status
$active_services = 0;
$pending_services = 0;
if ($services) {
    foreach ($services as $service) {
        if ($service['service_status'] === 'active') $active_services++;
        elseif ($service['service_status'] === 'pending') $pending_services++;
    }
}

// Count bookings by status
$pending_bookings = array_filter($bookings ?: [], fn($b) => $b['booking_status'] === 'pending');
$confirmed_bookings = array_filter($bookings ?: [], fn($b) => $b['booking_status'] === 'confirmed');
$completed_bookings = array_filter($bookings ?: [], fn($b) => $b['booking_status'] === 'completed');

// Check if provider has active premium subscription
$db_temp = new db_connection();
$db_temp->db_connect();
$premium_check = $db_temp->db->prepare("
    SELECT premium_listing_id FROM tl_premium_listings
    WHERE provider_id = ?
    AND status = 'active'
    AND end_date >= CURDATE()
    LIMIT 1
");
$premium_check->bind_param("i", $provider['provider_id']);
$premium_check->execute();
$has_premium = $premium_check->get_result()->num_rows > 0;

// Calculate profile completion
$completion = 40;
if (!empty($provider['business_name'])) $completion += 15;
if (!empty($provider['years_of_experience'])) $completion += 15;
if (!empty($provider['languages_spoken'])) $completion += 10;
if (!empty($provider['business_registration_number'])) $completion += 10;
if ($provider['verification_status'] === 'verified') $completion += 10;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TourLink Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <link href="../css/provider_sidebar.css" rel="stylesheet">
    <link href="../css/provider_dashboard.css" rel="stylesheet">
</head>
<body>
    <?php
    // Set current page for sidebar highlighting
    $current_page = 'dashboard';
    // Include reusable sidebar component
    include '../includes/provider_sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>. Here's what's happening.</p>
            </div>
            <div class="header-actions">
                <a href="add_service.php" class="btn-add-service">
                    <i class="fa fa-plus"></i> Add Service
                </a>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Pending Bookings Alert -->
            <?php if (count($pending_bookings) > 0): ?>
            <div class="alert-banner">
                <div class="alert-banner-content">
                    <h3><i class="fa fa-bell"></i> You have <?php echo count($pending_bookings); ?> pending booking<?php echo count($pending_bookings) > 1 ? 's' : ''; ?></h3>
                    <p>Review and respond to booking requests to keep customers happy.</p>
                </div>
                <a href="../view/provider/manage_bookings.php?filter=pending" class="btn">View Pending</a>
            </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon teal"><i class="fa fa-briefcase"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $active_services; ?></div>
                    <div class="stat-label">Active Services</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon blue"><i class="fa fa-calendar-check"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $statistics['total_bookings'] ?: 0; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon amber"><i class="fa fa-clock"></i></div>
                    </div>
                    <div class="stat-value"><?php echo count($pending_bookings); ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon green"><i class="fa fa-wallet"></i></div>
                    </div>
                    <div class="stat-value">GHS <?php echo number_format($statistics['total_earnings'] ?: 0, 0); ?></div>
                    <div class="stat-label">Total Earnings</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <div>
                    <!-- Recent Bookings -->
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2>Recent Bookings</h2>
                            <a href="../view/provider/manage_bookings.php" class="btn-link">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if ($bookings && count($bookings) > 0): ?>
                                <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                                    <?php $initials = strtoupper(substr($booking['tourist_first_name'], 0, 1) . substr($booking['tourist_last_name'], 0, 1)); ?>
                                    <div class="booking-item">
                                        <div class="booking-avatar"><?php echo $initials; ?></div>
                                        <div class="booking-info">
                                            <div class="booking-title"><?php echo htmlspecialchars($booking['service_title']); ?></div>
                                            <div class="booking-meta">
                                                <?php echo htmlspecialchars($booking['tourist_first_name'] . ' ' . $booking['tourist_last_name']); ?>
                                                &bull; <?php echo date('M d, Y', strtotime($booking['service_date'])); ?>
                                            </div>
                                        </div>
                                        <span class="booking-status <?php echo $booking['booking_status']; ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                        <div class="booking-amount">GHS <?php echo number_format($booking['provider_earnings'], 0); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fa fa-calendar-alt"></i></div>
                                    <h4>No bookings yet</h4>
                                    <p>When customers book your services, they'll appear here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Your Services -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Your Services</h2>
                            <a href="manage_services.php" class="btn-link">Manage All</a>
                        </div>
                        <div class="card-body">
                            <?php if ($services && count($services) > 0): ?>
                                <?php foreach (array_slice($services, 0, 4) as $service): ?>
                                    <?php
                                    $image_url = null;
                                    if (!empty($service['service_images'])) {
                                        $images = json_decode($service['service_images'], true);
                                        if (is_array($images) && count($images) > 0) {
                                            $image_url = '../' . $images[0];
                                        }
                                    }
                                    ?>
                                    <div class="service-item">
                                        <div class="service-thumb">
                                            <?php if ($image_url && file_exists($image_url)): ?>
                                                <img src="<?php echo htmlspecialchars($image_url); ?>" alt="">
                                            <?php else: ?>
                                                <i class="fa fa-image"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="service-info">
                                            <div class="service-title"><?php echo htmlspecialchars($service['service_title']); ?></div>
                                            <div class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></div>
                                        </div>
                                        <span class="service-status <?php echo $service['service_status']; ?>">
                                            <?php echo ucfirst($service['service_status']); ?>
                                        </span>
                                        <div class="service-price">GHS <?php echo number_format($service['base_price'], 0); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fa fa-box-open"></i></div>
                                    <h4>No services yet</h4>
                                    <p>Add your first service to start receiving bookings.</p>
                                    <a href="add_service.php" class="btn">Add Service</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div>
                    <!-- Profile Completion -->
                    <div class="card" style="margin-bottom: 24px;">
                        <div class="card-header">
                            <h2>Profile Status</h2>
                        </div>
                        <div class="card-body">
                            <div class="completion-ring">
                                <svg width="100" height="100" viewBox="0 0 100 100">
                                    <circle class="bg" cx="50" cy="50" r="42"/>
                                    <circle class="progress" cx="50" cy="50" r="42"
                                            stroke-dasharray="<?php echo (264 * $completion / 100); ?> 264"/>
                                </svg>
                                <div class="completion-percent"><?php echo $completion; ?>%</div>
                            </div>
                            <div class="completion-label">Profile Completion</div>

                            <div class="completion-item">
                                <i class="fa fa-<?php echo !empty($provider['business_name']) ? 'check-circle complete' : 'circle incomplete'; ?>"></i>
                                Business Name
                            </div>
                            <div class="completion-item">
                                <i class="fa fa-<?php echo !empty($provider['years_of_experience']) ? 'check-circle complete' : 'circle incomplete'; ?>"></i>
                                Years of Experience
                            </div>
                            <div class="completion-item">
                                <i class="fa fa-<?php echo !empty($provider['languages_spoken']) ? 'check-circle complete' : 'circle incomplete'; ?>"></i>
                                Languages Spoken
                            </div>
                            <div class="completion-item">
                                <i class="fa fa-<?php echo $provider['verification_status'] === 'verified' ? 'check-circle complete' : 'circle incomplete'; ?>"></i>
                                Verification Status
                            </div>

                            <?php if ($completion < 100): ?>
                                <a href="provider_profile.php" class="btn" style="width: 100%; margin-top: 16px; text-align: center; display: block; background: var(--primary); color: white; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                    Complete Profile
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Quick Actions</h2>
                        </div>
                        <div class="card-body" style="padding: 16px;">
                            <a href="add_service.php" class="quick-action">
                                <div class="quick-action-icon teal"><i class="fa fa-plus"></i></div>
                                <div class="quick-action-text">
                                    <div class="quick-action-title">Add New Service</div>
                                    <div class="quick-action-desc">List a new tour or experience</div>
                                </div>
                            </a>
                            <a href="../view/provider/manage_bookings.php" class="quick-action">
                                <div class="quick-action-icon amber"><i class="fa fa-calendar-check"></i></div>
                                <div class="quick-action-text">
                                    <div class="quick-action-title">Manage Bookings</div>
                                    <div class="quick-action-desc">Review and respond to requests</div>
                                </div>
                            </a>
                            <a href="provider_profile.php" class="quick-action">
                                <div class="quick-action-icon blue"><i class="fa fa-user-edit"></i></div>
                                <div class="quick-action-text">
                                    <div class="quick-action-title">Edit Business Profile</div>
                                    <div class="quick-action-desc">Update your information</div>
                                </div>
                            </a>
                            <a href="../view/all_services.php" class="quick-action">
                                <div class="quick-action-icon green"><i class="fa fa-search"></i></div>
                                <div class="quick-action-text">
                                    <div class="quick-action-title">Browse Marketplace</div>
                                    <div class="quick-action-desc">See what others are offering</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
