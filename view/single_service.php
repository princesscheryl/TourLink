<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../classes/service_provider_class.php';
require_once '../controllers/favorite_controller.php';
require_once '../controllers/review_controller.php';

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
    <script src="../js/dark-mode.js"></script>
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
        }

        .nav-link:hover {
            color: #2d6a4f;
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

        /* Breadcrumb */
        .breadcrumb-nav {
            margin-bottom: 30px;
        }

        .breadcrumb-nav a {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }

        /* Image Gallery */
        .service-gallery {
            margin-bottom: 24px;
            border-radius: 16px;
            overflow: hidden;
        }

        .gallery-main {
            position: relative;
            width: 100%;
            height: 400px;
            background: #f0f0f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
        }

        .gallery-placeholder i {
            font-size: 4rem;
            opacity: 0.5;
            margin-bottom: 16px;
        }

        .gallery-placeholder span {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        .gallery-thumbs {
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .gallery-thumb {
            width: 80px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }

        .gallery-thumb.active {
            border-color: #2d6a4f;
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-thumb:hover {
            border-color: #2d6a4f;
        }

        /* Service Card */
        .service-detail-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .badge {
            background: #2d6a4f !important;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-block;
        }

        h1 {
            font-weight: 800;
            color: #1a1a1a;
            margin: 15px 0 20px;
            font-size: 2rem;
        }

        .provider-info {
            color: #666;
            margin-bottom: 20px;
        }

        .provider-info i {
            color: #2d6a4f;
            margin-right: 5px;
        }

        .provider-info strong {
            color: #1a1a1a;
        }

        h5, h6 {
            font-weight: 700;
            color: #1b4332;
            margin-top: 20px;
            margin-bottom: 12px;
        }

        h5 i, h6 i {
            color: #2d6a4f;
            margin-right: 8px;
        }

        hr {
            border-top: 1px solid #e9ecef;
            margin: 24px 0;
        }

        /* Sidebar Booking Card */
        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 90px;
        }

        .booking-card h3 {
            color: #2d6a4f;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .booking-card .text-muted {
            color: #666 !important;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #2d6a4f;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            border: 2px solid #2d6a4f;
            color: #2d6a4f;
            background: transparent;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-primary:hover {
            background: #2d6a4f;
            color: white;
            border-color: #2d6a4f;
        }

        /* Contact Info */
        .contact-info p {
            margin-bottom: 12px;
            color: #333;
        }

        .contact-info i {
            color: #2d6a4f;
            margin-right: 8px;
            width: 20px;
        }

        .text-warning {
            color: #ffd700 !important;
        }

        /* Service Details */
        .service-detail-card p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .location-info, .capacity-info, .language-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        /* Favorite Button */
        .favorite-btn-large {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .favorite-btn-large:hover {
            border-color: #dc3545;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
        }

        .favorite-btn-large i {
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .favorite-btn-large.active {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .favorite-btn-large.active i {
            color: #dc3545;
        }

        .favorite-btn-large:not(.active) i {
            color: #999;
        }

        .title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .title-row h1 {
            margin: 0;
            flex: 1;
        }

        /* Reviews Section */
        .reviews-section {
            margin-top: 30px;
        }

        .review-stats {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .rating-overview {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .rating-number {
            font-size: 3rem;
            font-weight: 800;
            color: #2d6a4f;
        }

        .rating-stars {
            color: #ffd700;
            font-size: 1.5rem;
        }

        .rating-breakdown {
            margin-top: 10px;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .rating-bar-label {
            min-width: 60px;
            font-size: 0.9rem;
            color: #666;
        }

        .rating-bar-container {
            flex: 1;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: #ffd700;
            transition: width 0.3s;
        }

        .rating-bar-count {
            min-width: 30px;
            font-size: 0.9rem;
            color: #666;
            text-align: right;
        }

        /* Review Form */
        .review-form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
            margin: 15px 0;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #ddd;
            transition: color 0.2s;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffd700;
        }

        /* Individual Review */
        .review-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .reviewer-info h6 {
            margin: 0 0 5px 0;
            color: #1a1a1a;
            font-weight: 600;
        }

        .reviewer-info .text-muted {
            font-size: 0.85rem;
            color: #666;
        }

        .review-rating {
            color: #ffd700;
            font-size: 1.1rem;
        }

        .review-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .review-text {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .provider-response {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #2d6a4f;
            margin-top: 15px;
        }

        .provider-response-label {
            font-weight: 600;
            color: #2d6a4f;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .provider-response-text {
            color: #555;
            line-height: 1.6;
        }

        .response-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }

        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
        }

        .alert-info {
            background: #cff4fc;
            border: 1px solid #b6effb;
            color: #055160;
        }

        /* Booking Modal */
        .booking-modal .modal-dialog {
            max-width: 440px;
        }

        .booking-modal .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .booking-modal .modal-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 18px 24px;
        }

        .booking-modal .modal-header .modal-title {
            font-weight: 600;
            font-size: 1.15rem;
            color: #222;
        }

        .booking-modal .modal-header .btn-close {
            opacity: 0.5;
            transition: opacity 0.2s;
        }

        .booking-modal .modal-header .btn-close:hover {
            opacity: 1;
        }

        .booking-modal .modal-body {
            padding: 24px;
            background: white;
        }

        /* Service Header */
        .booking-modal .service-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .booking-modal .service-details h4 {
            font-size: 1.05rem;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 6px 0;
            line-height: 1.4;
        }

        .booking-modal .service-details .provider-name {
            font-size: 0.875rem;
            color: #666;
            margin: 0;
        }

        .booking-modal .service-price-box {
            text-align: right;
        }

        .booking-modal .service-price-box .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1b4332;
        }

        .booking-modal .service-price-box .price-unit {
            font-size: 0.8rem;
            color: #888;
        }

        /* Form Styles */
        .booking-modal .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }

        .booking-modal .form-group {
            margin-bottom: 18px;
        }

        .booking-modal .form-group:last-child {
            margin-bottom: 0;
        }

        .booking-modal .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .booking-modal .form-control,
        .booking-modal .form-select {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.9rem;
            background: #fafafa;
            transition: all 0.2s;
            color: #333;
        }

        .booking-modal .form-control:hover,
        .booking-modal .form-select:hover {
            border-color: #ccc;
            background: #f5f5f5;
        }

        .booking-modal .form-control:focus,
        .booking-modal .form-select:focus {
            border-color: #1b4332;
            background: white;
            box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1);
            outline: none;
        }

        .booking-modal textarea.form-control {
            resize: none;
            min-height: 70px;
        }

        /* Guest Counter */
        .booking-modal .guest-counter-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .booking-modal .guest-counter {
            display: inline-flex;
            align-items: center;
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .booking-modal .guest-counter button {
            width: 44px;
            height: 44px;
            border: none;
            background: transparent;
            font-size: 1.25rem;
            color: #1b4332;
            cursor: pointer;
            transition: background 0.15s;
        }

        .booking-modal .guest-counter button:hover:not(:disabled) {
            background: #e8f5e9;
        }

        .booking-modal .guest-counter button:disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .booking-modal .guest-counter .guest-count {
            min-width: 50px;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            padding: 0 8px;
        }

        .booking-modal .max-capacity {
            font-size: 0.8rem;
            color: #888;
        }

        /* Price Summary */
        .booking-modal .price-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
        }

        .booking-modal .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 0.9rem;
            color: #555;
        }

        .booking-modal .price-row.total {
            border-top: 1px solid #e0e0e0;
            margin-top: 10px;
            padding-top: 12px;
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .booking-modal .price-row.total .amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1b4332;
        }

        /* Modal Footer */
        .booking-modal .modal-footer {
            background: white;
            border-top: 1px solid #eee;
            padding: 16px 24px;
            gap: 12px;
        }

        .booking-modal .btn-cancel {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            background: white;
            border: 1px solid #ddd;
            color: #555;
            transition: all 0.2s;
        }

        .booking-modal .btn-cancel:hover {
            background: #f5f5f5;
            border-color: #ccc;
        }

        .booking-modal .btn-book {
            flex: 1;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            background: #1b4332;
            border: none;
            color: white;
            transition: all 0.2s;
        }

        .booking-modal .btn-book:hover {
            background: #153a2b;
        }

        @media (max-width: 480px) {
            .booking-modal .modal-dialog {
                margin: 16px;
            }

            .booking-modal .form-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .booking-modal .service-header {
                flex-direction: column;
                gap: 12px;
            }

            .booking-modal .service-price-box {
                text-align: left;
            }
        }
    </style>
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
                    <?php $first_img = '../' . $gallery_images[0]; ?>
                    <?php if (file_exists($first_img)): ?>
                        <img src="<?php echo htmlspecialchars($first_img); ?>"
                             alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                             id="mainGalleryImage">
                    <?php else: ?>
                        <div class="gallery-placeholder">
                            <i class="fa fa-image"></i>
                            <span>Image not available</span>
                        </div>
                    <?php endif; ?>
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
                    <?php $img_path = '../' . $img; ?>
                    <?php if (file_exists($img_path)): ?>
                    <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>"
                         onclick="changeGalleryImage('<?php echo htmlspecialchars($img_path); ?>', this)">
                        <img src="<?php echo htmlspecialchars($img_path); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                    </div>
                    <?php endif; ?>
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
                                    <form action="../actions/respond_to_review_action.php" method="POST">
                                        <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">

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
                        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">

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
                        <input type="hidden" name="service_duration" value="1">
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
    <script src="../js/favorites.js"></script>
    <script>
        // Service data for pricing calculation
        const serviceData = {
            basePrice: <?php echo $service['base_price']; ?>,
            pricingUnit: '<?php echo $service['pricing_unit']; ?>',
            maxCapacity: <?php echo $service['max_capacity'] ?: 'null'; ?>
        };

        let guestCount = 1;

        function bookService() {
            const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
            modal.show();
        }

        function updateGuestCount(change) {
            const newCount = guestCount + change;
            const maxCapacity = serviceData.maxCapacity || 20;

            if (newCount >= 1 && newCount <= maxCapacity) {
                guestCount = newCount;
                document.getElementById('guestCountDisplay').textContent = guestCount;
                document.getElementById('number_of_people').value = guestCount;
                document.getElementById('guestMultiplier').textContent = guestCount;

                // Update decrease button state
                document.getElementById('decreaseGuests').disabled = (guestCount <= 1);
                document.getElementById('increaseGuests').disabled = (guestCount >= maxCapacity);

                // Update pricing
                updatePricing();
            }
        }

        function updatePricing() {
            let total = serviceData.basePrice;
            let duration = 1;

            // Get duration if applicable
            const durationSelect = document.getElementById('service_duration');
            if (durationSelect) {
                duration = parseInt(durationSelect.value) || 1;
            }

            // Calculate based on pricing unit
            if (serviceData.pricingUnit === 'per_hour' || serviceData.pricingUnit === 'per_day') {
                total = serviceData.basePrice * duration;

                // Update duration display
                const durationMultiplier = document.getElementById('durationMultiplier');
                const durationSubtotal = document.getElementById('durationSubtotal');
                if (durationMultiplier) durationMultiplier.textContent = duration;
                if (durationSubtotal) durationSubtotal.textContent = 'GHS ' + formatNumber(total);
            }

            if (serviceData.pricingUnit === 'per_person') {
                total = serviceData.basePrice * guestCount;
                const subtotalEl = document.getElementById('subtotalAmount');
                const guestMultiplier = document.getElementById('guestMultiplier');
                if (guestMultiplier) guestMultiplier.textContent = guestCount;
                if (subtotalEl) subtotalEl.textContent = 'GHS ' + formatNumber(total);
            }

            document.getElementById('totalAmount').textContent = 'GHS ' + formatNumber(total);
        }

        function formatNumber(num) {
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Gallery image switching
        function changeGalleryImage(src, thumbElement) {
            const mainImage = document.getElementById('mainGalleryImage');
            if (mainImage) {
                mainImage.src = src;
            }
            // Update active state
            document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
            if (thumbElement) {
                thumbElement.classList.add('active');
            }
        }

        function submitBooking() {
            const form = document.getElementById('bookingForm');
            if (!form) {
                console.error('Booking form not found');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Booking form not found. Please refresh the page and try again.',
                    confirmButtonColor: '#2d6a4f'
                });
                return;
            }

            const serviceDate = document.getElementById('service_date') ? document.getElementById('service_date').value : '';
            const serviceTime = document.getElementById('service_time') ? document.getElementById('service_time').value : '';
            const serviceId = document.getElementById('service_id') ? document.getElementById('service_id').value : '';
            const numberOfPeople = document.getElementById('number_of_people') ? document.getElementById('number_of_people').value : '1';
            const serviceDurationEl = document.getElementById('service_duration');
            const serviceDuration = serviceDurationEl ? serviceDurationEl.value : '1';

            // Validate form
            if (!serviceDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Date Required',
                    text: 'Please select a date for your booking.',
                    confirmButtonColor: '#2d6a4f'
                });
                return;
            }

            if (!serviceTime) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Time Required',
                    text: 'Please select a preferred time.',
                    confirmButtonColor: '#2d6a4f'
                });
                return;
            }

            if (!serviceId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Service ID is missing. Please refresh the page and try again.',
                    confirmButtonColor: '#2d6a4f'
                });
                return;
            }

            // Redirect to booking page with parameters
            const params = new URLSearchParams({
                service_id: serviceId,
                date: serviceDate,
                time: serviceTime,
                guests: numberOfPeople,
                duration: serviceDuration
            });

            window.location.href = 'book_service.php?' + params.toString();
        }

        // Legacy AJAX booking (deprecated - keeping for reference)
        function submitBookingOld() {
            const form = document.getElementById('bookingForm');
            const serviceDate = document.getElementById('service_date').value;
            const serviceTime = document.getElementById('service_time').value;

            if (!serviceDate || !serviceTime) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields',
                    text: 'Please select date and time.',
                    confirmButtonColor: '#2d6a4f'
                });
                return;
            }

            const bookBtn = document.querySelector('.btn-book');
            const originalText = bookBtn.innerHTML;
            bookBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Processing...';
            bookBtn.disabled = true;

            $.ajax({
                url: '../actions/create_booking_action.php',
                method: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Booking Requested!',
                            html: `
                                <p>Your booking request has been submitted successfully.</p>
                                <p><strong>Reference:</strong> ${response.booking_reference}</p>
                                <p class="text-muted">The service provider will review and confirm your booking.</p>
                            `,
                            confirmButtonColor: '#2d6a4f',
                            confirmButtonText: 'View My Bookings'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'my_bookings.php';
                            }
                        });
                    } else {
                        if (response.require_login) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Login Required',
                                text: response.message,
                                confirmButtonColor: '#2d6a4f',
                                confirmButtonText: 'Sign In'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '../login/login.php';
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Booking Failed',
                                text: response.message,
                                confirmButtonColor: '#2d6a4f'
                            });
                        }
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#2d6a4f'
                    });
                },
                complete: function() {
                    bookBtn.innerHTML = originalText;
                    bookBtn.disabled = false;
                }
            });
        }

        // Initialize on page load
        $(document).ready(function() {
            // Set minimum date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('service_date').min = tomorrow.toISOString().split('T')[0];

            // Initialize button states
            document.getElementById('decreaseGuests').disabled = true;
        });

        // Update button text when favorite is toggled
        $(document).on('favoriteToggled', function(event, data) {
            if (data.serviceId == <?php echo $service_id; ?>) {
                // Update large favorite button (top of page)
                const buttonLarge = $('.favorite-btn-large');
                const span = buttonLarge.find('span');
                const iconLarge = buttonLarge.find('i');

                if (data.action === 'added') {
                    span.text('Saved');
                    iconLarge.removeClass('far').addClass('fas');
                    buttonLarge.addClass('active');
                } else {
                    span.text('Save');
                    iconLarge.removeClass('fas').addClass('far');
                    buttonLarge.removeClass('active');
                }

                // Update sidebar favorite button
                const buttonSidebar = $('.favorite-btn-sidebar');
                const iconSidebar = buttonSidebar.find('i');

                if (data.action === 'added') {
                    buttonSidebar.html('<i class="fas fa-heart"></i> Saved');
                    buttonSidebar.addClass('active');
                    buttonSidebar.attr('aria-label', 'Remove from favorites');
                } else {
                    buttonSidebar.html('<i class="far fa-heart"></i> Add to Favorites');
                    buttonSidebar.removeClass('active');
                    buttonSidebar.attr('aria-label', 'Add to favorites');
                }
            }
        });
    </script>
</body>
</html>
