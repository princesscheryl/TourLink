<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../controllers/service_category_controller.php';
require_once '../classes/festival_class.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : null;
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$min_rating = isset($_GET['rating']) && $_GET['rating'] !== '' ? (float)$_GET['rating'] : null;
$region_filter = isset($_GET['region']) ? trim($_GET['region']) : null;
$festival_filter = isset($_GET['festival']) ? (int)$_GET['festival'] : null;
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Get festival info if filtering by festival
$festival_info = null;
if ($festival_filter) {
    $festival_class = new Festival();
    $festival_info = $festival_class->get_festival_by_id($festival_filter);
}

// Get services
if ($festival_filter) {
    $services = get_services_by_festival_ctr($festival_filter);
} elseif ($category_filter) {
    $services = get_services_by_category_ctr($category_filter);
} else {
    $services = get_all_services_ctr();
}

// Check for upcoming festivals in the current month
$festival_class = new Festival();
$upcoming_festivals = $festival_class->get_upcoming_festivals(5);

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
            // Use service's average_rating if available, otherwise fall back to provider_rating
            $service_rating = $s['average_rating'] ?? $s['provider_rating'] ?? 0;
            return (float)$service_rating >= (float)$min_rating;
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

    <?php if ($festival_info): ?>
    <!-- Festival Banner -->
    <div style="background: linear-gradient(135deg, #d4a017 0%, #f4c430 100%); padding: 24px 0; margin-bottom: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div class="main-container">
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div style="font-size: 48px;">🎉</div>
                <div style="flex: 1; min-width: 250px;">
                    <h2 style="color: #1b4332; font-weight: 800; margin: 0 0 8px 0; font-size: 1.8rem;">
                        <?php echo htmlspecialchars($festival_info['festival_name']); ?> Festival
                    </h2>
                    <p style="color: #2d6a4f; margin: 0; font-size: 1.1rem; font-weight: 500;">
                        <?php echo date('F j', strtotime($festival_info['start_date'])); ?>
                        <?php if ($festival_info['end_date'] && $festival_info['end_date'] != $festival_info['start_date']): ?>
                            - <?php echo date('F j, Y', strtotime($festival_info['end_date'])); ?>
                        <?php else: ?>
                            , <?php echo date('Y', strtotime($festival_info['start_date'])); ?>
                        <?php endif; ?>
                        • <?php echo htmlspecialchars($festival_info['region']); ?> Region
                    </p>
                    <?php if (!empty($festival_info['description'])): ?>
                        <p style="color: #1b4332; margin: 12px 0 0 0; font-size: 0.95rem; line-height: 1.5;">
                            <?php echo htmlspecialchars(substr($festival_info['description'], 0, 180)); ?>...
                        </p>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="all_services.php" style="background: #1b4332; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.3s;">
                        View All Services
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php elseif (!empty($upcoming_festivals)): ?>
    <!-- Upcoming Festivals Quick Links -->
    <div style="background: #f8f9fa; padding: 20px 0; margin-bottom: 24px; border-top: 3px solid #d4a017;">
        <div class="main-container">
            <div style="display: flex; align-items: center; gap: 16px; overflow-x: auto; padding-bottom: 8px;">
                <span style="color: #1b4332; font-weight: 600; white-space: nowrap;">
                    <i class="fas fa-calendar-alt" style="color: #d4a017;"></i> Upcoming Festivals:
                </span>
                <?php foreach (array_slice($upcoming_festivals, 0, 4) as $fest): ?>
                    <a href="all_services.php?festival=<?php echo $fest['festival_id']; ?>"
                       style="background: white; padding: 8px 16px; border-radius: 20px; text-decoration: none; color: #2d6a4f; font-weight: 500; white-space: nowrap; border: 2px solid #e5e7eb; transition: all 0.3s; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($fest['festival_name']); ?>
                        <span style="color: #999; font-size: 0.85rem;">
                            (<?php echo date('M j', strtotime($fest['start_date'])); ?>)
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
    <script src="../js/all_services.js"></script>
</body>
</html>
