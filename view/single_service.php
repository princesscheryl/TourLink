<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../classes/service_provider_class.php';
require_once '../controllers/favorite_controller.php';
require_once '../controllers/review_controller.php';
require_once '../classes/hosted_upload_class.php';

$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$service_id) {
    header("Location: all_services.php");
    exit();
}

$service = get_service_by_id_ctr($service_id);

if (!$service) {
    header("Location: all_services.php");
    exit();
}

// Increment views
increment_service_views_ctr($service_id);

// Check if logged-in user is a provider
$is_provider = false;
$is_own_service = false;

if (isset($_SESSION['user_id'])) {
    $provider_class = new ServiceProvider();
    $provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

    if ($provider) {
        $is_provider = true;
        // Check if this is the provider's own service
        if ($provider['provider_id'] == $service['provider_id']) {
            $is_own_service = true;
        }
    }
}

// Check if service is favorited
$is_favorited = false;
if (isset($_SESSION['user_id'])) {
    $is_favorited = is_favorited_ctr($_SESSION['user_id'], $service_id);
}

// Get reviews and statistics
$reviews = get_service_reviews_ctr($service_id);
$review_stats = get_service_review_stats_ctr($service_id);

// Check if user can submit a review (has completed bookings)
$user_can_review = false;
$user_bookings = [];
if (isset($_SESSION['user_id']) && !$is_provider) {
    $user_bookings = get_user_completed_bookings_ctr($_SESSION['user_id'], $service_id);
    // Check if there's at least one booking without a review
    foreach ($user_bookings as $booking) {
        if ($booking['has_review'] == 0) {
            $user_can_review = true;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($service['service_title']); ?> - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/single_service.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script>
        // Pass PHP variables to JavaScript
        window.serviceData = {
            basePrice: <?php echo $service['base_price']; ?>,
            pricingUnit: '<?php echo $service['pricing_unit']; ?>',
            maxCapacity: <?php echo $service['max_capacity'] ?: 'null'; ?>
        };
        window.serviceId = <?php echo $service_id; ?>;
    </script>
</head>
<body>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="all_services.php" class="nav-link">Browse Services</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
                <?php else: ?>
                    <a href="../login/login.php" class="nav-link">Sign in</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-nav">
            <a href="../index_tourlink.php">Home</a> /
            <a href="all_services.php">Services</a> /
            <span><?php echo htmlspecialchars($service['service_title']); ?></span>
        </div>

        <!-- Image Gallery -->
        <?php
        $gallery_images = json_decode($service['service_images'], true);
        $has_images = is_array($gallery_images) && !empty($gallery_images);
        ?>
        <div class="service-gallery">
            <div class="gallery-main">
                <?php if ($has_images): ?>
                    <?php $first_img = HostedUpload::getImageUrl($gallery_images[0], '../'); ?>
                    <img src="<?php echo htmlspecialchars($first_img); ?>"
                         alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                         id="mainGalleryImage"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="gallery-placeholder" style="display:none;">
                        <i class="fa fa-image"></i>
                        <span>Image not available</span>
                    </div>
                <?php else: ?>
                    <div class="gallery-placeholder">
                        <i class="fa fa-image"></i>
                        <span>No images uploaded for this service</span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($has_images && count($gallery_images) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($gallery_images as $index => $img): ?>
                    <?php $img_path = HostedUpload::getImageUrl($img, '../'); ?>
                    <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>"
                         onclick="changeGalleryImage('<?php echo htmlspecialchars($img_path); ?>', this)">
                        <img src="<?php echo htmlspecialchars($img_path); ?>" alt="Thumbnail <?php echo $index + 1; ?>" onerror="this.style.display='none';">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="service-detail-card">
                    <span class="badge"><?php echo htmlspecialchars($service['category_name']); ?></span>

                    <div class="title-row">
                        <h1><?php echo htmlspecialchars($service['service_title']); ?></h1>

                        <!-- Favorite Button -->
                        <button class="favorite-btn-large <?php echo $is_favorited ? 'active' : ''; ?>"
                                data-favorite-btn
                                data-service-id="<?php echo $service_id; ?>"
                                aria-label="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>"
                                title="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>">
                            <i class="<?php echo $is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span><?php echo $is_favorited ? 'Saved' : 'Save'; ?></span>
                        </button>
                    </div>

                    <div class="provider-info">
                            <i class="fa fa-user"></i>
                            <strong><?php echo htmlspecialchars($service['provider_name'] ?: ($service['provider_first_name'] . ' ' . $service['provider_last_name'])); ?></strong>
                            <span class="text-muted"> | </span>
                            <i class="fa fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($service['provider_region']); ?>
                            <?php if ($service['provider_rating'] > 0): ?>
                                <span class="text-muted"> | </span>
                                <i class="fa fa-star text-warning"></i>
                                <?php echo number_format($service['provider_rating'], 1); ?>/5.0
                            <?php endif; ?>
                    </div>

                    <hr>

                    <h5><i class="fa fa-info-circle"></i> About This Service</h5>
                    <p><?php echo nl2br(htmlspecialchars($service['service_description'])); ?></p>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fa fa-map-marker-alt"></i> Location</h6>
                            <p><?php echo htmlspecialchars($service['service_location']); ?></p>
                        </div>
                        <?php if ($service['max_capacity']): ?>
                        <div class="col-md-6">
                            <h6><i class="fa fa-users"></i> Max Capacity</h6>
                            <p><?php echo $service['max_capacity']; ?> people</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($service['languages_spoken']): ?>
                    <div class="mt-3">
                        <h6><i class="fa fa-language"></i> Languages</h6>
                        <p><?php echo htmlspecialchars($service['languages_spoken']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="booking-card">
                    <h3>GHS <?php echo number_format($service['base_price'], 2); ?></h3>
                    <p class="text-muted"><?php echo str_replace('_', ' ', $service['pricing_unit']); ?></p>

                    <hr>

                    <?php if (isset($_SESSION['user_id']) && !$is_provider): ?>
                        <!-- Tourists can book and favorite -->
                        <button class="btn btn-primary w-100 mb-3" onclick="bookService()">
                            <i class="fa fa-calendar-check"></i> Book Now
                        </button>
                        <button class="btn btn-outline-primary w-100 favorite-btn-sidebar <?php echo $is_favorited ? 'active' : ''; ?>"
                                data-favorite-btn
                                data-service-id="<?php echo $service_id; ?>"
                                aria-label="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>">
                            <i class="<?php echo $is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
                            <?php echo $is_favorited ? 'Saved' : 'Add to Favorites'; ?>
                        </button>
                    <?php elseif ($is_own_service): ?>
                        <!-- Provider viewing their own service -->
                        <div class="alert alert-info" style="background: #e8f4f1; border: 1px solid #2d6a4f; color: #1b4332; border-radius: 8px; padding: 15px;">
                            <i class="fa fa-info-circle"></i> This is your service listing
                        </div>
                    <?php elseif ($is_provider): ?>
                        <!-- Provider viewing another provider's service -->
                        <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #856404; color: #856404; border-radius: 8px; padding: 15px;">
                            <i class="fa fa-exclamation-triangle"></i> Providers cannot book services
                        </div>
                    <?php else: ?>
                        <!-- Not logged in -->
                        <a href="../login/login.php" class="btn btn-primary w-100">
                            Sign in to Book
                        </a>
                    <?php endif; ?>

                    <hr>

                    <h6><i class="fa fa-address-book"></i> Contact Provider</h6>
                    <div class="contact-info">
                        <?php if ($service['provider_phone']): ?>
                        <p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($service['provider_phone']); ?></p>
                        <?php endif; ?>
                        <?php if ($service['provider_email']): ?>
                        <p><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($service['provider_email']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="row mt-5" id="reviews">
            <div class="col-12">
                <h3><i class="fa fa-star text-warning"></i> Reviews & Ratings</h3>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($review_stats && $review_stats['total_reviews'] > 0): ?>
                    <!-- Rating Overview -->
                    <div class="service-detail-card review-stats">
                        <div class="rating-overview">
                            <div class="rating-number">
                                <?php echo number_format($review_stats['average_rating'], 1); ?>
                            </div>
                            <div>
                                <div class="rating-stars">
                                    <?php
                                    $avg = round($review_stats['average_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $avg ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                                <p class="mb-0 text-muted"><?php echo $review_stats['total_reviews']; ?> review(s)</p>
                            </div>
                        </div>

                        <!-- Rating Breakdown -->
                        <div class="rating-breakdown">
                            <?php
                            for ($star = 5; $star >= 1; $star--) {
                                $count = $review_stats[$star == 5 ? 'five_star' : ($star == 4 ? 'four_star' : ($star == 3 ? 'three_star' : ($star == 2 ? 'two_star' : 'one_star')))];
                                $percentage = $review_stats['total_reviews'] > 0 ? ($count / $review_stats['total_reviews']) * 100 : 0;
                            ?>
                                <div class="rating-bar">
                                    <span class="rating-bar-label"><?php echo $star; ?> star</span>
                                    <div class="rating-bar-container">
                                        <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="rating-bar-count"><?php echo $count; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Review Submission Form -->
                <?php if ($user_can_review): ?>
                    <div class="review-form">
                        <h4><i class="fa fa-edit"></i> Write a Review</h4>
                        <p class="text-muted">Share your experience with this service</p>

                        <form action="../actions/submit_review_action.php" method="POST">
                            <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">

                            <!-- Booking Selection -->
                            <div class="mb-3">
                                <label class="form-label"><strong>Select Booking to Review</strong></label>
                                <select name="booking_id" class="form-select" required>
                                    <option value="">Choose a booking...</option>
                                    <?php foreach ($user_bookings as $booking): ?>
                                        <?php if ($booking['has_review'] == 0): ?>
                                            <option value="<?php echo $booking['booking_id']; ?>">
                                                <?php echo $booking['booking_reference']; ?> -
                                                <?php echo date('M d, Y', strtotime($booking['service_date'])); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Star Rating -->
                            <div class="mb-3">
                                <label class="form-label"><strong>Your Rating</strong></label>
                                <div class="star-rating">
                                    <input type="radio" name="rating" value="5" id="star5" required>
                                    <label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="4" id="star4">
                                    <label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="3" id="star3">
                                    <label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="2" id="star2">
                                    <label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="1" id="star1">
                                    <label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>

                            <!-- Review Title -->
                            <div class="mb-3">
                                <label for="review_title" class="form-label"><strong>Review Title</strong></label>
                                <input type="text"
                                       class="form-control"
                                       id="review_title"
                                       name="review_title"
                                       placeholder="Summarize your experience"
                                       maxlength="100"
                                       required>
                            </div>

                            <!-- Review Text -->
                            <div class="mb-3">
                                <label for="review_text" class="form-label"><strong>Your Review</strong></label>
                                <textarea class="form-control"
                                          id="review_text"
                                          name="review_text"
                                          rows="5"
                                          placeholder="Tell others about your experience with this service..."
                                          required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i> Submit Review
                            </button>
                        </form>
                    </div>
                <?php elseif (isset($_SESSION['user_id']) && !$is_provider): ?>
                    <?php
                    // Check if user has any bookings (pending, confirmed, or completed)
                    require_once '../classes/booking_class.php';
                    $booking_class = new Booking();
                    $all_user_bookings = $booking_class->get_tourist_bookings($_SESSION['user_id']);
                    $has_booking_for_service = false;
                    $has_pending_booking = false;
                    foreach ($all_user_bookings as $bkg) {
                        if ($bkg['service_id'] == $service_id) {
                            $has_booking_for_service = true;
                            if ($bkg['booking_status'] != 'completed') {
                                $has_pending_booking = true;
                            }
                        }
                    }
                    ?>
                    <?php if ($has_pending_booking): ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>You have a booking for this service!</strong><br>
                            You'll be able to write a review once your booking is marked as "completed" by the provider after you've used the service.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> You can write a review after booking and completing this service.
                            <a href="#" onclick="document.querySelector('.btn-primary').scrollIntoView({behavior: 'smooth'}); return false;" class="alert-link">Book now</a> to get started!
                        </div>
                    <?php endif; ?>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Please <a href="../login/login.php">sign in</a> to write a review.
                    </div>
                <?php endif; ?>

                <!-- Display Reviews -->
                <?php if ($reviews && count($reviews) > 0): ?>
                    <h4 class="mt-4 mb-3">Customer Reviews (<?php echo count($reviews); ?>)</h4>

                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <h6><?php echo htmlspecialchars($review['tourist_first_name'] . ' ' . $review['tourist_last_name']); ?></h6>
                                    <p class="text-muted mb-0">
                                        <?php echo date('F j, Y', strtotime($review['review_date'])); ?>
                                        <?php if ($review['service_date']): ?>
                                            | Service Date: <?php echo date('M j, Y', strtotime($review['service_date'])); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <h5 class="review-title"><?php echo htmlspecialchars($review['review_title']); ?></h5>
                            <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>

                            <!-- Provider Response -->
                            <?php if (!empty($review['response_from_provider'])): ?>
                                <div class="provider-response">
                                    <div class="provider-response-label">
                                        <i class="fa fa-reply"></i> Provider Response
                                    </div>
                                    <p class="provider-response-text mb-0">
                                        <?php echo nl2br(htmlspecialchars($review['response_from_provider'])); ?>
                                    </p>
                                    <small class="text-muted">
                                        <?php echo date('F j, Y', strtotime($review['response_date'])); ?>
                                    </small>
                                </div>
                            <?php elseif ($is_own_service): ?>
                                <!-- Provider can respond -->
                                <div class="response-form">
                                    <form class="reply-form" data-review-id="<?php echo $review['review_id']; ?>" data-service-id="<?php echo $service_id; ?>">
                                        <label class="form-label"><strong>Respond to this review</strong></label>
                                        <textarea class="form-control mb-2"
                                                  name="provider_response"
                                                  rows="3"
                                                  placeholder="Thank your customer and address their feedback..."
                                                  required></textarea>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fa fa-reply"></i> Post Response
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (!$user_can_review): ?>
                    <div class="service-detail-card text-center py-5">
                        <i class="far fa-comments fa-3x text-muted mb-3"></i>
                        <h5>No reviews yet</h5>
                        <p class="text-muted">Be the first to review this service!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade booking-modal" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Request Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        <input type="hidden" id="service_id" name="service_id" value="<?php echo $service_id; ?>">

                        <!-- Service Header -->
                        <div class="service-header">
                            <div class="service-details">
                                <h4><?php echo htmlspecialchars($service['service_title']); ?></h4>
                                <p class="provider-name"><?php echo htmlspecialchars($service['provider_name'] ?: ($service['provider_first_name'] . ' ' . $service['provider_last_name'])); ?></p>
                            </div>
                            <div class="service-price-box">
                                <div class="price">GHS <?php echo number_format($service['base_price'], 2); ?></div>
                                <div class="price-unit"><?php echo str_replace('_', ' ', $service['pricing_unit']); ?></div>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="form-row">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="service_date" class="form-label">Date</label>
                                <input type="date"
                                       class="form-control"
                                       id="service_date"
                                       name="service_date"
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                       required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="service_time" class="form-label">Start Time</label>
                                <select class="form-select" id="service_time" name="service_time" required>
                                    <option value="">Select time</option>
                                    <option value="06:00">6:00 AM</option>
                                    <option value="07:00">7:00 AM</option>
                                    <option value="08:00">8:00 AM</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <!-- Duration (for per_hour or per_day services) -->
                        <?php if ($service['pricing_unit'] === 'per_hour'): ?>
                        <div class="form-group" id="durationGroup">
                            <label for="service_duration" class="form-label">Duration (Hours)</label>
                            <select class="form-select" id="service_duration" name="service_duration" required onchange="updatePricing()">
                                <option value="1">1 hour</option>
                                <option value="2">2 hours</option>
                                <option value="3">3 hours</option>
                                <option value="4">4 hours</option>
                                <option value="5">5 hours</option>
                                <option value="6">6 hours</option>
                                <option value="8">8 hours (Full day)</option>
                                <option value="10">10 hours</option>
                                <option value="12">12 hours</option>
                            </select>
                        </div>
                        <?php elseif ($service['pricing_unit'] === 'per_day'): ?>
                        <div class="form-group" id="durationGroup">
                            <label for="service_duration" class="form-label">Number of Days</label>
                            <select class="form-select" id="service_duration" name="service_duration" required onchange="updatePricing()">
                                <option value="1">1 day</option>
                                <option value="2">2 days</option>
                                <option value="3">3 days</option>
                                <option value="4">4 days</option>
                                <option value="5">5 days</option>
                                <option value="6">6 days</option>
                                <option value="7">1 week</option>
                                <option value="14">2 weeks</option>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" id="service_duration" name="service_duration" value="1">
                        <?php endif; ?>

                        <!-- Number of Guests -->
                        <div class="form-group">
                            <label class="form-label">Number of Guests</label>
                            <div class="guest-counter-wrapper">
                                <div class="guest-counter">
                                    <button type="button" id="decreaseGuests" onclick="updateGuestCount(-1)">−</button>
                                    <span class="guest-count" id="guestCountDisplay">1</span>
                                    <button type="button" id="increaseGuests" onclick="updateGuestCount(1)">+</button>
                                </div>
                                <input type="hidden" name="number_of_people" id="number_of_people" value="1">
                                <?php if ($service['max_capacity']): ?>
                                    <span class="max-capacity">Max <?php echo $service['max_capacity']; ?> guests</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div class="form-group">
                            <label for="special_requests" class="form-label">Special Requests (Optional)</label>
                            <textarea class="form-control"
                                      id="special_requests"
                                      name="special_requests"
                                      placeholder="Any dietary needs, accessibility requirements, or special occasions..."></textarea>
                        </div>

                        <!-- Price Summary -->
                        <div class="price-summary">
                            <div class="price-row">
                                <span>Base rate</span>
                                <span>GHS <?php echo number_format($service['base_price'], 2); ?> / <?php echo str_replace('per_', '', $service['pricing_unit']); ?></span>
                            </div>
                            <?php if ($service['pricing_unit'] === 'per_hour' || $service['pricing_unit'] === 'per_day'): ?>
                            <div class="price-row" id="durationPriceRow">
                                <span>× <span id="durationMultiplier">1</span> <?php echo $service['pricing_unit'] === 'per_hour' ? 'hour(s)' : 'day(s)'; ?></span>
                                <span id="durationSubtotal">GHS <?php echo number_format($service['base_price'], 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($service['pricing_unit'] === 'per_person'): ?>
                            <div class="price-row" id="guestPriceRow">
                                <span>× <span id="guestMultiplier">1</span> guest(s)</span>
                                <span id="subtotalAmount">GHS <?php echo number_format($service['base_price'], 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="price-row total">
                                <span>Total</span>
                                <span class="amount" id="totalAmount">GHS <?php echo number_format($service['base_price'], 2); ?></span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-book" onclick="submitBooking()">Request Booking</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/review_reply.js"></script>
    <script src="../js/review_submit.js"></script>
    <script src="../js/favorites.js"></script>
    <script src="../js/single_service.js"></script>
</body>
</html>
