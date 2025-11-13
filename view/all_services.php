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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

    <!-- Page Header -->
    <div class="page-header">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
