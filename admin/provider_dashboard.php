<?php
require_once '../settings/core.php';
require_once '../classes/service_class.php';
require_once '../classes/booking_class.php';

$current_page = 'dashboard';
include '../includes/provider_sidebar.php';


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
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5a54;
            --primary-light: #14b8a6;
            --accent: #f59e0b;
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-main);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 12px;
        }

        .sidebar-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .sidebar-logo-dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            margin-top: 4px;
            margin-left: 1px;
        }

        .sidebar-logo-badge {
            background: var(--primary);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.15s;
            margin-bottom: 4px;
        }

        .nav-item:hover {
            background: #f1f5f9;
            color: var(--text-primary);
        }

        .nav-item.active {
            background: var(--primary);
            color: white;
        }

        .nav-item i { width: 20px; text-align: center; font-size: 1rem; }

        .nav-item .badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .nav-item.active .badge {
            background: rgba(255,255,255,0.25);
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role { font-size: 0.75rem; color: var(--text-secondary); }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .page-title p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 4px 0 0;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-add-service {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s;
        }

        .btn-add-service:hover {
            background: var(--primary-dark);
            color: white;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 32px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid var(--border);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.green { background: #ecfdf5; color: var(--success); }
        .stat-icon.blue { background: #eff6ff; color: var(--info); }
        .stat-icon.amber { background: #fffbeb; color: var(--warning); }
        .stat-icon.teal { background: #f0fdfa; color: var(--primary); }

        .stat-trend {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .stat-trend.up { background: #ecfdf5; color: var(--success); }
        .stat-trend.down { background: #fef2f2; color: var(--danger); }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Alert Banner */
        .alert-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alert-banner-content h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .alert-banner-content p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .alert-banner .btn {
            background: white;
            color: var(--primary);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .alert-banner .btn:hover {
            background: #f8fafc;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .card-header .btn-link {
            font-size: 0.85rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .card-body { padding: 24px; }

        /* Booking Item */
        .booking-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }

        .booking-item:last-child { border-bottom: none; }

        .booking-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .booking-info { flex: 1; }
        .booking-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }
        .booking-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .booking-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .booking-status.pending { background: #fef3c7; color: #92400e; }
        .booking-status.confirmed { background: #d1fae5; color: #065f46; }
        .booking-status.completed { background: #e0e7ff; color: #3730a3; }
        .booking-status.cancelled { background: #fee2e2; color: #991b1b; }

        .booking-amount {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-primary);
            text-align: right;
            min-width: 80px;
        }

        /* Service Item */
        .service-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }

        .service-item:last-child { border-bottom: none; }

        .service-thumb {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .service-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .service-thumb i { font-size: 1.25rem; color: var(--text-secondary); }

        .service-info { flex: 1; }
        .service-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }
        .service-category {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .service-price {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary);
        }

        .service-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .service-status.active { background: #d1fae5; color: #065f46; }
        .service-status.pending { background: #fef3c7; color: #92400e; }
        .service-status.inactive { background: #f1f5f9; color: #64748b; }

        /* Quick Actions */
        .quick-action {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.15s;
            margin-bottom: 8px;
            border: 1px solid var(--border);
        }

        .quick-action:hover {
            background: #f8fafc;
            border-color: var(--primary);
            color: var(--text-primary);
        }

        .quick-action-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .quick-action-icon.teal { background: #f0fdfa; color: var(--primary); }
        .quick-action-icon.blue { background: #eff6ff; color: var(--info); }
        .quick-action-icon.amber { background: #fffbeb; color: var(--warning); }
        .quick-action-icon.green { background: #ecfdf5; color: var(--success); }

        .quick-action-text { flex: 1; }
        .quick-action-title { font-weight: 600; font-size: 0.9rem; }
        .quick-action-desc { font-size: 0.75rem; color: var(--text-secondary); }

        /* Profile Completion */
        .completion-ring {
            width: 100px;
            height: 100px;
            margin: 0 auto 16px;
            position: relative;
        }

        .completion-ring svg {
            transform: rotate(-90deg);
        }

        .completion-ring circle {
            fill: none;
            stroke-width: 8;
        }

        .completion-ring .bg { stroke: #e2e8f0; }
        .completion-ring .progress { stroke: var(--primary); stroke-linecap: round; }

        .completion-percent {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .completion-label {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 16px;
        }

        .completion-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            font-size: 0.85rem;
        }

        .completion-item i.complete { color: var(--success); }
        .completion-item i.incomplete { color: #cbd5e1; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 1.5rem;
            color: var(--text-secondary);
        }

        .empty-state h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .empty-state p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 16px;
        }

        .empty-state .btn {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .dashboard-content { padding: 20px; }
        }
    </style>
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
