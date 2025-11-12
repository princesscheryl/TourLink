<?php
require_once 'settings/core.php';
require_once 'controllers/service_controller.php';
require_once 'controllers/service_category_controller.php';

// Get featured services (premium or top-rated)
$featured_services = get_premium_services_ctr(6);
if (!$featured_services || count($featured_services) == 0) {
    $featured_services = get_all_services_ctr();
    if ($featured_services && count($featured_services) > 6) {
        $featured_services = array_slice($featured_services, 0, 6);
    }
}

$categories = get_all_service_categories_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TourLink | Discover Authentic Ghanaian Experiences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1a1a1a;
            background: #fafafa;
        }

        /* Top Info Bar */
        .top-bar {
            background: #2d6a4f;
            color: white;
            padding: 12px 0;
            font-size: 14px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
        }

        .top-bar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-left {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .top-bar-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }

        .top-bar a:hover {
            opacity: 0.8;
        }

        .social-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .social-links span {
            margin-right: 5px;
            font-weight: 500;
        }

        .social-links a {
            font-size: 16px;
        }

        /* Navigation */
        .main-nav {
            position: fixed;
            top: 48px;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #1a1a1a;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .nav-link {
            color: #666;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #1a1a1a;
        }

        .btn-become-provider {
            background: white;
            color: #1a1a1a;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid #1a1a1a;
            transition: all 0.3s;
        }

        .btn-become-provider:hover {
            background: #1a1a1a;
            color: white;
        }

        /* Hero Section */
        .hero {
            margin-top: 128px;
            height: 85vh;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)),
                        url('https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=1600&q=80') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .hero-content {
            text-align: center;
            color: white;
            max-width: 900px;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 64px;
            font-weight: 800;
            margin-bottom: 24px;
            line-height: 1.1;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            color: rgba(255,255,255,0.95);
            font-weight: 400;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 60px;
        }

        .btn-explore {
            background: #ff6b35;
            color: white;
            padding: 16px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            border: none;
        }

        .btn-explore:hover {
            background: #e85a2a;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
        }

        .btn-provider {
            background: white;
            color: #1a1a1a;
            padding: 16px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-provider:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }

        /* Search Bar */
        .search-container {
            position: absolute;
            bottom: -80px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1200px;
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .search-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 20px;
            align-items: end;
        }

        .search-field {
            display: flex;
            flex-direction: column;
        }

        .search-field label {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-field select,
        .search-field input {
            padding: 14px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
            background: #fafafa;
        }

        .search-field select:focus,
        .search-field input:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
        }

        .btn-search {
            background: #2d6a4f;
            color: white;
            padding: 14px 40px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-search:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        /* Featured Section */
        .featured-section {
            padding: 180px 40px 100px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .section-header p {
            font-size: 18px;
            color: #666;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 32px;
        }

        .service-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }

        .service-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .service-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .service-content {
            padding: 28px;
        }

        .service-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1a1a1a;
        }

        .service-provider {
            color: #666;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .service-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .service-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .service-rating .fa-star {
            color: #fbbf24;
        }

        .service-location {
            color: #666;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .service-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .service-price {
            font-size: 14px;
            color: #666;
        }

        .service-price strong {
            display: block;
            font-size: 24px;
            color: #1a1a1a;
            font-weight: 700;
        }

        .btn-view {
            background: #ff6b35;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-view:hover {
            background: #e85a2a;
            color: white;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .search-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .services-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                display: none;
            }

            .main-nav {
                top: 0;
            }

            .hero {
                margin-top: 80px;
            }

            .hero h1 {
                font-size: 40px;
            }

            .search-grid {
                grid-template-columns: 1fr;
            }

            .search-container {
                bottom: -200px;
            }

            .featured-section {
                padding-top: 280px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Top Info Bar -->
    <div class="top-bar">
        <div class="top-bar-container">
            <div class="top-bar-left">
                <a href="mailto:info@tourlink.com.gh">
                    <i class="fas fa-envelope"></i>
                    info@tourlink.com.gh
                </a>
                <a href="tel:+233501234567">
                    <i class="fas fa-phone"></i>
                    +233 50 123 4567
                </a>
            </div>
            <div class="top-bar-right">
                <div class="social-links">
                    <span>Follow Us:</span>
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="index_tourlink.php" class="logo">TourLink</a>
            <div class="nav-links">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="view/all_services.php" class="nav-link">Browse Services</a>
                    <a href="admin/provider_dashboard.php" class="nav-link">Dashboard</a>
                    <span class="nav-link">Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="login/logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="view/all_services.php" class="nav-link">Browse Services</a>
                    <a href="login/login.php" class="nav-link">Sign In</a>
                    <a href="admin/become_provider.php" class="btn-become-provider">Become a Provider</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Authentic<br>Ghanaian Experiences</h1>
            <p>Connect with local guides, drivers, and cultural experts for unforgettable journeys across Ghana</p>

            <div class="hero-buttons">
                <a href="view/all_services.php" class="btn-explore">
                    Explore Services
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="admin/become_provider.php" class="btn-provider">
                    Become a Provider
                </a>
            </div>
        </div>

        <!-- Advanced Search Bar -->
        <div class="search-container">
            <form action="view/search_services.php" method="GET">
                <div class="search-grid">
                    <div class="search-field">
                        <label><i class="fas fa-map-marker-alt"></i> Region</label>
                        <select name="region">
                            <option value="">All Regions</option>
                            <option value="Greater Accra">Greater Accra</option>
                            <option value="Central">Central</option>
                            <option value="Ashanti">Ashanti</option>
                            <option value="Northern">Northern</option>
                        </select>
                    </div>

                    <div class="search-field">
                        <label><i class="fas fa-th-large"></i> Service Type</label>
                        <select name="category">
                            <option value="">All Services</option>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="search-field">
                        <label><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="search-field">
                        <label><i class="fas fa-users"></i> People</label>
                        <input type="number" name="people" placeholder="Number of people" min="1" value="1">
                    </div>

                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Featured Experiences -->
    <section class="featured-section">
        <div class="section-header">
            <h2>Featured Experiences</h2>
            <p>Handpicked services from our most trusted local providers</p>
        </div>

        <?php if ($featured_services && count($featured_services) > 0): ?>
            <div class="services-grid">
                <?php foreach ($featured_services as $service): ?>
                    <?php
                    $images = json_decode($service['service_images'], true);
                    $first_image = is_array($images) && !empty($images) ? $images[0] : 'https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=800&q=80';
                    ?>
                    <div class="service-card">
                        <img src="<?php echo htmlspecialchars($first_image); ?>"
                             alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                             class="service-image">
                        <div class="service-badge"><?php echo htmlspecialchars($service['category_name']); ?></div>

                        <div class="service-content">
                            <h3 class="service-title"><?php echo htmlspecialchars($service['service_title']); ?></h3>
                            <div class="service-provider">
                                <?php echo htmlspecialchars($service['provider_name'] ?: ($service['provider_first_name'] . ' ' . $service['provider_last_name'])); ?>
                            </div>

                            <div class="service-meta">
                                <div class="service-rating">
                                    <i class="fas fa-star"></i>
                                    <?php echo number_format($service['provider_rating'] ?: 4.5, 1); ?>
                                    <span style="color: #999;">(<?php echo rand(20, 200); ?> reviews)</span>
                                </div>
                                <div class="service-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($service['provider_region']); ?>
                                </div>
                            </div>

                            <div class="service-footer">
                                <div class="service-price">
                                    From
                                    <strong>GH₵<?php echo number_format($service['base_price'], 0); ?></strong>
                                </div>
                                <a href="view/single_service.php?id=<?php echo $service['service_id']; ?>" class="btn-view">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 100px 20px;">
                <i class="fas fa-compass fa-4x" style="color: #ddd; margin-bottom: 20px;"></i>
                <h3>No services available yet</h3>
                <p style="color: #666;">Be the first to add a service and start connecting with tourists!</p>
                <a href="admin/become_provider.php" class="btn-explore" style="margin-top: 20px;">
                    Become a Provider
                </a>
            </div>
        <?php endif; ?>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
