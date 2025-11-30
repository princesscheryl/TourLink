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
    <link href="../css/my_bookings.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/my_bookings.js"></script>
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
                require_once '../classes/hosted_upload_class.php';
                $image_url = null;
                if (!empty($booking['service_images'])) {
                    $images = json_decode($booking['service_images'], true);
                    if (is_array($images) && count($images) > 0) {
                        $image_url = HostedUpload::getImageUrl($images[0], '../');
                    } elseif (!empty($booking['service_images'])) {
                        $image_url = HostedUpload::getImageUrl($booking['service_images'], '../');
                    }
                }
                $status_class = 'status-' . $booking['booking_status'];
                ?>
                <div class="booking-card">
                    <div class="booking-card-inner">
                        <div class="booking-image">
                            <?php if ($image_url): ?>
                                <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Service" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="booking-image-placeholder" style="display:none;">
                                    <i class="fa fa-image"></i>
                                </div>
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
</body>
</html>
