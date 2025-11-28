<?php
/**
 * Provider Sidebar Component
 * Reusable sidebar for all provider dashboard pages
 *
 * Usage:
 * $current_page = 'dashboard'; // or 'bookings', 'services', 'add_service', 'profile', 'premium', 'settings'
 * include '../includes/provider_sidebar.php';
 */

// Ensure user is logged in as provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header('Location: ../login/login.php');
    exit();
}

// Get provider data if not already loaded
if (!isset($provider)) {
    require_once __DIR__ . '/../classes/service_provider_class.php';
    $provider_class = new ServiceProvider();
    $provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);
}

// Get pending bookings count if not already loaded
if (!isset($pending_bookings)) {
    require_once __DIR__ . '/../classes/booking_class.php';
    $booking_class = new Booking();
    $pending_bookings = $booking_class->get_provider_pending_bookings($_SESSION['provider_id']) ?: [];
}

// Check premium status if not already loaded
if (!isset($has_premium)) {
    require_once __DIR__ . '/../classes/premium_listing_class.php';
    $premium_class = new PremiumListing();
    $has_premium = $premium_class->has_active_subscription($_SESSION['provider_id']);
}

// Set default current page if not specified
if (!isset($current_page)) {
    $current_page = 'dashboard';
}
?>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="provider_dashboard.php" class="sidebar-logo">
            <span class="sidebar-logo-text">TourLink<span class="sidebar-logo-dot"></span></span>
            <span class="sidebar-logo-badge">Provider</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">MAIN</div>
            <a href="provider_dashboard.php" class="nav-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <i class="fa fa-th-large"></i> Dashboard
            </a>
            <a href="../view/provider/manage_bookings.php" class="nav-item <?php echo $current_page === 'bookings' ? 'active' : ''; ?>">
                <i class="fa fa-calendar-check"></i> Bookings
                <?php if (count($pending_bookings) > 0): ?>
                    <span class="badge"><?php echo count($pending_bookings); ?></span>
                <?php endif; ?>
            </a>
            <a href="manage_services.php" class="nav-item <?php echo $current_page === 'services' ? 'active' : ''; ?>">
                <i class="fa fa-briefcase"></i> My Services
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">MANAGEMENT</div>
            <a href="add_service.php" class="nav-item <?php echo $current_page === 'add_service' ? 'active' : ''; ?>">
                <i class="fa fa-plus-circle"></i> Add Service
            </a>
            <a href="provider_profile.php" class="nav-item <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                <i class="fa fa-user-cog"></i> Business Profile
            </a>
            <a href="premium_subscription.php" class="nav-item <?php echo $current_page === 'premium' ? 'active' : ''; ?>">
                <i class="fa fa-crown"></i> Premium Subscription
                <?php if ($has_premium): ?>
                    <span class="badge" style="background: #d4a017;">Active</span>
                <?php endif; ?>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">OTHER</div>
            <a href="../index_tourlink.php" class="nav-item">
                <i class="fa fa-external-link-alt"></i> View Site
            </a>
            <a href="../view/profile_settings.php" class="nav-item <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                <i class="fa fa-cog"></i> Account Settings
            </a>
            <a href="../login/logout.php" class="nav-item">
                <i class="fa fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($provider['business_name'] ?: $_SESSION['first_name']); ?></div>
                <div class="user-role"><?php echo ucfirst($provider['verification_status']); ?></div>
            </div>
        </div>
    </div>
</aside>
