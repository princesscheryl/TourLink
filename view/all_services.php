<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../controllers/service_category_controller.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get services
if ($category_filter) {
    $services = get_services_by_category_ctr($category_filter);
} else {
    $services = get_all_services_ctr();
}

// Get all categories for filter
$categories = get_all_service_categories_ctr();

// Get user's favorites if logged in (wrapped in try-catch to prevent errors)
$user_favorites = [];
$favorites_enabled = false;

if (isset($_SESSION['user_id'])) {
    try {
        require_once '../controllers/favorite_controller.php';
        $user_id = $_SESSION['user_id'];
        $favorites_list = get_user_favorites_ctr($user_id);
        if ($favorites_list) {
            foreach ($favorites_list as $fav) {
                $user_favorites[] = $fav['service_id'];
            }
        }
        $favorites_enabled = true;
    } catch (Exception $e) {
        // Favorites feature not available - continue without it
        $user_favorites = [];
        $favorites_enabled = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Services - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        .btn-nav-join {
            background: #2d6a4f;
            color: white;
        }

        .btn-nav-join:hover {
            background: #1b4332;
            transform: translateY(-2px);
            color: white;
        }

        .btn-nav-logout {
            background: #dc3545;
            color: white;
        }

        .btn-nav-logout:hover {
            background: #c82333;
        }

        .language-selector {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #1a1a1a;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 100px;
        }

        .language-selector:hover {
            border-color: #2d6a4f;
        }

        .theme-toggle-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            color: #1a1a1a;
        }

        .theme-toggle-btn:hover {
            border-color: #2d6a4f;
            background: #f0f7f4;
        }

        .nav-user {
            color: #333;
            font-weight: 500;
        }

        .cart-count {
            background: #ffd700;
            color: #1b4332;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 5px;
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

        /* Filter Sidebar */
        .filter-sidebar {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 90px;
        }

        .filter-sidebar h5 {
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .category-filter {
            display: block;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .category-filter:hover {
            background: #2d6a4f;
            color: white;
            transform: translateX(5px);
        }

        .category-filter.active {
            background: #2d6a4f;
            color: white;
        }

        .category-filter i {
            margin-right: 10px;
            width: 20px;
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

        .favorite-btn.active i {
            color: #dc3545;
        }

        .favorite-btn:not(.active) i {
            color: #999;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state i {
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
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

        [data-theme="dark"] .filter-section {
            background: #2d2d2d !important;
        }

        [data-theme="dark"] .filter-btn {
            background: #3d3d3d !important;
            color: #e0e0e0 !important;
            border-color: #505050 !important;
        }

        [data-theme="dark"] .filter-btn.active {
            background: #52b788 !important;
            border-color: #52b788 !important;
            color: #1a1a1a !important;
        }

        [data-theme="dark"] .service-card {
            background: #2d2d2d !important;
            border-color: #404040 !important;
        }

        [data-theme="dark"] .service-card h3 {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .service-card p {
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
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <!-- Navigation -->
    <nav class="main-nav" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="all_services.php" class="nav-link active" data-i18n="nav.destinations">Browse Services</a>
                <a href="cart.php" class="nav-link">
                    <i class="fa fa-shopping-cart"></i> <span data-i18n="nav.cart">Cart</span>
                    <span class="cart-count">0</span>
                </a>

                <!-- Language Switcher -->
                <select id="languageSelector" class="language-selector" aria-label="Select language" onchange="changeLanguage(this.value)">
                    <option value="en">English</option>
                    <option value="fr">Français</option>
                    <option value="es">Español</option>
                    <option value="tw">Twi</option>
                    <option value="ga">Ga</option>
                </select>

                <!-- Theme Toggle -->
                <button onclick="toggleTheme()" class="theme-toggle-btn" id="publicThemeToggle" aria-label="Toggle dark mode">
                    <i class="fa fa-moon"></i>
                </button>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="nav-user">
                        <i class="fa fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="../login/logout.php" class="btn-nav btn-nav-logout" data-i18n="nav.logout">Logout</a>
                <?php else: ?>
                    <a href="../login/login.php" class="nav-link" data-i18n="nav.sign_in">Sign in</a>
                    <a href="../login/register.php" class="btn-nav btn-nav-join" data-i18n="nav.join">Join</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header" id="main-content" role="main">
        <div class="main-container">
            <h1>Browse Tourism Services</h1>
            <p>Discover amazing experiences across Ghana</p>
        </div>
    </div>

    <div class="main-container">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-md-3">
                <div class="filter-sidebar">
                    <h5 class="mb-3">Categories</h5>
                    <a href="all_services.php" class="category-filter <?php echo !$category_filter ? 'active' : ''; ?>">
                        <i class="fa fa-list"></i> All Services
                    </a>
                    <?php if ($categories): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="all_services.php?category=<?php echo $category['category_id']; ?>"
                               class="category-filter <?php echo $category_filter == $category['category_id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="col-md-9">
                <?php if ($services && count($services) > 0): ?>
                    <p class="services-count"><strong><?php echo count($services); ?></strong> service(s) found</p>
                    <div class="row">
                        <?php foreach ($services as $service): ?>
                            <div class="col-md-4">
                                <div class="service-card">
                                    <?php if ($favorites_enabled): ?>
                                        <?php
                                        // Check if service is favorited
                                        $is_favorited = in_array($service['service_id'], $user_favorites);
                                        $heart_class = $is_favorited ? 'fas' : 'far';
                                        $btn_class = $is_favorited ? 'active' : '';
                                        ?>

                                        <!-- Favorite Button -->
                                        <button class="favorite-btn <?php echo $btn_class; ?>"
                                                data-favorite-btn
                                                data-service-id="<?php echo $service['service_id']; ?>"
                                                aria-label="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>"
                                                title="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>">
                                            <i class="<?php echo $heart_class; ?> fa-heart"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php
                                    $images = json_decode($service['service_images'], true);
                                    $first_image = is_array($images) && !empty($images) ? $images[0] : null;
                                    ?>
                                    <?php if ($first_image): ?>
                                        <img src="<?php echo htmlspecialchars($first_image); ?>"
                                             alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                             class="service-image">
                                    <?php else: ?>
                                        <div class="service-image" style="background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="service-content">
                                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($service['category_name']); ?></span>
                                        <h5><?php echo htmlspecialchars($service['service_title']); ?></h5>
                                        <p class="text-muted small">
                                            <i class="fa fa-user"></i>
                                            <?php echo htmlspecialchars($service['provider_name'] ?: ($service['provider_first_name'] . ' ' . $service['provider_last_name'])); ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <?php echo htmlspecialchars(substr($service['service_description'], 0, 100)); ?>...
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-primary">GHS <?php echo number_format($service['base_price'], 2); ?></strong>
                                                <br><small class="text-muted"><?php echo str_replace('_', ' ', $service['pricing_unit']); ?></small>
                                            </div>
                                            <a href="single_service.php?id=<?php echo $service['service_id']; ?>" class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa fa-search fa-3x mb-3"></i>
                        <h4>No services found</h4>
                        <p class="text-muted">Try adjusting your filters or browse all services</p>
                        <?php if ($category_filter): ?>
                            <a href="all_services.php" class="btn btn-primary mt-3">View All Services</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/favorites.js"></script>
    <script src="../js/accessibility.js"></script>
</body>
</html>
