<?php
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$bookings = get_tourist_bookings_ctr($user_id);
if (!$bookings) {
    $bookings = [];
}

// Group bookings by status
$upcoming = array_filter($bookings, fn($b) => in_array($b['booking_status'], ['pending', 'confirmed']));
$past = array_filter($bookings, fn($b) => in_array($b['booking_status'], ['completed', 'cancelled', 'refunded']));
$in_progress = array_filter($bookings, fn($b) => $b['booking_status'] === 'in_progress');

// Get current filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
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
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; min-height: 100vh; }

        .page-header {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 120px 0 50px;
            margin-bottom: 40px;
        }
        .page-header h1 { font-size: 2.2rem; font-weight: 700; margin-bottom: 8px; }
        .page-header p { opacity: 0.9; }

        .main-container { max-width: 1200px; margin: 0 auto; padding: 0 24px 60px; }

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

        /* Booking Card */
        .booking-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 16px;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .booking-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }

        .booking-card-inner {
            display: flex;
            padding: 20px;
            gap: 20px;
        }

        .booking-image {
            width: 140px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            background: #f0f0f0;
        }
        .booking-image img { width: 100%; height: 100%; object-fit: cover; }
        .booking-image-placeholder {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
        }
        .booking-image-placeholder i { color: white; opacity: 0.5; font-size: 2rem; }

        .booking-details { flex: 1; min-width: 0; }
        .booking-reference {
            font-size: 0.75rem;
            color: #888;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .booking-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .booking-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.9rem;
            color: #666;
        }
        .booking-meta span i { color: #2d6a4f; margin-right: 6px; width: 16px; }

        .booking-status-price {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
            min-width: 120px;
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
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1e7dd; color: #0f5132; }
        .status-in_progress { background: #cff4fc; color: #055160; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #e2e3e5; color: #383d41; }

        .booking-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d6a4f;
        }

        /* Actions Footer */
        .booking-actions {
            display: flex;
            gap: 8px;
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
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
        .btn-view { background: #2d6a4f; color: white; }
        .btn-view:hover { background: #1b4332; color: white; }
        .btn-cancel { background: white; border: 1px solid #dc3545; color: #dc3545; }
        .btn-cancel:hover { background: #dc3545; color: white; }
        .btn-review { background: #ffc107; color: #1a1a1a; }
        .btn-review:hover { background: #e0a800; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
        }
        .empty-state i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }
        .empty-state h3 { color: #333; margin-bottom: 10px; }
        .empty-state p { color: #666; margin-bottom: 24px; }
        .btn-primary {
            background: #2d6a4f; border: none;
            padding: 12px 24px; border-radius: 8px;
            font-weight: 600; color: white;
            text-decoration: none;
        }
        .btn-primary:hover { background: #1b4332; color: white; }

        /* Responsive */
        @media (max-width: 768px) {
            .booking-card-inner { flex-direction: column; }
            .booking-image { width: 100%; height: 160px; }
            .booking-status-price { flex-direction: row; width: 100%; margin-top: 12px; }
        }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="page-header">
        <div class="main-container">
            <h1><i class="fa fa-calendar-check"></i> My Bookings</h1>
            <p>View and manage your service bookings</p>
        </div>
    </div>

    <div class="main-container">
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All <span class="count"><?php echo count($bookings); ?></span>
            </a>
            <a href="?filter=upcoming" class="filter-tab <?php echo $filter === 'upcoming' ? 'active' : ''; ?>">
                Upcoming <span class="count"><?php echo count($upcoming); ?></span>
            </a>
            <a href="?filter=in_progress" class="filter-tab <?php echo $filter === 'in_progress' ? 'active' : ''; ?>">
                In Progress <span class="count"><?php echo count($in_progress); ?></span>
            </a>
            <a href="?filter=past" class="filter-tab <?php echo $filter === 'past' ? 'active' : ''; ?>">
                Past <span class="count"><?php echo count($past); ?></span>
            </a>
        </div>

        <?php
        // Apply filter
        $filtered_bookings = $bookings;
        if ($filter === 'upcoming') $filtered_bookings = $upcoming;
        elseif ($filter === 'in_progress') $filtered_bookings = $in_progress;
        elseif ($filter === 'past') $filtered_bookings = $past;
        ?>

        <?php if (empty($filtered_bookings)): ?>
            <div class="empty-state">
                <i class="far fa-calendar-alt"></i>
                <h3>No bookings found</h3>
                <p>You haven't made any bookings yet. Start exploring services!</p>
                <a href="all_services.php" class="btn-primary">Browse Services</a>
            </div>
        <?php else: ?>
            <?php foreach ($filtered_bookings as $booking): ?>
                <?php
                $image_url = null;
                if (!empty($booking['service_images'])) {
                    $images = json_decode($booking['service_images'], true);
                    if (is_array($images) && count($images) > 0) {
                        $image_url = '../' . $images[0];
                    } elseif (file_exists('../' . $booking['service_images'])) {
                        $image_url = '../' . $booking['service_images'];
                    }
                }
                $status_class = 'status-' . $booking['booking_status'];
                ?>
                <div class="booking-card">
                    <div class="booking-card-inner">
                        <div class="booking-image">
                            <?php if ($image_url && file_exists($image_url)): ?>
                                <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Service">
                            <?php else: ?>
                                <div class="booking-image-placeholder">
                                    <i class="fa fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="booking-details">
                            <div class="booking-reference"><?php echo htmlspecialchars($booking['booking_reference']); ?></div>
                            <div class="booking-title"><?php echo htmlspecialchars($booking['service_title']); ?></div>
                            <div class="booking-meta">
                                <span><i class="fa fa-calendar"></i> <?php echo date('M d, Y', strtotime($booking['service_date'])); ?></span>
                                <span><i class="fa fa-clock"></i> <?php echo date('g:i A', strtotime($booking['service_time'])); ?></span>
                                <span><i class="fa fa-users"></i> <?php echo $booking['number_of_people']; ?> guest(s)</span>
                                <span><i class="fa fa-store"></i> <?php echo htmlspecialchars($booking['provider_name']); ?></span>
                            </div>
                        </div>

                        <div class="booking-status-price">
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                            </span>
                            <div class="booking-price">GHS <?php echo number_format($booking['total_amount'], 2); ?></div>
                        </div>
                    </div>

                    <div class="booking-actions">
                        <a href="single_service.php?id=<?php echo $booking['service_id']; ?>" class="btn-action btn-view">
                            <i class="fa fa-eye"></i> View Service
                        </a>

                        <?php if (in_array($booking['booking_status'], ['pending', 'confirmed'])): ?>
                            <button class="btn-action btn-cancel" onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        <?php endif; ?>

                        <?php if ($booking['booking_status'] === 'completed'): ?>
                            <a href="single_service.php?id=<?php echo $booking['service_id']; ?>#reviews" class="btn-action btn-review">
                                <i class="fa fa-star"></i> Leave Review
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function cancelBooking(bookingId) {
        Swal.fire({
            title: 'Cancel Booking?',
            text: 'Are you sure you want to cancel this booking?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it',
            input: 'textarea',
            inputLabel: 'Reason for cancellation (optional)',
            inputPlaceholder: 'Enter your reason...'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../actions/update_booking_status_action.php',
                    method: 'POST',
                    data: {
                        booking_id: bookingId,
                        status: 'cancelled',
                        reason: result.value || ''
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Cancelled', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to cancel booking', 'error');
                    }
                });
            }
        });
    }
    </script>
</body>
</html>
