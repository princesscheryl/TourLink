<?php
/**
 * Provider Booking Management Page
 * Allows service providers to view, approve, reject, and manage bookings
 */
require_once '../../settings/core.php';
require_once '../../controllers/booking_controller.php';
require_once '../../classes/service_provider_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    header("Location: ../../index_tourlink.php");
    exit();
}

$provider_id = $provider['provider_id'];
$bookings = get_provider_bookings_ctr($provider_id);
if (!$bookings) {
    $bookings = [];
}

// Get statistics
$stats = get_provider_statistics_ctr($provider_id);

// Group bookings by status
$pending = array_filter($bookings, fn($b) => $b['booking_status'] === 'pending');
$confirmed = array_filter($bookings, fn($b) => $b['booking_status'] === 'confirmed');
$in_progress = array_filter($bookings, fn($b) => $b['booking_status'] === 'in_progress');
$completed = array_filter($bookings, fn($b) => $b['booking_status'] === 'completed');
$cancelled = array_filter($bookings, fn($b) => in_array($b['booking_status'], ['cancelled', 'refunded']));

// Get current filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Set current page for sidebar
$current_page = 'bookings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - TourLink Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            padding: 16px 0;
            overflow-y: auto;
        }

        .nav-section {
            padding: 0 16px;
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 600;
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
            padding: 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .nav-item:hover {
            background: var(--bg-main);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .nav-item i { width: 20px; text-align: center; font-size: 1rem; }

        .nav-item .badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .nav-item.active .badge {
            background: rgba(255,255,255,0.3);
        }

        /* Legacy support for nav-badge */
        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .nav-item.active .nav-badge {
            background: rgba(255,255,255,0.3);
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .provider-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--bg-main);
            border-radius: 10px;
        }

        .provider-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .provider-info h4 { font-size: 0.85rem; font-weight: 600; margin: 0; }
        .provider-info span { font-size: 0.75rem; color: var(--text-secondary); }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .top-bar {
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

        .page-title h1 { font-size: 1.25rem; font-weight: 600; margin: 0; }
        .page-title p { font-size: 0.85rem; color: var(--text-secondary); margin: 4px 0 0 0; }

        .content-area { padding: 24px 32px; }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .stat-icon.pending { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-icon.total { background: rgba(15, 118, 110, 0.1); color: var(--primary); }
        .stat-icon.completed { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.earnings { background: rgba(59, 130, 246, 0.1); color: var(--info); }

        .stat-details h3 { font-size: 1.5rem; font-weight: 700; margin: 0; line-height: 1; }
        .stat-details span { font-size: 0.8rem; color: var(--text-secondary); }

        /* Filter Tabs */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .filter-tabs {
            display: flex;
            gap: 4px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 8px 14px;
            border: none;
            background: none;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-tab:hover { color: var(--text-primary); }

        .filter-tab.active {
            background: var(--primary);
            color: white;
        }

        .filter-tab .count {
            background: rgba(0,0,0,0.08);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
        }

        .filter-tab.active .count {
            background: rgba(255,255,255,0.25);
        }

        /* Booking Cards */
        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .booking-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            border-left: 4px solid var(--border);
            transition: all 0.2s;
        }

        .booking-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .booking-card.status-pending { border-left-color: var(--warning); }
        .booking-card.status-confirmed { border-left-color: var(--success); }
        .booking-card.status-in_progress { border-left-color: var(--info); }
        .booking-card.status-completed { border-left-color: #059669; }
        .booking-card.status-cancelled, .booking-card.status-rejected { border-left-color: var(--danger); }

        .booking-card-inner {
            display: grid;
            grid-template-columns: 1fr auto;
            padding: 20px;
            gap: 20px;
        }

        .booking-main { min-width: 0; }

        .booking-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .booking-reference {
            font-size: 0.7rem;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--bg-main);
            padding: 4px 10px;
            border-radius: 4px;
        }

        .booking-service-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .customer-avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .customer-name { font-weight: 600; font-size: 0.9rem; }
        .customer-contact { font-size: 0.8rem; color: var(--text-secondary); }

        .booking-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .booking-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .booking-meta i { color: var(--primary); font-size: 0.9rem; }

        .special-requests {
            margin-top: 14px;
            padding: 12px 14px;
            background: rgba(245, 158, 11, 0.08);
            border-radius: 8px;
            border-left: 3px solid var(--warning);
            font-size: 0.85rem;
            color: #78350f;
        }

        .special-requests strong {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #92400e;
            margin-bottom: 4px;
        }

        .booking-sidebar {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
            min-width: 140px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-pending { background: rgba(245, 158, 11, 0.1); color: #d97706; }
        .status-confirmed { background: rgba(16, 185, 129, 0.1); color: #059669; }
        .status-in_progress { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
        .status-completed { background: rgba(16, 185, 129, 0.15); color: #047857; }
        .status-cancelled, .status-rejected { background: rgba(239, 68, 68, 0.1); color: #dc2626; }

        .booking-amount {
            text-align: right;
            margin-top: 12px;
        }

        .booking-amount .total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }

        .booking-amount .earnings {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Actions Footer */
        .booking-actions {
            display: flex;
            gap: 8px;
            padding: 14px 20px;
            background: var(--bg-main);
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Inter', sans-serif;
        }

        .btn-approve { background: var(--success); color: white; }
        .btn-approve:hover { background: #059669; color: white; }
        .btn-reject { background: white; border: 1px solid var(--danger); color: var(--danger); }
        .btn-reject:hover { background: var(--danger); color: white; }
        .btn-start { background: var(--info); color: white; }
        .btn-start:hover { background: #2563eb; color: white; }
        .btn-complete { background: var(--primary); color: white; }
        .btn-complete:hover { background: var(--primary-dark); color: white; }
        .btn-contact { background: white; border: 1px solid var(--border); color: var(--text-secondary); }
        .btn-contact:hover { background: var(--bg-main); color: var(--text-primary); }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--bg-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .empty-icon i { font-size: 2rem; color: var(--text-secondary); }
        .empty-state h3 { font-size: 1.1rem; font-weight: 600; margin: 0 0 8px 0; }
        .empty-state p { font-size: 0.9rem; color: var(--text-secondary); margin: 0; }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar-logo-text, .sidebar-logo-badge, .nav-section-title, .nav-item span, .provider-info { display: none; }
            .nav-item .badge, .nav-badge { margin-left: 0; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .content-area { padding: 16px; }
            .stats-row { grid-template-columns: 1fr 1fr; gap: 12px; }
            .booking-card-inner { grid-template-columns: 1fr; }
            .booking-sidebar { flex-direction: row; justify-content: space-between; align-items: center; }
            .booking-amount { text-align: left; margin-top: 0; }
        }
    </style>
</head>
<body>
    <?php
    // Include reusable sidebar component
    include '../../includes/provider_sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Manage Bookings</h1>
                <p>View and manage your service booking requests</p>
            </div>
        </div>

        <div class="content-area">
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo count($pending); ?></h3>
                        <span>Pending</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                        <span>Total Bookings</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['completed_bookings'] ?? 0; ?></h3>
                        <span>Completed</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon earnings">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-details">
                        <h3>GHS <?php echo number_format($stats['total_earnings'] ?? 0, 0); ?></h3>
                        <span>Earnings</span>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        All <span class="count"><?php echo count($bookings); ?></span>
                    </a>
                    <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                        Pending <span class="count"><?php echo count($pending); ?></span>
                    </a>
                    <a href="?filter=confirmed" class="filter-tab <?php echo $filter === 'confirmed' ? 'active' : ''; ?>">
                        Confirmed <span class="count"><?php echo count($confirmed); ?></span>
                    </a>
                    <a href="?filter=in_progress" class="filter-tab <?php echo $filter === 'in_progress' ? 'active' : ''; ?>">
                        In Progress <span class="count"><?php echo count($in_progress); ?></span>
                    </a>
                    <a href="?filter=completed" class="filter-tab <?php echo $filter === 'completed' ? 'active' : ''; ?>">
                        Completed <span class="count"><?php echo count($completed); ?></span>
                    </a>
                    <a href="?filter=cancelled" class="filter-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">
                        Cancelled <span class="count"><?php echo count($cancelled); ?></span>
                    </a>
                </div>
            </div>

            <?php
            // Apply filter
            $filtered_bookings = $bookings;
            switch ($filter) {
                case 'pending': $filtered_bookings = $pending; break;
                case 'confirmed': $filtered_bookings = $confirmed; break;
                case 'in_progress': $filtered_bookings = $in_progress; break;
                case 'completed': $filtered_bookings = $completed; break;
                case 'cancelled': $filtered_bookings = $cancelled; break;
            }
            ?>

            <?php if (empty($filtered_bookings)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>No bookings found</h3>
                    <p>
                        <?php if ($filter === 'pending'): ?>
                            You don't have any pending booking requests at the moment.
                        <?php elseif ($filter === 'all'): ?>
                            You haven't received any bookings yet.
                        <?php else: ?>
                            No bookings match this filter.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($filtered_bookings as $booking): ?>
                        <?php
                        $status_class = 'status-' . $booking['booking_status'];
                        $initials = strtoupper(substr($booking['tourist_first_name'], 0, 1) . substr($booking['tourist_last_name'], 0, 1));
                        ?>
                        <div class="booking-card <?php echo $status_class; ?>">
                            <div class="booking-card-inner">
                                <div class="booking-main">
                                    <div class="booking-header">
                                        <span class="booking-reference"><?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                                    </div>
                                    <div class="booking-service-title"><?php echo htmlspecialchars($booking['service_title']); ?></div>

                                    <div class="customer-info">
                                        <div class="customer-avatar"><?php echo $initials; ?></div>
                                        <div>
                                            <div class="customer-name"><?php echo htmlspecialchars($booking['tourist_first_name'] . ' ' . $booking['tourist_last_name']); ?></div>
                                            <div class="customer-contact">
                                                <?php echo htmlspecialchars($booking['tourist_email']); ?>
                                                <?php if (!empty($booking['tourist_phone'])): ?>
                                                    &bull; <?php echo htmlspecialchars($booking['tourist_phone']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="booking-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($booking['service_date'])); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($booking['service_time'])); ?></span>
                                        <span><i class="fas fa-users"></i> <?php echo $booking['number_of_people']; ?> guest(s)</span>
                                        <span><i class="fas fa-calendar-plus"></i> Booked: <?php echo date('M d', strtotime($booking['booking_date'])); ?></span>
                                    </div>

                                    <?php if (!empty($booking['special_requests'])): ?>
                                        <div class="special-requests">
                                            <strong><i class="fas fa-comment-alt"></i> Special Request</strong>
                                            <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="booking-sidebar">
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                                    </span>
                                    <div class="booking-amount">
                                        <div class="total">GHS <?php echo number_format($booking['total_amount'], 2); ?></div>
                                        <div class="earnings">You earn: GHS <?php echo number_format($booking['provider_earnings'], 2); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="booking-actions">
                                <?php if ($booking['booking_status'] === 'pending'): ?>
                                    <button class="btn-action btn-approve" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'confirmed')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn-action btn-reject" onclick="rejectBooking(<?php echo $booking['booking_id']; ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php endif; ?>

                                <?php if ($booking['booking_status'] === 'confirmed'): ?>
                                    <button class="btn-action btn-start" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'in_progress')">
                                        <i class="fas fa-play"></i> Start Service
                                    </button>
                                    <button class="btn-action btn-reject" onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                <?php endif; ?>

                                <?php if ($booking['booking_status'] === 'in_progress'): ?>
                                    <button class="btn-action btn-complete" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'completed')">
                                        <i class="fas fa-check-double"></i> Mark Complete
                                    </button>
                                <?php endif; ?>

                                <a href="mailto:<?php echo htmlspecialchars($booking['tourist_email']); ?>" class="btn-action btn-contact">
                                    <i class="fas fa-envelope"></i> Email
                                </a>
                                <?php if (!empty($booking['tourist_phone'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($booking['tourist_phone']); ?>" class="btn-action btn-contact">
                                        <i class="fas fa-phone"></i> Call
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function updateStatus(bookingId, status) {
        let title, text, confirmText;

        switch(status) {
            case 'confirmed':
                title = 'Approve Booking?';
                text = 'This will confirm the booking and notify the customer.';
                confirmText = 'Yes, approve it';
                break;
            case 'in_progress':
                title = 'Start Service?';
                text = 'This will mark the service as in progress.';
                confirmText = 'Yes, start it';
                break;
            case 'completed':
                title = 'Complete Booking?';
                text = 'This will mark the booking as completed.';
                confirmText = 'Yes, complete it';
                break;
            default:
                return;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0f766e',
            cancelButtonColor: '#64748b',
            confirmButtonText: confirmText
        }).then((result) => {
            if (result.isConfirmed) {
                submitStatusUpdate(bookingId, status);
            }
        });
    }

    function rejectBooking(bookingId) {
        Swal.fire({
            title: 'Reject Booking?',
            text: 'Please provide a reason for rejection:',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Reason for rejection...',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Reject Booking',
            inputValidator: (value) => {
                if (!value) return 'Please provide a reason';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitStatusUpdate(bookingId, 'rejected', result.value);
            }
        });
    }

    function cancelBooking(bookingId) {
        Swal.fire({
            title: 'Cancel Booking?',
            text: 'Are you sure you want to cancel this booking?',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Reason for cancellation',
            inputPlaceholder: 'Enter reason...',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, cancel it'
        }).then((result) => {
            if (result.isConfirmed) {
                submitStatusUpdate(bookingId, 'cancelled', result.value || 'Cancelled by provider');
            }
        });
    }

    function submitStatusUpdate(bookingId, status, reason = '') {
        $.ajax({
            url: '../../actions/update_booking_status_action.php',
            method: 'POST',
            data: { booking_id: bookingId, status: status, reason: reason },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonColor: '#0f766e'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonColor: '#0f766e'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#0f766e'
                });
            }
        });
    }
    </script>
</body>
</html>
