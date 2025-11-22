<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../settings/core.php';

// Check if user is logged in - redirect if not
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = getUserID();

// Get user's favorites - with error handling
$favorites = [];
try {
    require_once '../controllers/favorite_controller.php';
    $favorites = get_user_favorites_ctr($user_id);
    if (!$favorites) {
        $favorites = [];
    }
} catch (Exception $e) {
    error_log("Favorites error: " . $e->getMessage());
    $favorites = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="favorites.page_title">My Favorites - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/translator.js"></script>
    <script src="../js/accessibility.js"></script>
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

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 120px 0 60px;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Service Cards */
        .service-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 24px;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(45, 106, 79, 0.2);
        }

        .service-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .service-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .badge {
            background: #2d6a4f !important;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-block;
            width: fit-content;
        }

        .service-content h5 {
            font-weight: 700;
            color: #1a1a1a;
            margin: 12px 0;
            font-size: 1.1rem;
        }

        .service-content .text-muted {
            color: #666 !important;
        }

        .service-content .text-primary {
            color: #2d6a4f !important;
            font-size: 1.3rem;
            font-weight: 800;
        }

        .btn-primary {
            background: #2d6a4f;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        /* Favorite Button */
        .favorite-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.3s;
            z-index: 10;
        }

        .favorite-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .favorite-btn i {
            color: #dc3545;
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .favorite-btn.active i,
        .favorite-btn i.fas {
            color: #dc3545;
        }

        .favorite-btn:not(.active) i {
            color: #999;
        }

        /* Empty State */
        .empty-favorites {
            text-align: center;
            padding: 100px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .empty-favorites i {
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-favorites h3 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-favorites p {
            color: #666;
            margin-bottom: 30px;
        }

        /* Services Count */
        .services-count {
            color: #666;
            font-weight: 500;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }

        .services-count strong {
            color: #2d6a4f;
            font-weight: 700;
        }

        /* Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px 60px;
        }

        /* Dark Mode Styles */
        [data-theme="dark"] body {
            background: #1a1a1a !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .main-nav {
            background: #2d2d2d !important;
        }

        [data-theme="dark"] .logo {
            color: #52b788 !important;
        }

        [data-theme="dark"] .nav-link {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .nav-link:hover,
        [data-theme="dark"] .nav-link.active {
            color: #52b788 !important;
        }

        [data-theme="dark"] .language-selector {
            background: #3d3d3d !important;
            border-color: #505050 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .theme-toggle-btn {
            background: #3d3d3d !important;
            border-color: #505050 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .theme-toggle-btn:hover {
            background: #4d4d4d !important;
        }

        [data-theme="dark"] .page-header {
            background: linear-gradient(135deg, #1b4332 0%, #0d2418 100%) !important;
        }

        [data-theme="dark"] .service-card {
            background: #2d2d2d !important;
            border-color: #404040 !important;
        }

        [data-theme="dark"] .service-card h5 {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .service-card p {
            color: #b0b0b0 !important;
        }

        [data-theme="dark"] .empty-favorites {
            background: #2d2d2d !important;
        }

        [data-theme="dark"] .empty-favorites h3 {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .empty-favorites p {
            color: #b0b0b0 !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 20px;
            }

            .nav-left, .nav-right {
                gap: 10px;
            }

            .language-selector {
                min-width: 80px;
                font-size: 0.8rem;
                padding: 6px 8px;
            }

            .theme-toggle-btn {
                width: 36px;
                height: 36px;
            }

            .nav-link {
                font-size: 0.85rem;
            }

            .btn-nav {
                padding: 8px 16px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 1.4rem;
            }

            .language-selector {
                min-width: 70px;
                font-size: 0.75rem;
                padding: 5px 6px;
            }

            .theme-toggle-btn {
                width: 32px;
                height: 32px;
            }

            .nav-link:not(.active) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link" data-i18n="accessibility.skip_to_content">Skip to main content</a>

    <!-- Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <!-- Page Header -->
    <div class="page-header" id="main-content" role="main">
        <div class="main-container">
            <h1 data-i18n="favorites.my_favorites">My Favorites</h1>
            <p data-i18n="favorites.page_description">Services you've saved for later</p>
        </div>
    </div>

    <div class="main-container">
        <?php if ($favorites && count($favorites) > 0): ?>
            <p class="services-count">
                <strong><?php echo count($favorites); ?></strong>
                <span data-i18n="favorites.services_saved">service(s) saved</span>
            </p>
            <div class="row favorites-grid">
                <?php foreach ($favorites as $favorite): ?>
                    <div class="col-md-4">
                        <div class="service-card" data-service-id="<?php echo $favorite['service_id']; ?>">
                            <!-- Favorite Button -->
                            <button class="favorite-btn active"
                                    data-favorite-btn
                                    data-service-id="<?php echo $favorite['service_id']; ?>"
                                    aria-label="Remove from favorites"
                                    title="Remove from favorites">
                                <i class="fas fa-heart"></i>
                            </button>

                            <?php
                            // Handle service_image (JSON array or single path)
                            $service_image = $favorite['service_image'] ?? null;
                            $image_url = null;

                            if ($service_image) {
                                // Check if it's a JSON array (multiple images)
                                $images = json_decode($service_image, true);
                                if (is_array($images) && count($images) > 0) {
                                    // Use first image from array
                                    $first_image = $images[0];
                                    if (file_exists('../' . $first_image)) {
                                        $image_url = '../' . $first_image;
                                    }
                                } elseif (file_exists('../' . $service_image)) {
                                    // Single image path (old format)
                                    $image_url = '../' . $service_image;
                                }
                            }
                            ?>

                            <?php if ($image_url): ?>
                                <img src="<?php echo htmlspecialchars($image_url); ?>"
                                     alt="<?php echo htmlspecialchars($favorite['service_name']); ?>"
                                     class="service-image">
                            <?php else: ?>
                                <div class="service-image" style="background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                                </div>
                            <?php endif; ?>

                            <div class="service-content">
                                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($favorite['category_name']); ?></span>
                                <h5><?php echo htmlspecialchars($favorite['service_name']); ?></h5>
                                <p class="text-muted small">
                                    <i class="fa fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($favorite['location'] . ', ' . $favorite['region']); ?>
                                </p>
                                <p class="text-muted small">
                                    <i class="fa fa-user"></i>
                                    <?php echo htmlspecialchars($favorite['business_name']); ?>
                                </p>
                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars(substr($favorite['service_description'], 0, 100)); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div>
                                        <strong class="text-primary">GHS <?php echo number_format($favorite['base_price'], 2); ?></strong>
                                        <br><small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $favorite['pricing_type'])); ?></small>
                                    </div>
                                    <a href="single_service.php?id=<?php echo $favorite['service_id']; ?>"
                                       class="btn btn-sm btn-primary"
                                       data-i18n="service.view_details">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-favorites">
                <i class="far fa-heart fa-4x"></i>
                <h3 data-i18n="favorites.no_favorites">No favorites yet</h3>
                <p data-i18n="favorites.start_adding">Start adding services to your favorites to see them here.</p>
                <a href="all_services.php" class="btn btn-primary" data-i18n="favorites.browse_services">Browse Services</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/favorites.js"></script>
    <script src="../js/accessibility.js"></script>
</body>
</html>
