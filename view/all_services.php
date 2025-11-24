<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../controllers/service_category_controller.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : null;
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$min_rating = isset($_GET['rating']) && $_GET['rating'] !== '' ? (float)$_GET['rating'] : null;
$region_filter = isset($_GET['region']) ? trim($_GET['region']) : null;
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Get services
if ($category_filter) {
    $services = get_services_by_category_ctr($category_filter);
} else {
    $services = get_all_services_ctr();
}

// Apply additional filters
if ($services && is_array($services)) {
    // Price filter
    if ($min_price !== null) {
        $services = array_filter($services, function($s) use ($min_price) {
            return $s['base_price'] >= $min_price;
        });
    }
    if ($max_price !== null) {
        $services = array_filter($services, function($s) use ($max_price) {
            return $s['base_price'] <= $max_price;
        });
    }

    // Rating filter
    if ($min_rating !== null) {
        $services = array_filter($services, function($s) use ($min_rating) {
            return ($s['provider_rating'] ?? 0) >= $min_rating;
        });
    }

    // Region filter
    if ($region_filter) {
        $services = array_filter($services, function($s) use ($region_filter) {
            $regions = json_decode($s['available_regions'] ?? '[]', true);
            return in_array($region_filter, $regions ?: []) ||
                   stripos($s['service_location'] ?? '', $region_filter) !== false ||
                   stripos($s['provider_region'] ?? '', $region_filter) !== false;
        });
    }

    // Sort
    $services = array_values($services); // Re-index array
    switch ($sort_by) {
        case 'price_low':
            usort($services, fn($a, $b) => $a['base_price'] <=> $b['base_price']);
            break;
        case 'price_high':
            usort($services, fn($a, $b) => $b['base_price'] <=> $a['base_price']);
            break;
        case 'rating':
            usort($services, fn($a, $b) => ($b['provider_rating'] ?? 0) <=> ($a['provider_rating'] ?? 0));
            break;
        case 'popular':
            usort($services, fn($a, $b) => ($b['views_count'] ?? 0) <=> ($a['views_count'] ?? 0));
            break;
        case 'newest':
        default:
            // Already sorted by date
            break;
    }
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

        /* Filter Sidebar */
        .filter-sidebar {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            position: sticky;
            top: 90px;
        }

        .filter-section {
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .filter-section:last-of-type {
            border-bottom: none;
            margin-bottom: 16px;
        }

        .filter-sidebar h5 {
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 14px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-sidebar h5 i {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .filter-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #333;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .filter-select:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .category-filter {
            display: block;
            padding: 10px 14px;
            margin-bottom: 6px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
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

        .price-inputs {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .price-input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.9rem;
            width: 100%;
        }

        .price-input:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .price-separator {
            color: #999;
            font-weight: 500;
        }

        .rating-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .rating-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border: 1px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .rating-option:hover {
            border-color: #2d6a4f;
            background: #f8f9fa;
        }

        .rating-option input[type="radio"] {
            accent-color: #2d6a4f;
        }

        .rating-option input[type="radio"]:checked + span {
            color: #2d6a4f;
            font-weight: 600;
        }

        .btn-apply-filters {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-apply-filters:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
        }

        .btn-clear-filters {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            margin-top: 10px;
            color: #666;
            text-decoration: none;
            font-size: 0.85rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-clear-filters:hover {
            background: #f8f9fa;
            color: #333;
            border-color: #ccc;
        }

        .text-warning {
            color: #f59e0b !important;
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

        /* Image Carousel */
        .image-carousel {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .carousel-slides {
            display: flex;
            height: 100%;
            transition: transform 0.5s ease;
        }

        .carousel-slide {
            min-width: 100%;
            height: 100%;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-dots {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 5;
        }

        .carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            padding: 0;
        }

        .carousel-dot.active {
            background: white;
            transform: scale(1.2);
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.9);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 5;
        }

        .image-carousel:hover .carousel-nav {
            opacity: 1;
        }

        .carousel-nav.prev { left: 10px; }
        .carousel-nav.next { right: 10px; }

        .carousel-nav:hover {
            background: white;
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

        [data-theme="dark"] .filter-sidebar {
            background: #2d2d2d !important;
        }

        [data-theme="dark"] .filter-sidebar h5 {
            color: #52b788 !important;
        }

        [data-theme="dark"] .filter-select,
        [data-theme="dark"] .price-input {
            background: #3d3d3d !important;
            border-color: #505050 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .category-filter {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .category-filter:hover,
        [data-theme="dark"] .category-filter.active {
            background: #52b788 !important;
            color: #1a1a1a !important;
        }

        [data-theme="dark"] .rating-option {
            border-color: #505050 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .filter-section {
            border-color: #404040 !important;
        }

        [data-theme="dark"] .btn-clear-filters {
            border-color: #505050 !important;
            color: #b0b0b0 !important;
        }

        /* Active Filters Pills */
        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #e8f5e9;
            color: #2d6a4f;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .filter-pill i {
            font-size: 0.7rem;
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
    <?php include '../includes/navigation.php'; ?>

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
                <form id="filterForm" method="GET" action="all_services.php">
                    <div class="filter-sidebar">
                        <!-- Sort By -->
                        <div class="filter-section">
                            <h5><i class="fas fa-sort-amount-down"></i> Sort By</h5>
                            <select name="sort" class="filter-select" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>

                        <!-- Categories -->
                        <div class="filter-section">
                            <h5><i class="fas fa-th-large"></i> Categories</h5>
                            <a href="all_services.php<?php echo $sort_by !== 'newest' ? '?sort='.$sort_by : ''; ?>" class="category-filter <?php echo !$category_filter ? 'active' : ''; ?>">
                                <i class="fa fa-list"></i> All Services
                            </a>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $category): ?>
                                    <a href="all_services.php?category=<?php echo $category['category_id']; ?><?php echo $sort_by !== 'newest' ? '&sort='.$sort_by : ''; ?>"
                                       class="category-filter <?php echo $category_filter == $category['category_id'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($category_filter): ?>
                                <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                            <?php endif; ?>
                        </div>

                        <!-- Price Range -->
                        <div class="filter-section">
                            <h5><i class="fas fa-tag"></i> Price Range (GHS)</h5>
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Min" class="price-input"
                                       value="<?php echo $min_price !== null ? $min_price : ''; ?>">
                                <span class="price-separator">-</span>
                                <input type="number" name="max_price" placeholder="Max" class="price-input"
                                       value="<?php echo $max_price !== null ? $max_price : ''; ?>">
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="filter-section">
                            <h5><i class="fas fa-star"></i> Minimum Rating</h5>
                            <div class="rating-options">
                                <label class="rating-option">
                                    <input type="radio" name="rating" value="" <?php echo $min_rating === null ? 'checked' : ''; ?>>
                                    <span>All Ratings</span>
                                </label>
                                <label class="rating-option">
                                    <input type="radio" name="rating" value="4" <?php echo $min_rating == 4 ? 'checked' : ''; ?>>
                                    <span><i class="fas fa-star text-warning"></i> 4+ Stars</span>
                                </label>
                                <label class="rating-option">
                                    <input type="radio" name="rating" value="3" <?php echo $min_rating == 3 ? 'checked' : ''; ?>>
                                    <span><i class="fas fa-star text-warning"></i> 3+ Stars</span>
                                </label>
                            </div>
                        </div>

                        <!-- Region Filter -->
                        <div class="filter-section">
                            <h5><i class="fas fa-map-marker-alt"></i> Region</h5>
                            <select name="region" class="filter-select">
                                <option value="">All Regions</option>
                                <?php
                                $regions = ['Greater Accra', 'Ashanti', 'Central', 'Northern'];
                                foreach ($regions as $region): ?>
                                    <option value="<?php echo $region; ?>" <?php echo $region_filter === $region ? 'selected' : ''; ?>><?php echo $region; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Apply Filters Button -->
                        <button type="submit" class="btn-apply-filters">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>

                        <?php if ($min_price !== null || $max_price !== null || $min_rating !== null || $region_filter): ?>
                            <a href="all_services.php<?php echo $category_filter ? '?category='.$category_filter : ''; ?>" class="btn-clear-filters">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Services Grid -->
            <div class="col-md-9">
                <?php if ($services && count($services) > 0): ?>
                    <p class="services-count"><strong><?php echo count($services); ?></strong> service(s) found</p>

                    <?php if ($min_price !== null || $max_price !== null || $min_rating !== null || $region_filter || $category_filter): ?>
                    <div class="active-filters">
                        <?php if ($category_filter): ?>
                            <?php
                            $cat_name = 'Category';
                            foreach ($categories as $cat) {
                                if ($cat['category_id'] == $category_filter) {
                                    $cat_name = $cat['category_name'];
                                    break;
                                }
                            }
                            ?>
                            <span class="filter-pill"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($cat_name); ?></span>
                        <?php endif; ?>
                        <?php if ($min_price !== null || $max_price !== null): ?>
                            <span class="filter-pill">
                                <i class="fas fa-tag"></i>
                                <?php if ($min_price !== null && $max_price !== null): ?>
                                    GHS <?php echo number_format($min_price); ?> - <?php echo number_format($max_price); ?>
                                <?php elseif ($min_price !== null): ?>
                                    Min GHS <?php echo number_format($min_price); ?>
                                <?php else: ?>
                                    Max GHS <?php echo number_format($max_price); ?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($min_rating !== null): ?>
                            <span class="filter-pill"><i class="fas fa-star"></i> <?php echo $min_rating; ?>+ Stars</span>
                        <?php endif; ?>
                        <?php if ($region_filter): ?>
                            <span class="filter-pill"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($region_filter); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
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
                                    $valid_images = [];
                                    if (is_array($images) && !empty($images)) {
                                        foreach ($images as $img) {
                                            $img_path = '../' . $img;
                                            if (file_exists($img_path)) {
                                                $valid_images[] = $img_path;
                                            }
                                        }
                                    }
                                    $carousel_id = 'carousel-' . $service['service_id'];
                                    ?>
                                    <?php if (!empty($valid_images)): ?>
                                        <div class="image-carousel" data-carousel>
                                            <div class="carousel-slides" data-slides>
                                                <?php foreach ($valid_images as $img): ?>
                                                    <div class="carousel-slide">
                                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($service['service_title']); ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (count($valid_images) > 1): ?>
                                                <button class="carousel-nav prev" data-prev><i class="fas fa-chevron-left"></i></button>
                                                <button class="carousel-nav next" data-next><i class="fas fa-chevron-right"></i></button>
                                                <div class="carousel-dots" data-dots>
                                                    <?php for ($i = 0; $i < count($valid_images); $i++): ?>
                                                        <button class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></button>
                                                    <?php endfor; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
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

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/favorites.js"></script>
    <script src="../js/accessibility.js"></script>

    <script>
    // Image Carousel Functionality
    document.querySelectorAll('[data-carousel]').forEach(carousel => {
        const slides = carousel.querySelector('[data-slides]');
        const dots = carousel.querySelectorAll('.carousel-dot');
        const prevBtn = carousel.querySelector('[data-prev]');
        const nextBtn = carousel.querySelector('[data-next]');
        const slideCount = carousel.querySelectorAll('.carousel-slide').length;

        if (slideCount <= 1) return;

        let currentIndex = 0;
        let autoPlayInterval;

        function goToSlide(index) {
            if (index < 0) index = slideCount - 1;
            if (index >= slideCount) index = 0;
            currentIndex = index;
            slides.style.transform = `translateX(-${currentIndex * 100}%)`;
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }

        function nextSlide() {
            goToSlide(currentIndex + 1);
        }

        function prevSlide() {
            goToSlide(currentIndex - 1);
        }

        // Auto-play every 4 seconds
        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 4000);
        }

        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }

        // Start auto-play
        startAutoPlay();

        // Pause on hover
        carousel.addEventListener('mouseenter', stopAutoPlay);
        carousel.addEventListener('mouseleave', startAutoPlay);

        // Navigation buttons
        if (prevBtn) prevBtn.addEventListener('click', (e) => { e.preventDefault(); prevSlide(); });
        if (nextBtn) nextBtn.addEventListener('click', (e) => { e.preventDefault(); nextSlide(); });

        // Dot navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });
    });
    </script>
</body>
</html>
