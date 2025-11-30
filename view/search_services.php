<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../controllers/service_category_controller.php';
require_once '../controllers/search_history_controller.php';

// Get search parameters
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : null;
$region_filter = isset($_GET['region']) ? trim($_GET['region']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';

// Save search to history for logged-in users
if (!empty($search_query) && isset($_SESSION['user_id'])) {
    $filters = [];
    if ($category_filter) $filters['category'] = $category_filter;
    if ($region_filter) $filters['region'] = $region_filter;
    if ($min_price !== null) $filters['min_price'] = $min_price;
    if ($max_price !== null) $filters['max_price'] = $max_price;

    save_search_ctr($_SESSION['user_id'], $search_query, !empty($filters) ? $filters : null);
}

// Get services
$services = [];
if ($search_query) {
    $services = search_services_ctr($search_query);
} else if ($category_filter) {
    $services = get_services_by_category_ctr($category_filter);
} else {
    $services = get_all_services_ctr();
}

// Apply filters
if ($services && is_array($services)) {
    // Category filter
    if ($category_filter) {
        $services = array_filter($services, function($service) use ($category_filter) {
            return $service['category_id'] == $category_filter;
        });
    }

    // Region filter
    if ($region_filter) {
        $services = array_filter($services, function($service) use ($region_filter) {
            // Check service location
            if (stripos($service['service_location'] ?? '', $region_filter) !== false) {
                return true;
            }
            // Check provider region
            if (stripos($service['provider_region'] ?? '', $region_filter) !== false) {
                return true;
            }
            // Check available regions (JSON array)
            if (!empty($service['available_regions'])) {
                $regions = json_decode($service['available_regions'], true);
                if (is_array($regions) && in_array($region_filter, $regions)) {
                    return true;
                }
            }
            return false;
        });
    }

    // Price range filter
    if ($min_price !== null) {
        $services = array_filter($services, function($service) use ($min_price) {
            return $service['base_price'] >= $min_price;
        });
    }

    if ($max_price !== null) {
        $services = array_filter($services, function($service) use ($max_price) {
            return $service['base_price'] <= $max_price;
        });
    }

    // Sorting
    switch ($sort_by) {
        case 'price_low':
            usort($services, function($a, $b) {
                return $a['base_price'] <=> $b['base_price'];
            });
            break;
        case 'price_high':
            usort($services, function($a, $b) {
                return $b['base_price'] <=> $a['base_price'];
            });
            break;
        case 'rating':
            usort($services, function($a, $b) {
                $rating_a = $a['provider_rating'] ?? 0;
                $rating_b = $b['provider_rating'] ?? 0;
                return $rating_b <=> $rating_a;
            });
            break;
        case 'newest':
            usort($services, function($a, $b) {
                return strtotime($b['date_created'] ?? '0') <=> strtotime($a['date_created'] ?? '0');
            });
            break;
        case 'relevance':
        default:
            // Keep original order (relevance)
            break;
    }
}

// Get all categories for filter
$all_categories = get_all_service_categories_ctr();

// Available regions
$regions = ['Greater Accra', 'Central', 'Ashanti', 'Northern', 'Western', 'Eastern', 'Volta', 'Upper East', 'Upper West', 'Bono'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/translator.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            padding-top: 70px;
        }

        .search-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        /* Search Header */
        .search-header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .search-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .search-header .result-count {
            color: #666;
            font-size: 0.95rem;
        }

        /* Search and Filter Bar */
        .filter-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .price-range {
            display: flex;
            gap: 10px;
        }

        .price-range input {
            width: 100px;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-apply {
            background: #2d6a4f;
            color: white;
        }

        .btn-apply:hover {
            background: #1b4332;
        }

        .btn-clear {
            background: #f0f0f0;
            color: #666;
        }

        .btn-clear:hover {
            background: #e0e0e0;
        }

        /* Sort Bar */
        .sort-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .sort-bar select {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }

        /* Service Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .service-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
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
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .service-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
        }

        .service-price {
            color: #2d6a4f !important;
            font-size: 1.3rem;
            font-weight: 800;
        }

        .btn-view {
            background: #2d6a4f;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-view:hover {
            background: #1b4332;
            color: white;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
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

        .empty-state .btn-primary {
            background: #2d6a4f;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
        }

        /* Rating Stars */
        .rating-stars {
            color: #ffd700;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .search-container {
                padding: 0 15px;
            }

            .filter-row {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .sort-bar {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <div class="search-container">
        <!-- Search Header -->
        <div class="search-header">
            <h1>
                <?php if ($search_query): ?>
                    Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                <?php else: ?>
                    Browse Services
                <?php endif; ?>
            </h1>
            <p class="result-count">
                <strong><?php echo $services ? count($services) : 0; ?></strong> service(s) found
            </p>
        </div>

        <!-- Advanced Filters -->
        <div class="filter-bar">
            <form method="GET" action="search_services.php" id="filterForm">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">

                <div class="filter-row">
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label><i class="fa fa-th-large"></i> Category</label>
                        <select name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php if ($all_categories): ?>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"
                                            <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Region Filter -->
                    <div class="filter-group">
                        <label><i class="fa fa-map-marker-alt"></i> Region</label>
                        <select name="region" onchange="this.form.submit()">
                            <option value="">All Regions</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?php echo $region; ?>"
                                        <?php echo $region_filter == $region ? 'selected' : ''; ?>>
                                    <?php echo $region; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <label><i class="fa fa-money-bill-wave"></i> Price Range (GHS)</label>
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="Min"
                                   value="<?php echo $min_price ?? ''; ?>" min="0" step="10">
                            <input type="number" name="max_price" placeholder="Max"
                                   value="<?php echo $max_price ?? ''; ?>" min="0" step="10">
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter btn-apply">
                            <i class="fa fa-filter"></i> Apply
                        </button>
                        <a href="search_services.php?q=<?php echo htmlspecialchars($search_query); ?>"
                           class="btn-filter btn-clear">
                            <i class="fa fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sort Bar -->
        <div class="sort-bar">
            <span><strong><?php echo $services ? count($services) : 0; ?></strong> results</span>
            <form method="GET" action="search_services.php" style="display: inline;">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                <input type="hidden" name="category" value="<?php echo $category_filter ?? ''; ?>">
                <input type="hidden" name="region" value="<?php echo $region_filter ?? ''; ?>">
                <input type="hidden" name="min_price" value="<?php echo $min_price ?? ''; ?>">
                <input type="hidden" name="max_price" value="<?php echo $max_price ?? ''; ?>">
                <label style="font-weight: 600; font-size: 0.9rem; margin-right: 10px;">Sort by:</label>
                <select name="sort" onchange="this.form.submit()" style="padding: 8px 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                    <option value="relevance" <?php echo $sort_by == 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                    <option value="price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="rating" <?php echo $sort_by == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                </select>
            </form>
        </div>

        <!-- Services Grid -->
        <?php if ($services && count($services) > 0): ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <?php
                        require_once '../classes/hosted_upload_class.php';
                        $images = json_decode($service['service_images'], true);
                        $first_image = is_array($images) && !empty($images) ? HostedUpload::getImageUrl($images[0], '../') : null;
                        ?>
                        <?php if ($first_image): ?>
                            <img src="<?php echo htmlspecialchars($first_image); ?>"
                                 alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                 class="service-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="service-image" style="background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); display: none; align-items: center; justify-content: center;">
                                <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                            </div>
                        <?php else: ?>
                            <div class="service-image" style="background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                            </div>
                        <?php endif; ?>

                        <div class="service-content">
                            <span class="badge"><?php echo htmlspecialchars($service['category_name']); ?></span>
                            <h5><?php echo htmlspecialchars($service['service_title']); ?></h5>

                            <p class="text-muted">
                                <i class="fa fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($service['service_location']); ?>
                            </p>

                            <?php if (isset($service['provider_rating']) && $service['provider_rating'] > 0): ?>
                                <div class="rating-stars">
                                    <?php
                                    $rating = round($service['provider_rating']);
                                    for ($i = 0; $i < 5; $i++) {
                                        echo $i < $rating ? '<i class="fa fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                    <span style="color: #666; font-size: 0.85rem;">
                                        (<?php echo number_format($service['provider_rating'], 1); ?>)
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="service-footer">
                                <div>
                                    <span class="service-price">GHS <?php echo number_format($service['base_price'], 2); ?></span>
                                    <br><small style="color: #999;"><?php echo ucfirst(str_replace('_', ' ', $service['pricing_unit'])); ?></small>
                                </div>
                                <a href="single_service.php?id=<?php echo $service['service_id']; ?>" class="btn-view">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-search fa-4x"></i>
                <h4>No services found</h4>
                <p style="color: #666;">Try adjusting your filters or search with different keywords</p>
                <a href="all_services.php" class="btn btn-primary">Browse All Services</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
