<?php
require_once '../settings/core.php';
require_once '../classes/service_provider_class.php';
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
    header("Location: become_provider.php");
    exit();
}

// Get all bookings for this provider
$booking_class = new Booking();
$bookings = $booking_class->get_provider_bookings($provider['provider_id']);
$stats = $booking_class->get_provider_statistics($provider['provider_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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

        /* Page Header */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 1rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .stat-card.total .icon {
            background: #e8f4f1;
            color: #2d6a4f;
        }

        .stat-card.pending .icon {
            background: #fff3cd;
            color: #856404;
        }

        .stat-card.completed .icon {
            background: #d1e7dd;
            color: #0f5132;
        }

        .stat-card.earnings .icon {
            background: #f8d7da;
            color: #842029;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .filter-section .row {
            align-items: end;
        }

        /* Bookings Table Card */
        .table-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .table-responsive {
            margin-top: 20px;
        }

        table {
            width: 100%;
        }

        thead {
            background: #f8f9fa;
        }

        thead th {
            font-weight: 600;
            color: #1b4332;
            padding: 15px;
            border: none;
        }

        tbody td {
            padding: 15px;
            vertical-align: middle;
            border-top: 1px solid #e9ecef;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        /* Status Badges */
        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-confirmed {
            background: #cfe2ff;
            color: #084298;
        }

        .badge-in-progress {
            background: #e7e7ff;
            color: #3d3d99;
        }

        .badge-completed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        /* Action Buttons */
        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-view {
            background: #2d6a4f;
            color: white;
        }

        .btn-view:hover {
            background: #1b4332;
        }

        .btn-confirm {
            background: #0d6efd;
            color: white;
        }

        .btn-confirm:hover {
            background: #0b5ed7;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
        }

        .btn-cancel:hover {
            background: #c82333;
        }

        .btn-complete {
            background: #198754;
            color: white;
        }

        .btn-complete:hover {
            background: #157347;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
        }

        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: #1b4332;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 0.2rem rgba(45, 106, 79, 0.15);
        }

        .btn-filter {
            background: #2d6a4f;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            background: #1b4332;
        }

        .btn-reset {
            background: #6c757d;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-reset:hover {
            background: #5a6268;
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
                <a href="provider_dashboard.php" class="nav-link">Dashboard</a>
                <a href="manage_services.php" class="nav-link">My Services</a>
                <a href="bookings.php" class="nav-link active">Bookings</a>
                <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fa fa-calendar-check"></i> My Bookings</h2>
            <p>Manage and track all your service bookings</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card total">
                <div class="icon"><i class="fa fa-list"></i></div>
                <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card pending">
                <div class="icon"><i class="fa fa-clock"></i></div>
                <h3><?php echo $stats['pending_bookings'] ?? 0; ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card completed">
                <div class="icon"><i class="fa fa-check-circle"></i></div>
                <h3><?php echo $stats['completed_bookings'] ?? 0; ?></h3>
                <p>Completed</p>
            </div>
            <div class="stat-card earnings">
                <div class="icon"><i class="fa fa-money-bill-wave"></i></div>
                <h3>GH₵<?php echo number_format($stats['total_earnings'] ?? 0, 2); ?></h3>
                <p>Total Earnings</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form id="filterForm" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="in_progress" <?php echo (isset($_GET['status']) && $_GET['status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" id="searchFilter"
                               placeholder="Reference or tourist name"
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" id="dateFromFilter"
                               value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" id="dateToFilter"
                               value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-filter"><i class="fa fa-filter"></i> Filter</button>
                    <a href="bookings.php" class="btn btn-reset"><i class="fa fa-undo"></i> Reset</a>
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="table-card">
            <h4><i class="fa fa-list"></i> All Bookings</h4>

            <?php
            // Apply filters
            $filtered_bookings = $bookings;

            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $filtered_bookings = array_filter($filtered_bookings, function($booking) {
                    return $booking['booking_status'] === $_GET['status'];
                });
            }

            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = strtolower($_GET['search']);
                $filtered_bookings = array_filter($filtered_bookings, function($booking) use ($search) {
                    return strpos(strtolower($booking['booking_reference']), $search) !== false ||
                           strpos(strtolower($booking['tourist_first_name'] . ' ' . $booking['tourist_last_name']), $search) !== false;
                });
            }

            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $filtered_bookings = array_filter($filtered_bookings, function($booking) {
                    return $booking['service_date'] >= $_GET['date_from'];
                });
            }

            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $filtered_bookings = array_filter($filtered_bookings, function($booking) {
                    return $booking['service_date'] <= $_GET['date_to'];
                });
            }

            if (empty($filtered_bookings)):
            ?>
                <div class="empty-state">
                    <i class="fa fa-calendar-times"></i>
                    <h3>No Bookings Found</h3>
                    <p>You don't have any bookings matching your criteria yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Service</th>
                                <th>Tourist</th>
                                <th>Date & Time</th>
                                <th>People</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($booking['booking_reference']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($booking['service_title']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['tourist_first_name'] . ' ' . $booking['tourist_last_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['tourist_phone']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($booking['service_date'])); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['service_time'] ?? 'TBD'); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['number_of_people']); ?></td>
                                    <td><strong>GH₵<?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                                    <td>
                                        <?php
                                        $status = $booking['booking_status'];
                                        $badge_class = 'badge-' . str_replace('_', '-', $status);
                                        echo "<span class='badge {$badge_class}'>" . ucwords(str_replace('_', ' ', $status)) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn-action btn-view" onclick="viewBooking(<?php echo $booking['booking_id']; ?>)">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <?php if ($status === 'pending'): ?>
                                                <button class="btn-action btn-confirm" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'confirmed')">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($status === 'confirmed'): ?>
                                                <button class="btn-action btn-complete" onclick="updateStatus(<?php echo $booking['booking_id']; ?>, 'completed')">
                                                    <i class="fa fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($status, ['pending', 'confirmed'])): ?>
                                                <button class="btn-action btn-cancel" onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function viewBooking(bookingId) {
            // Redirect to booking details page (to be created)
            window.location.href = 'booking_details.php?id=' + bookingId;
        }

        function updateStatus(bookingId, status) {
            const statusText = status === 'confirmed' ? 'confirm' : 'complete';

            Swal.fire({
                title: 'Confirm Action',
                text: `Are you sure you want to ${statusText} this booking?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2d6a4f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + statusText
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../actions/update_booking_status_action.php',
                        type: 'POST',
                        data: {
                            booking_id: bookingId,
                            status: status
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
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
            });
        }

        function cancelBooking(bookingId) {
            Swal.fire({
                title: 'Cancel Booking',
                text: 'Please provide a reason for cancellation:',
                input: 'textarea',
                inputPlaceholder: 'Enter cancellation reason...',
                inputAttributes: {
                    'aria-label': 'Enter cancellation reason'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Cancel Booking',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to provide a reason!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../actions/cancel_booking_action.php',
                        type: 'POST',
                        data: {
                            booking_id: bookingId,
                            cancelled_by: 'provider',
                            cancellation_reason: result.value
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Cancelled!',
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
            });
        }
    </script>
</body>
</html>
