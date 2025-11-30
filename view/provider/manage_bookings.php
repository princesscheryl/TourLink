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
    <link href="../../css/provider_sidebar.css" rel="stylesheet">
    <link href="../../css/provider_bookings.css" rel="stylesheet">
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
    <script src="../../js/provider_bookings.js"></script>
</body>
</html>
