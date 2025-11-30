<?php
/**
 * Admin Sidebar Component
 * Reusable sidebar for all admin dashboard pages
 *
 * Usage:
 * $current_page = 'dashboard'; // or 'users', 'providers', 'bookings', 'services', 'discounts', 'festivals'
 * include '../includes/admin_sidebar.php';
 */

// Determine the correct base path
$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);

// Set base path based on current location
if ($in_admin_folder) {
    $base_path = '../';
} else {
    $base_path = '';
}

// Verify admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . $base_path . 'admin/platform_login.php');
    exit();
}

// Set default current page if not specified
if (!isset($current_page)) {
    $current_page = '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="<?php echo $base_path; ?>admin/platform_dashboard.php">TourLink<span>.</span></a>
        <small>Administration</small>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Overview</div>
        <a href="<?php echo $base_path; ?>admin/platform_dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i>
            Dashboard
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Management</div>
        <a href="<?php echo $base_path; ?>admin/users.php" class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            Users
        </a>
        <a href="<?php echo $base_path; ?>admin/manage_providers.php" class="nav-link <?php echo $current_page === 'providers' ? 'active' : ''; ?>">
            <i class="fas fa-store"></i>
            Providers
        </a>
        <a href="<?php echo $base_path; ?>admin/manage_bookings.php" class="nav-link <?php echo $current_page === 'bookings' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            Bookings
        </a>
        <a href="<?php echo $base_path; ?>admin/services.php" class="nav-link <?php echo $current_page === 'services' ? 'active' : ''; ?>">
            <i class="fas fa-concierge-bell"></i>
            Services
        </a>
        <a href="<?php echo $base_path; ?>admin/manage_discounts.php" class="nav-link <?php echo $current_page === 'discounts' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            Discount Codes
        </a>
        <a href="<?php echo $base_path; ?>admin/manage_tickets.php" class="nav-link <?php echo $current_page === 'tickets' ? 'active' : ''; ?>">
            <i class="fas fa-headset"></i>
            Support Tickets
            <?php 
            // Get new tickets count
            if (!isset($new_tickets_count)) {
                require_once __DIR__ . '/../controllers/support_ticket_controller.php';
                $stats = get_ticket_stats_ctr();
                $new_tickets_count = $stats['new_tickets'] ?? 0;
            }
            if ($new_tickets_count > 0): 
            ?>
                <span class="badge" style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem; margin-left: 8px;"><?php echo $new_tickets_count; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Content</div>
        <a href="<?php echo $base_path; ?>admin/manage_festivals.php" class="nav-link <?php echo $current_page === 'festivals' ? 'active' : ''; ?>">
            <i class="fas fa-drum"></i>
            Festivals
        </a>
    </div>

    <div class="nav-section" style="margin-top: auto;">
        <a href="<?php echo $base_path; ?>admin/platform_logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i>
            Sign Out
        </a>
    </div>
</aside>

<style>
    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100vh;
        background: #1b4332;
        padding: 24px 0;
        overflow-y: auto;
        z-index: 100;
    }

    .sidebar-brand {
        padding: 0 24px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 24px;
    }

    .sidebar-brand a {
        color: white;
        text-decoration: none;
        font-size: 22px;
        font-weight: 700;
    }

    .sidebar-brand span {
        color: #d4a017;
    }

    .sidebar-brand small {
        display: block;
        color: rgba(255,255,255,0.6);
        font-size: 11px;
        font-weight: 400;
        margin-top: 4px;
    }

    .nav-section {
        padding: 0 16px;
        margin-bottom: 24px;
    }

    .nav-section-title {
        color: rgba(255,255,255,0.4);
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0 8px;
        margin-bottom: 8px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        margin-bottom: 2px;
    }

    .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: white;
    }

    .nav-link.active {
        background: rgba(255,255,255,0.15);
        color: white;
    }

    .nav-link i {
        width: 18px;
        font-size: 14px;
    }

    /* Main Content - needed for layout when sidebar is included */
    .main-content {
        margin-left: 260px;
        min-height: 100vh;
    }
</style>

