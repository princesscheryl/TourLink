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
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - TourLink Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../../css/dark-mode.css" rel="stylesheet">
    <script src="../../js/dark-mode.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f6fa; min-height: 100vh; }

        /* Navigation */
        .provider-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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

        .logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: #2d6a4f;
            text-decoration: none;
        }
        .logo-dot { color: #ffd700; }
        .logo-sub { font-size: 0.75rem; color: #666; font-weight: 500; }

        .nav-links { display: flex; gap: 24px; align-items: center; }
        .nav-link-item {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .nav-link-item:hover, .nav-link-item.active {
            color: #2d6a4f;
            border-bottom-color: #2d6a4f;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 110px 30px 40px;
        }
        .page-header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        .page-header p { opacity: 0.9; }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-top: -30px;
            padding: 0 30px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1a1a;
        }
        .stat-card .stat-label {
            color: #666;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .stat-card.earnings .stat-value { color: #2d6a4f; }
        .stat-card.pending .stat-value { color: #f59e0b; }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px 60px;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 10px 20px;
            border-radius: 8px;
            background: white;
            border: 2px solid #e0e0e0;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .filter-tab:hover { border-color: #2d6a4f; color: #2d6a4f; }
        .filter-tab.active {
            background: #2d6a4f;
            border-color: #2d6a4f;
            color: white;
        }
        .filter-tab .count {
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 6px;
        }
        .filter-tab.active .count { background: rgba(255,255,255,0.2); }
        .filter-tab.pending-tab .count { background: #fef3c7; color: #92400e; }
        .filter-tab.pending-tab.active .count { background: rgba(255,255,255,0.3); color: white; }

        /* Booking Card */
        .booking-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 16px;
            overflow: hidden;
            border-left: 4px solid transparent;
        }
        .booking-card.status-pending { border-left-color: #f59e0b; }
        .booking-card.status-confirmed { border-left-color: #10b981; }
        .booking-card.status-in_progress { border-left-color: #3b82f6; }
        .booking-card.status-completed { border-left-color: #059669; }
        .booking-card.status-cancelled { border-left-color: #ef4444; }

        .booking-card-inner {
            display: flex;
            padding: 20px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .booking-main { flex: 1; min-width: 250px; }
        .booking-reference {
            font-size: 0.75rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .booking-service-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .customer-name { font-weight: 500; color: #1a1a1a; }
        .customer-contact { font-size: 0.85rem; color: #666; }

        .booking-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.9rem;
            color: #555;
        }
        .booking-meta span i { color: #2d6a4f; margin-right: 6px; width: 16px; }

        .booking-sidebar {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
            min-width: 150px;
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-in_progress { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-refunded { background: #e5e7eb; color: #374151; }

        .booking-amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d6a4f;
            margin-top: 10px;
        }
        .booking-earnings {
            font-size: 0.85rem;
            color: #666;
        }

        /* Actions Footer */
        .booking-actions {
            display: flex;
            gap: 8px;
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
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
        }
        .btn-approve { background: #10b981; color: white; }
        .btn-approve:hover { background: #059669; color: white; }
        .btn-reject { background: white; border: 1px solid #ef4444; color: #ef4444; }
        .btn-reject:hover { background: #ef4444; color: white; }
        .btn-start { background: #3b82f6; color: white; }
        .btn-start:hover { background: #2563eb; color: white; }
        .btn-complete { background: #2d6a4f; color: white; }
        .btn-complete:hover { background: #1b4332; color: white; }
        .btn-contact { background: white; border: 1px solid #e0e0e0; color: #666; }
        .btn-contact:hover { background: #f0f0f0; }

        /* Special Requests */
        .special-requests {
            margin-top: 12px;
            padding: 12px;
            background: #fffbeb;
            border-radius: 8px;
            border-left: 3px solid #f59e0b;
            font-size: 0.9rem;
            color: #78350f;
        }
        .special-requests strong { color: #92400e; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
        }
        .empty-state i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }
        .empty-state h3 { color: #333; margin-bottom: 10px; }
        .empty-state p { color: #666; }

        @media (max-width: 768px) {
            .booking-card-inner { flex-direction: column; }
            .booking-sidebar { flex-direction: row; width: 100%; justify-content: space-between; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <nav class="provider-nav">
        <div class="nav-container">
            <a href="../../index_tourlink.php" class="logo">
                TourLink<span class="logo-dot">.</span>
                <span class="logo-sub">Provider</span>
            </a>
            <div class="nav-links">
                <a href="provider_dashboard.php" class="nav-link-item">Dashboard</a>
                <a href="manage_services.php" class="nav-link-item">My Services</a>
                <a href="manage_bookings.php" class="nav-link-item active">Bookings</a>
                <a href="../../login/logout.php" class="nav-link-item">Logout</a>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div style="max-width: 1400px; margin: 0 auto;">
            <h1><i class="fa fa-calendar-alt me-2"></i>Manage Bookings</h1>
            <p>View and manage your service booking requests</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card pending">
            <div class="stat-value"><?php echo count($pending); ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total_bookings'] ?? 0; ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['completed_bookings'] ?? 0; ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card earnings">
            <div class="stat-value">GHS <?php echo number_format($stats['total_earnings'] ?? 0, 0); ?></div>
            <div class="stat-label">Total Earnings</div>
        </div>
    </div>

    <div class="main-container">
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?filter=pending" class="filter-tab pending-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fa fa-clock"></i> Pending <span class="count"><?php echo count($pending); ?></span>
            </a>
            <a href="?filter=confirmed" class="filter-tab <?php echo $filter === 'confirmed' ? 'active' : ''; ?>">
                <i class="fa fa-check"></i> Confirmed <span class="count"><?php echo count($confirmed); ?></span>
            </a>
            <a href="?filter=in_progress" class="filter-tab <?php echo $filter === 'in_progress' ? 'active' : ''; ?>">
                <i class="fa fa-play"></i> In Progress <span class="count"><?php echo count($in_progress); ?></span>
            </a>
            <a href="?filter=completed" class="filter-tab <?php echo $filter === 'completed' ? 'active' : ''; ?>">
                <i class="fa fa-check-double"></i> Completed <span class="count"><?php echo count($completed); ?></span>
            </a>
            <a href="?filter=cancelled" class="filter-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">
                <i class="fa fa-times"></i> Cancelled <span class="count"><?php echo count($cancelled); ?></span>
            </a>
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All <span class="count"><?php echo count($bookings); ?></span>
            </a>
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
                <i class="far fa-calendar-alt"></i>
                <h3>No bookings found</h3>
                <p>
                    <?php if ($filter === 'pending'): ?>
                        You don't have any pending booking requests at the moment.
                    <?php else: ?>
                        No bookings match this filter.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($filtered_bookings as $booking): ?>
                <?php
                $status_class = 'status-' . $booking['booking_status'];
                $initials = strtoupper(substr($booking['tourist_first_name'], 0, 1) . substr($booking['tourist_last_name'], 0, 1));
                ?>
                <div class="booking-card <?php echo $status_class; ?>">
                    <div class="booking-card-inner">
                        <div class="booking-main">
                            <div class="booking-reference"><?php echo htmlspecialchars($booking['booking_reference']); ?></div>
                            <div class="booking-service-title"><?php echo htmlspecialchars($booking['service_title']); ?></div>

                            <div class="customer-info">
                                <div class="customer-avatar"><?php echo $initials; ?></div>
                                <div>
                                    <div class="customer-name"><?php echo htmlspecialchars($booking['tourist_first_name'] . ' ' . $booking['tourist_last_name']); ?></div>
                                    <div class="customer-contact">
                                        <?php echo htmlspecialchars($booking['tourist_phone'] ?? 'No phone'); ?>
                                        &bull;
                                        <?php echo htmlspecialchars($booking['tourist_email']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="booking-meta">
                                <span><i class="fa fa-calendar"></i> <?php echo date('M d, Y', strtotime($booking['service_date'])); ?></span>
                                <span><i class="fa fa-clock"></i> <?php echo date('g:i A', strtotime($booking['service_time'])); ?></span>
                                <span><i class="fa fa-users"></i> <?php echo $booking['number_of_people']; ?> guest(s)</span>
                                <span><i class="fa fa-calendar-plus"></i> Booked: <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></span>
                            </div>

                            <?php if (!empty($booking['special_requests'])): ?>
                                <div class="special-requests">
                                    <strong><i class="fa fa-comment"></i> Special Requests:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="booking-sidebar">
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                            </span>
                            <div>
                                <div class="booking-amount">GHS <?php echo number_format($booking['total_amount'], 2); ?></div>
                                <div class="booking-earnings">Your earnings: GHS <?php echo number_format($booking['provider_earnings'], 2); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="booking-actions">
                        <?php if ($booking['booking_status'] === 'pending'): ?>
                            <button class="btn-action btn-approve" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'confirmed')">
                                <i class="fa fa-check"></i> Approve
                            </button>
                            <button class="btn-action btn-reject" onclick="rejectBooking(<?php echo $booking['booking_id']; ?>)">
                                <i class="fa fa-times"></i> Reject
                            </button>
                        <?php endif; ?>

                        <?php if ($booking['booking_status'] === 'confirmed'): ?>
                            <button class="btn-action btn-start" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'in_progress')">
                                <i class="fa fa-play"></i> Start Service
                            </button>
                            <button class="btn-action btn-reject" onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        <?php endif; ?>

                        <?php if ($booking['booking_status'] === 'in_progress'): ?>
                            <button class="btn-action btn-complete" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'completed')">
                                <i class="fa fa-check-double"></i> Mark Complete
                            </button>
                        <?php endif; ?>

                        <a href="mailto:<?php echo htmlspecialchars($booking['tourist_email']); ?>" class="btn-action btn-contact">
                            <i class="fa fa-envelope"></i> Contact
                        </a>
                        <?php if ($booking['tourist_phone']): ?>
                            <a href="tel:<?php echo htmlspecialchars($booking['tourist_phone']); ?>" class="btn-action btn-contact">
                                <i class="fa fa-phone"></i> Call
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

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
                text = 'This will mark the booking as completed. The customer will be able to leave a review.';
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
            confirmButtonColor: '#2d6a4f',
            cancelButtonColor: '#6c757d',
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
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Reject Booking',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a reason for rejection';
                }
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
            text: 'Are you sure you want to cancel this confirmed booking?',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Reason for cancellation',
            inputPlaceholder: 'Enter reason...',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
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
            data: {
                booking_id: bookingId,
                status: status,
                reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonColor: '#2d6a4f'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonColor: '#2d6a4f'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#2d6a4f'
                });
            }
        });
    }
    </script>
</body>
</html>
