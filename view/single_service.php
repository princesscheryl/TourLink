<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../classes/service_provider_class.php';
require_once '../controllers/favorite_controller.php';

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
                        <button class="btn btn-outline-primary w-100">
                            <i class="fa fa-heart"></i> Add to Favorites
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/favorites.js"></script>
    <script>
        function bookService() {
            alert('Booking functionality coming soon!');
        }

        // Update button text when favorite is toggled
        $(document).on('favoriteToggled', function(event, data) {
            if (data.serviceId == <?php echo $service_id; ?>) {
                const button = $('.favorite-btn-large');
                const span = button.find('span');
                const icon = button.find('i');

                if (data.action === 'added') {
                    span.text('Saved');
                    icon.removeClass('far').addClass('fas');
                    button.addClass('active');
                } else {
                    span.text('Save');
                    icon.removeClass('fas').addClass('far');
                    button.removeClass('active');
                }
            }
        });
    </script>
</body>
</html>
