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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Services - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        .service-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 24px;
        }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .service-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .service-content {
            padding: 20px;
        }
        .filter-sidebar {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .category-filter {
            display: block;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        .category-filter:hover {
            background: var(--primary);
            color: white;
        }
        .category-filter.active {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="all_services.php" class="nav-link active">Browse Services</a>
                <a href="cart.php" class="nav-link">
                    <i class="fa fa-shopping-cart"></i> Cart
                    <span class="cart-count">0</span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="nav-user">
                        <i class="fa fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="../login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
                <?php else: ?>
                    <a href="../login/login.php" class="nav-link">Sign in</a>
                    <a href="../login/register.php" class="btn-nav btn-nav-join">Join</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <h1 class="mb-4">Browse Tourism Services</h1>

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
                    <p class="text-muted mb-4"><?php echo count($services); ?> service(s) found</p>
                    <div class="row">
                        <?php foreach ($services as $service): ?>
                            <div class="col-md-4">
                                <div class="service-card">
                                    <?php
                                    $images = json_decode($service['service_images'], true);
                                    $first_image = is_array($images) && !empty($images) ? $images[0] : null;
                                    ?>
                                    <?php if ($first_image): ?>
                                        <img src="<?php echo htmlspecialchars($first_image); ?>"
                                             alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                             class="service-image">
                                    <?php else: ?>
                                        <div class="service-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
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
                    <div class="text-center py-5">
                        <i class="fa fa-search fa-3x text-muted mb-3"></i>
                        <h4>No services found</h4>
                        <p class="text-muted">Try adjusting your filters or browse all services</p>
                        <?php if ($category_filter): ?>
                            <a href="all_services.php" class="btn btn-primary">View All Services</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
