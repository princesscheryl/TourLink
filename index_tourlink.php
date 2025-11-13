<?php
require_once 'settings/core.php';
require_once 'controllers/service_controller.php';
require_once 'controllers/service_category_controller.php';

// Get featured services
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
    <title>TourLink | Discover Your Next Adventure in Ghana</title>
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
            color: #1a1a1a;
            background: #fff;
            overflow-x: hidden;
        }

        /* Top Bar */
        .top-bar {
            background: #2d6a4f;
            color: white;
            padding: 10px 0;
            font-size: 14px;
        }

        .top-bar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            margin-right: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            margin-right: 10px;
        }

        /* Main Navigation */
        .main-nav {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
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
            font-size: 32px;
            font-weight: 800;
            color: #2d6a4f;
            text-decoration: none;
        }

        .logo-dot {
            color: #ffd700;
        }

        .nav-menu {
            display: flex;
            gap: 35px;
            align-items: center;
            list-style: none;
        }

        .nav-menu a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s;
        }

        .nav-menu a:hover {
            color: #2d6a4f;
        }

        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-signin {
            background: transparent;
            color: #2d6a4f;
            padding: 10px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid #2d6a4f;
            transition: all 0.3s;
        }

        .btn-signin:hover {
            background: #2d6a4f;
            color: white;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 10px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #2d6a4f;
            transition: all 0.3s;
            border-radius: 2px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)),
                        url('https://images.unsplash.com/photo-1489392191049-fc10c97e64b6?w=1600') center/cover;
            min-height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 100px 20px;
        }

        .hero-content {
            max-width: 1400px;
            width: 100%;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 60px;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 72px;
            font-weight: 800;
            color: white;
            line-height: 1.1;
            margin-bottom: 24px;
        }

        .hero-text p {
            font-size: 18px;
            color: rgba(255,255,255,0.95);
            margin-bottom: 40px;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
        }

        .btn-choose {
            background: #ffd700;
            color: #1a1a1a;
            padding: 16px 36px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
        }

        .btn-choose:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
        }

        .btn-become {
            background: white;
            color: #1a1a1a;
            padding: 16px 36px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-become:hover {
            transform: translateY(-2px);
        }

        .hero-image {
            position: relative;
        }

        .hero-image img {
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            transform: rotate(-8deg);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        /* Search Bar */
        .search-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin: -80px auto 0;
            max-width: 1300px;
            position: relative;
            z-index: 100;
        }

        .search-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr) auto;
            gap: 16px;
        }

        .search-field {
            position: relative;
        }

        .search-field label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .search-field select,
        .search-field input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .search-field select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }

        .search-field select:focus,
        .search-field input:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .btn-search {
            background: #2d6a4f;
            color: white;
            padding: 14px 40px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            align-self: end;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        /* Trust Indicators */
        .trust-section {
            max-width: 1300px;
            margin: 40px auto;
            display: flex;
            justify-content: space-around;
            padding: 0 20px;
        }

        .trust-item {
            text-align: center;
        }

        .trust-item i {
            color: #ffd700;
            font-size: 20px;
            margin-right: 8px;
        }

        .trust-item strong {
            font-size: 18px;
            color: #1a1a1a;
        }

        /* Popular Destinations */
        .destinations-section {
            padding: 100px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .section-header p {
            font-size: 18px;
            color: #666;
        }

        .destinations-tabs {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 50px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 16px;
            color: #666;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn.active,
        .tab-btn:hover {
            color: #2d6a4f;
            border-bottom-color: #2d6a4f;
        }

        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 32px;
        }

        .destination-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.4s;
            position: relative;
        }

        .destination-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .destination-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .price-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #2d6a4f;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
        }

        .destination-content {
            padding: 28px;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .rating i {
            color: #ffd700;
            font-size: 14px;
        }

        .destination-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .destination-features {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn-view-packages {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 600;
            transition: gap 0.3s;
        }

        .btn-view-packages:hover {
            gap: 12px;
        }

        /* Why Choose Section */
        .why-choose {
            background: #f8f9fa;
            padding: 100px 40px;
        }

        .why-choose-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .why-choose h2 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 24px;
        }

        .why-choose p {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
        }

        .features-list {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .feature-item {
            display: flex;
            gap: 20px;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: #2d6a4f;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon i {
            color: white;
            font-size: 24px;
        }

        .feature-text h4 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .feature-text p {
            font-size: 15px;
            color: #666;
            margin: 0;
        }

        /* Testimonials */
        .testimonials {
            padding: 100px 40px;
            background: white;
        }

        .testimonials-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 32px;
            margin-top: 60px;
        }

        .testimonial-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .testimonial-text {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 24px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            background: #2d6a4f;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 700;
        }

        .author-info h5 {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .author-info p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        /* Newsletter */
        .newsletter {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            padding: 80px 40px;
            color: white;
        }

        .newsletter-container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .newsletter h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .newsletter p {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .newsletter-form {
            display: flex;
            gap: 12px;
            max-width: 600px;
            margin: 0 auto;
        }

        .newsletter-form input {
            flex: 1;
            padding: 16px 24px;
            border-radius: 10px;
            border: none;
            font-size: 15px;
        }

        .newsletter-form button {
            background: #ffd700;
            color: #1a1a1a;
            padding: 16px 36px;
            border-radius: 10px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .newsletter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 60px 40px 30px;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 40px;
        }

        .footer h4 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .footer p {
            color: rgba(255,255,255,0.7);
            line-height: 1.8;
        }

        .footer ul {
            list-style: none;
        }

        .footer ul li {
            margin-bottom: 12px;
        }

        .footer ul a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer ul a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 30px;
            text-align: center;
            color: rgba(255,255,255,0.7);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
            }
            .hero-image {
                display: none;
            }
            .search-grid {
                grid-template-columns: 1fr 1fr;
            }
            .why-choose-container {
                grid-template-columns: 1fr;
            }
            .footer-container {
                grid-template-columns: 1fr 1fr;
            }

            .hamburger {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                top: 68px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 68px);
                background: white;
                flex-direction: column;
                padding: 40px;
                gap: 20px;
                transition: left 0.3s;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }

            .nav-menu.active {
                left: 0;
            }

            .top-bar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 42px;
            }
            .search-grid {
                grid-template-columns: 1fr;
            }
            .destinations-grid {
                grid-template-columns: 1fr;
            }
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            .footer-container {
                grid-template-columns: 1fr;
            }
            .destinations-tabs {
                flex-wrap: wrap;
                gap: 15px;
            }
            .hero-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-container">
            <div>
                <a href="mailto:info@tourlink.com.gh">
                    <i class="fas fa-envelope"></i>
                    info@tourlink.com.gh
                </a>
                <a href="tel:+233501234567">
                    <i class="fas fa-phone"></i>
                    +233 50 123 4567
                </a>
            </div>
            <div class="social-links">
                <span>Follow Us:</span>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index_tourlink.php">Home</a></li>
                <li><a href="view/all_services.php">Destinations</a></li>
                <li><a href="view/all_services.php">Tour Listing</a></li>
                <li><a href="view/about.php">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <div class="nav-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['user_type'] === 'provider'): ?>
                        <a href="admin/provider_dashboard.php" class="btn-signin">Dashboard</a>
                    <?php endif; ?>
                    <a href="login/logout.php" class="btn-signin" style="background: #dc3545; color: white; border-color: #dc3545;">Logout</a>
                <?php else: ?>
                    <a href="login/login.php" class="btn-signin">Sign In</a>
                <?php endif; ?>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Discover Your Next Adventure with TourLink!</h1>
                <p>Discover tours tailored to your dream destinations — from cultural escapes to adventure getaways. Book in minutes, travel without limits.</p>
                <div class="hero-buttons">
                    <a href="view/all_services.php" class="btn-choose">Choose a Destinations</a>
                    <a href="login/register_provider.php" class="btn-become">Become a Provider</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=500" alt="Ghana Tourism">
            </div>
        </div>
    </section>

    <!-- Search Bar -->
    <section class="search-section">
        <form action="view/search_services.php" method="GET">
            <div class="search-grid">
                <div class="search-field">
                    <label><i class="fas fa-map-marker-alt"></i> Destination</label>
                    <select name="region">
                        <option value="">All Regions</option>
                        <option value="Greater Accra">Greater Accra</option>
                        <option value="Central">Central Region</option>
                        <option value="Ashanti">Ashanti Region</option>
                        <option value="Northern">Northern Region</option>
                    </select>
                </div>
                <div class="search-field">
                    <label><i class="fas fa-th-large"></i> Tour Type</label>
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
                    <label><i class="fas fa-users"></i> Number of Travelers</label>
                    <input type="number" name="people" placeholder="1" min="1" value="1">
                </div>
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </div>
        </form>
    </section>

    <!-- Trust Indicators -->
    <div class="trust-section">
        <div class="trust-item">
            <i class="fas fa-star"></i>
            <strong>4.8/5 (10K+ Reviews)</strong>
        </div>
        <div class="trust-item">
            <i class="fas fa-users"></i>
            <strong>Trusted by 500K+ Travelers</strong>
        </div>
        <div class="trust-item">
            <i class="fas fa-lock"></i>
            <strong>Secure Booking</strong>
        </div>
    </div>

    <!-- Popular Destinations -->
    <section class="destinations-section">
        <div class="section-header">
            <h2>Popular Destinations</h2>
            <p>Explore our most sought-after travel locations around Ghana</p>
        </div>

        <div class="destinations-tabs">
            <button class="tab-btn active">Beach</button>
            <button class="tab-btn">Mountain</button>
            <button class="tab-btn">Cultural</button>
            <button class="tab-btn">Adventure</button>
            <button class="tab-btn">Historical</button>
        </div>

        <div class="destinations-grid">
            <?php
            $destinations = [
                [
                    'title' => 'Cape Coast Castle',
                    'location' => 'Central Region',
                    'price' => 150,
                    'rating' => 4.9,
                    'reviews' => 234,
                    'image' => 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=600',
                    'features' => 'UNESCO Heritage Site, Museum, Ocean Views'
                ],
                [
                    'title' => 'Kakum National Park',
                    'location' => 'Central Region',
                    'price' => 200,
                    'rating' => 4.8,
                    'reviews' => 189,
                    'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=600',
                    'features' => 'Canopy Walk, Rainforest, Wildlife'
                ],
                [
                    'title' => 'Mole National Park',
                    'location' => 'Northern Region',
                    'price' => 350,
                    'rating' => 4.7,
                    'reviews' => 156,
                    'image' => 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=600',
                    'features' => 'Safari, Elephants, Antelopes'
                ],
                [
                    'title' => 'Lake Volta Cruise',
                    'location' => 'Volta Region',
                    'price' => 180,
                    'rating' => 4.6,
                    'reviews' => 198,
                    'image' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600',
                    'features' => 'Boat Cruise, Sunset Views, Island Hopping'
                ],
                [
                    'title' => 'Wli Waterfalls',
                    'location' => 'Volta Region',
                    'price' => 120,
                    'rating' => 4.8,
                    'reviews' => 267,
                    'image' => 'https://images.unsplash.com/photo-1432405972618-c60b0225b8f9?w=600',
                    'features' => 'Hiking, Swimming, Nature Trails'
                ],
                [
                    'title' => 'Elmina Castle',
                    'location' => 'Central Region',
                    'price' => 140,
                    'rating' => 4.9,
                    'reviews' => 312,
                    'image' => 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?w=600',
                    'features' => 'Historical Tour, Guided Tours, Museum'
                ]
            ];

            foreach ($destinations as $dest):
            ?>
            <div class="destination-card">
                <img src="<?php echo $dest['image']; ?>" alt="<?php echo $dest['title']; ?>" class="destination-image">
                <div class="price-badge">From: GH₵<?php echo $dest['price']; ?> / person</div>
                <div class="destination-content">
                    <div class="rating">
                        <?php for($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <span><?php echo $dest['rating']; ?> (<?php echo $dest['reviews']; ?> reviews)</span>
                    </div>
                    <h3 class="destination-title"><?php echo $dest['title']; ?></h3>
                    <div class="destination-features">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo $dest['features']; ?>
                    </div>
                    <a href="view/all_services.php" class="btn-view-packages">
                        View Packages <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Why Choose TourLink -->
    <section class="why-choose">
        <div class="why-choose-container">
            <div>
                <h2>Why Choose TourLink?</h2>
                <p>We don't just book trips — we create unforgettable experiences. From personalized service to handpicked travel partners, we ensure every part of your journey is smooth, safe, and inspiring.</p>
            </div>
            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Best Price Guarantee</h4>
                        <p>Found a better price elsewhere? We'll match it and give you an additional 10% off your booking.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-text">
                        <h4>24/7 Customer Support</h4>
                        <p>Our travel experts are available round-the-clock to assist you, no matter where you are in the world.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Flexible Booking Options</h4>
                        <p>Flexible cancellation policies on most bookings with full refunds up to 48 hours before travel.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Verified Travel Partners</h4>
                        <p>Each hotel, guide, and experience partner is carefully vetted to meet our quality and safety standards.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="testimonials-container">
            <div class="section-header">
                <h2>What Our Travelers Say</h2>
                <p>Don't just take our word for it - hear from our satisfied customers</p>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Our trip to Cape Coast Castle was absolutely perfect thanks to TourLink. The guide was knowledgeable, and our tour guide was outstanding. Everything was perfectly organized. The kids had a blast and we have wonderful memories!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">KM</div>
                        <div class="author-info">
                            <h5>Kwame Mensah</h5>
                            <p>Family Vacation, Cape Coast</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"The Kakum Canopy Walk was incredible! TourLink handled all the details perfectly - the early morning pickup, the guide was amazing, and the Canopy Walk view was breathtaking. Customer service was available 24/7. Highly recommend!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AA</div>
                        <div class="author-info">
                            <h5>Ama Asante</h5>
                            <p>Adventure Tour, Kakum</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Best tour company! The Mole Safari was worth every penny! The website, the private tours were exceptional, the food experiences were authentic. TourLink's local connections made all the difference. They had a representative to ensure smooth transfers. That's service!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">KO</div>
                        <div class="author-info">
                            <h5>Kofi Owusu</h5>
                            <p>Safari Tour, Mole</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="newsletter-container">
            <h2>Stay Updated with Exclusive Travel Deals!</h2>
            <p>Subscribe to our newsletter and be the first to receive special offers, insider tips, and curated travel inspiration straight to your inbox.</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit">Subscribe <i class="fas fa-paper-plane"></i></button>
            </form>
            <p style="font-size: 14px; margin-top: 20px;">We respect your privacy. Unsubscribe at any time.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="footer-container">
            <div>
                <h4>TourLink</h4>
                <p>Your trusted partner for unforgettable travel experiences. We specialize in curated tours and personalized travel solutions around Ghana.</p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#faq">FAQs</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#terms">Terms & Conditions</a></li>
                </ul>
            </div>
            <div>
                <h4>Social Media</h4>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Instagram</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">LinkedIn</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact Info</h4>
                <p>Ashesi University, Berekuso<br>Ghana</p>
                <p>info@tourlink.com.gh</p>
                <p>+233 50 123 4567</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 TourLink. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile Menu Toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close menu when clicking on a link
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });

        // Category Tabs
        const tabButtons = document.querySelectorAll('.tab-btn');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // In a real application, this would filter the destinations
                // For now, it just changes the active state
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
