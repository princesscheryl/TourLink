<?php
// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once 'settings/core.php';
require_once 'controllers/service_controller.php';
require_once 'controllers/service_category_controller.php';
require_once 'classes/festival_class.php';

// Get premium services for Featured Experiences section
$db_temp = new db_connection();
$db_temp->db_connect();

// Check which premium column exists
$check_is_premium = $db_temp->db->query("SHOW COLUMNS FROM tl_services LIKE 'is_premium'");
$has_is_premium = $check_is_premium && $check_is_premium->num_rows > 0;

$check_is_premium_listing = $db_temp->db->query("SHOW COLUMNS FROM tl_services LIKE 'is_premium_listing'");
$has_is_premium_listing = $check_is_premium_listing && $check_is_premium_listing->num_rows > 0;

// Build WHERE clause based on available columns
$premium_condition = "";
if ($has_is_premium) {
    $premium_condition = "s.is_premium = 1";
} elseif ($has_is_premium_listing) {
    $premium_condition = "s.is_premium_listing = 1";
} else {
    // No premium column exists - check via premium_listings table
    $premium_condition = "EXISTS (
        SELECT 1 FROM tl_premium_listings pl
        WHERE pl.provider_id = s.provider_id
        AND pl.status = 'active'
        AND pl.end_date >= CURDATE()
    )";
}

// Query premium services
$premium_query = $db_temp->db->query("
    SELECT s.*, sp.business_name, sp.verification_status, sc.category_name,
           AVG(r.rating) as average_rating,
           COUNT(DISTINCT b.booking_id) as total_bookings
    FROM tl_services s
    JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
    JOIN tl_service_categories sc ON s.category_id = sc.category_id
    JOIN tl_users u ON sp.user_id = u.user_id
    LEFT JOIN tl_reviews r ON s.service_id = r.service_id
    LEFT JOIN tl_bookings b ON s.service_id = b.service_id
    WHERE $premium_condition
    AND s.service_status = 'active'
    AND sp.verification_status = 'verified'
    AND u.account_status = 'active'
    GROUP BY s.service_id
    ORDER BY s.date_created DESC
    LIMIT 8
");
$premium_services = $premium_query ? $premium_query->fetch_all(MYSQLI_ASSOC) : [];

// Debug: Log premium services count for troubleshooting
error_log("Featured Experiences: Found " . count($premium_services) . " premium services");

// Get featured services (fallback to all if no premium)
$featured_services = get_premium_services_ctr(6);

// Get upcoming festivals
$festival_class = new Festival();
$upcoming_festivals = $festival_class->get_upcoming_festivals(4);
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
    <link href="css/footer.css" rel="stylesheet">
    <link href="css/dark-mode.css" rel="stylesheet">
    <link href="css/accessibility.css" rel="stylesheet">
    <script src="js/dark-mode.js"></script>
    <script src="js/translator.js"></script>
    <script src="js/accessibility.js"></script>
    <link href="css/index_tourlink.css" rel="stylesheet">
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

        /* Language Selector */
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

        .language-selector:focus {
            outline: none;
            border-color: #2d6a4f;
            box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
        }

        /* Public Theme Toggle Button */
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
            background: #f8f9fa;
        }

        .theme-toggle-btn i {
            font-size: 1.1rem;
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 25px;
            transition: all 0.3s;
            background: white;
            border: 2px solid #2d6a4f;
        }

        .profile-trigger:hover {
            background: #f8f9fa;
        }

        .profile-pic {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2d6a4f;
        }

        .profile-pic-placeholder {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #2d6a4f;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .profile-name {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9rem;
        }

        .dropdown-icon {
            color: #2d6a4f;
            font-size: 0.8rem;
            transition: transform 0.3s;
        }

        .profile-dropdown.active .dropdown-icon {
            transform: rotate(180deg);
        }

        .dropdown-menu-custom {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            min-width: 220px;
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
        }

        .profile-dropdown.active .dropdown-menu-custom {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu-custom a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #1a1a1a;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .dropdown-menu-custom a:first-child {
            border-radius: 12px 12px 0 0;
        }

        .dropdown-menu-custom a:last-child {
            border-radius: 0 0 12px 12px;
            color: #dc3545;
        }

        .dropdown-menu-custom a:hover {
            background: #f8f9fa;
            padding-left: 24px;
        }

        .dropdown-menu-custom a i {
            width: 20px;
            color: #2d6a4f;
        }

        .dropdown-menu-custom a:last-child i {
            color: #dc3545;
        }

        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 5px 0;
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
            min-height: 1.1em; /* Prevent layout shift during rotation */
        }

        /* Rotating text styles */
        .rotating-text-container {
            position: relative;
            display: inline-block;
        }

        .rotating-text {
            display: inline-block;
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            transition: opacity 0.8s ease-in-out;
        }

        .rotating-text.active {
            opacity: 1;
            position: relative;
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

        /* Featured Experiences Section */
        .featured-experiences-section {
            padding: 100px 40px;
            max-width: 1400px;
            margin: 0 auto;
            background: #f8f9fa;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 16px;
            color: #1a1a1a;
        }

        .section-header p {
            font-size: 18px;
            color: #666;
        }

        /* Featured Experiences Slider */
        .featured-slider-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .featured-slider-wrapper {
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .featured-slider {
            display: flex;
            gap: 24px;
            transition: transform 0.5s ease-in-out;
        }

        .featured-card {
            min-width: 350px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .featured-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .featured-card-image {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
        }

        .featured-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .featured-card:hover .featured-card-image img {
            transform: scale(1.05);
        }

        .verified-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #0f5132;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .featured-card-content {
            padding: 24px;
        }

        .featured-category {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .featured-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1a1a1a;
            line-height: 1.3;
        }

        .featured-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .featured-rating .stars {
            color: #ffc107;
            font-size: 14px;
        }

        .featured-rating .rating-value {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 14px;
        }

        .featured-rating .booking-count {
            color: #999;
            font-size: 13px;
        }

        .featured-provider {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .featured-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
        }

        .featured-price {
            display: flex;
            flex-direction: column;
        }

        .price-amount {
            font-size: 24px;
            font-weight: 700;
            color: #1b4332;
        }

        .price-unit {
            font-size: 12px;
            color: #999;
        }

        .btn-featured {
            padding: 10px 20px;
            background: #1b4332;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-featured:hover {
            background: #2d6a4f;
        }

        .slider-arrow {
            width: 50px;
            height: 50px;
            background: white;
            border: 2px solid #1b4332;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1b4332;
            font-size: 18px;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .slider-arrow:hover,
        .slider-arrow:focus {
            background: #1b4332;
            color: white;
            transform: scale(1.1);
            outline: none;
        }

        .slider-arrow:active {
            transform: scale(0.95);
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
            /* Navigation */
            .nav-container {
                padding: 0 20px;
            }

            .nav-actions {
                gap: 8px;
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

            .btn-signin {
                padding: 8px 16px;
                font-size: 0.85rem;
            }

            .profile-name {
                display: none; /* Hide name on mobile */
            }

            /* Hero Section */
            .hero-text h1 {
                font-size: 2rem;
                line-height: 1.2;
            }

            .hero-text p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 12px;
            }

            .btn-choose, .btn-become {
                width: 100%;
                text-align: center;
                padding: 14px 24px;
            }

            /* Search Section */
            .search-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .search-field input,
            .search-field select {
                font-size: 0.9rem;
            }

            .btn-search {
                width: 100%;
            }

            /* Grids */
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }

            /* Cards */
            .service-card {
                margin: 0;
            }

            /* Footer */
            footer {
                padding: 40px 20px 20px;
            }
        }

        /* Very Small Screens (Phones) */
        @media (max-width: 480px) {
            .main-nav {
                padding: 12px 0;
            }

            .logo {
                font-size: 1.4rem;
            }

            .nav-actions {
                gap: 6px;
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

            .theme-toggle-btn i {
                font-size: 0.9rem;
            }

            .btn-signin {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .hamburger {
                width: 32px;
                height: 32px;
            }

            .hamburger span {
                height: 2px;
            }

            .hero-text h1 {
                font-size: 1.75rem;
                min-height: 2.1em; /* Prevent layout shift on small mobile */
            }

            .hero-text p {
                font-size: 0.9rem;
            }

            .featured-experiences-section {
                padding: 40px 15px;
            }

            .section-header h2 {
                font-size: 1.5rem;
            }

            .featured-card {
                min-width: 100%;
            }

            .featured-slider-container {
                gap: 10px;
            }

            .slider-arrow {
                width: 36px;
                height: 36px;
                font-size: 12px;
            }

            .search-section h2 {
                font-size: 1.5rem;
            }

            .services-section h2 {
                font-size: 1.75rem;
            }

            .btn-choose, .btn-become {
                font-size: 0.9rem;
                padding: 12px 20px;
            }
        }

        /* Dark Mode Mobile Adjustments */
        [data-theme="dark"] .nav-menu {
            background: #2d2d2d !important;
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

        [data-theme="dark"] .theme-toggle-btn:hover,
        [data-theme="dark"] .language-selector:hover {
            border-color: #2d6a4f !important;
        }

        /* Community Tourism Section */
        .community-section {
            padding: 100px 40px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }

        .community-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .community-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .community-header h2 {
            font-size: 42px;
            font-weight: 800;
            color: #1b4332;
            margin-bottom: 16px;
        }

        .community-header p {
            font-size: 18px;
            color: #4b5563;
            max-width: 700px;
            margin: 0 auto;
        }

        .community-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1b4332;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .community-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .community-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .community-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }

        .community-image {
            height: 220px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .community-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .community-image i {
            font-size: 48px;
            color: rgba(255,255,255,0.3);
        }

        .community-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #d4a017;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .community-content {
            padding: 20px;
        }

        .community-content h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .community-content p {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .community-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: #6b7280;
        }

        .community-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Festivals Section */
        .festivals-section {
            padding: 100px 40px;
            background: white;
        }

        .festivals-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .festivals-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 40px;
        }

        .festivals-header h2 {
            font-size: 42px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .festivals-header p {
            font-size: 16px;
            color: #6b7280;
        }

        .btn-view-all {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1b4332;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-view-all:hover {
            background: #143728;
            transform: translateY(-2px);
        }

        .festivals-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .festival-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }

        .festival-card:hover {
            border-color: #1b4332;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .festival-date {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .festival-date-box {
            background: #f0fdf4;
            padding: 12px 16px;
            border-radius: 12px;
            text-align: center;
            min-width: 70px;
        }

        .festival-date-day {
            font-size: 24px;
            font-weight: 800;
            color: #1b4332;
            line-height: 1;
        }

        .festival-date-month {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }

        .festival-type {
            font-size: 11px;
            background: #fef3c7;
            color: #92400e;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .festival-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .festival-location {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Provider Stories Section */
        .stories-section {
            padding: 100px 40px;
            background: #fafafa;
        }

        .stories-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .stories-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .stories-header h2 {
            font-size: 42px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 16px;
        }

        .stories-header p {
            font-size: 18px;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }

        .stories-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .story-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .story-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }

        .story-image {
            height: 240px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .story-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .story-image i {
            font-size: 64px;
            color: rgba(255,255,255,0.2);
        }

        .story-badge {
            position: absolute;
            bottom: -20px;
            left: 24px;
            background: #d4a017;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .story-content {
            padding: 32px 24px 24px;
        }

        .story-content h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .story-location {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .story-quote {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.7;
            font-style: italic;
            position: relative;
            padding-left: 16px;
            border-left: 3px solid #d4a017;
        }

        .story-impact {
            display: flex;
            gap: 24px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .impact-item {
            text-align: center;
        }

        .impact-item strong {
            display: block;
            font-size: 18px;
            font-weight: 700;
            color: #1b4332;
        }

        .impact-item span {
            font-size: 11px;
            color: #6b7280;
        }

        @media (max-width: 1024px) {
            .community-grid,
            .festivals-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .stories-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .community-grid,
            .festivals-grid {
                grid-template-columns: 1fr;
            }
            .festivals-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            .community-header h2,
            .festivals-header h2,
            .stories-header h2 {
                font-size: 28px;
            }
        }
</head>
<body>
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

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
    <?php 
    // Include Adinkra symbols for decorative use
    if (file_exists('includes/adinkra_symbols.php')) {
        include 'includes/adinkra_symbols.php';
    }
    ?>
    <nav class="main-nav" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <a href="index_tourlink.php" class="logo" aria-label="TourLink Home">TourLink<span class="logo-dot">.</span></a>
            <ul class="nav-menu" id="navMenu" role="menubar">
                <li><a href="index_tourlink.php" data-i18n="nav.home">Home</a></li>
                <li><a href="view/all_services.php" data-i18n="nav.destinations">Browse Services</a></li>
                <li><a href="view/about.php" data-i18n="nav.about">About</a></li>
                <li><a href="view/contact.php" data-i18n="nav.contact">Contact</a></li>
            </ul>
            <div class="nav-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Profile Dropdown -->
                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="profile-trigger" onclick="toggleProfileDropdown()">
                            <?php if(!empty($_SESSION['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile" class="profile-pic">
                            <?php else: ?>
                                <div class="profile-pic-placeholder">
                                    <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <span class="profile-name"><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                            <i class="fa fa-chevron-down dropdown-icon"></i>
                        </div>
                        <div class="dropdown-menu-custom">
                            <a href="view/profile_settings.php">
                                <i class="fa fa-user-circle"></i>
                                Profile Settings
                            </a>
                            <a href="view/my_favorites.php">
                                <i class="fas fa-heart"></i>
                                <span data-i18n="favorites.my_favorites">My Favorites</span>
                            </a>
                            <?php if($_SESSION['user_type'] !== 'provider'): ?>
                            <a href="view/my_bookings.php">
                                <i class="fa fa-calendar-check"></i>
                                My Bookings
                            </a>
                            <?php endif; ?>
                            <?php if($_SESSION['user_type'] === 'provider'): ?>
                                <div class="dropdown-divider"></div>
                                <a href="admin/provider_dashboard.php">
                                    <i class="fa fa-th-large"></i>
                                    Dashboard
                                </a>
                                <a href="admin/manage_services.php">
                                    <i class="fa fa-briefcase"></i>
                                    My Services
                                </a>
                                <a href="view/provider/manage_bookings.php">
                                    <i class="fa fa-calendar-check"></i>
                                    Bookings
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0)" onclick="toggleTheme(); event.stopPropagation();" id="themeToggle">
                                <i class="fa fa-moon"></i>
                                <span class="toggle-text">Dark Mode</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="login/logout.php">
                                <i class="fa fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login/login.php" class="btn-signin" data-i18n="nav.sign_in">Sign In</a>
                <?php endif; ?>

                <!-- Language Switcher (visible to all) -->
                <select id="languageSelector" class="language-selector" aria-label="Select language" onchange="changeLanguage(this.value)">
                    <option value="en">English</option>
                    <option value="fr">Français</option>
                    <option value="es">Español</option>
                    <option value="tw">Twi</option>
                    <option value="ga">Ga</option>
                </select>

                <!-- Theme Toggle (visible to all) -->
                <button onclick="toggleTheme()" class="theme-toggle-btn" id="publicThemeToggle" aria-label="Toggle dark mode">
                    <i class="fa fa-moon"></i>
                </button>

                <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="navMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="main-content" role="main">
        <?php if (function_exists('get_adinkra_symbol')): ?>
        <div style="position: absolute; top: 20px; right: 20px; opacity: 0.3; z-index: 1;">
            <?php echo get_adinkra_symbol('gye_nyame', '60px', '#d4a017'); ?>
        </div>
        <?php endif; ?>
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="rotating-text-container">
                    <span class="rotating-text active" data-text="0">Discover Ghana's Hidden Roots</span>
                    <span class="rotating-text" data-text="1">Taste, Feel, and Explore Ghana</span>
                    <span class="rotating-text" data-text="2">Chale, Let's Go Explore</span>
                    <span class="rotating-text" data-text="3">Akwaaba, Welcome Home</span>
                </h1>
                <p data-i18n="hero.subtitle">Discover tours tailored to your dream destinations, from cultural escapes to adventure getaways. Book in minutes, travel without limits.</p>
                <p style="color: rgba(255,255,255,0.9); font-size: 15px; margin-top: 12px; font-style: italic;">
                    <em>Experience the beauty of Ghana — Yɛbɛhyia biom!</em>
                </p>
                <div class="hero-buttons">
                    <a href="view/all_services.php" class="btn-choose" data-i18n="hero.cta_primary">Choose a Destinations</a>
                    <a href="view/become_provider.php" class="btn-become" data-i18n="hero.cta_secondary">Become a Provider</a>
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
                    <label><i class="fas fa-map-marker-alt"></i> <span data-i18n="search.location">Destination</span></label>
                    <select name="region">
                        <option value="" data-i18n="search.all_regions">All Regions</option>
                        <option value="Greater Accra" data-i18n="regions.greater_accra">Greater Accra</option>
                        <option value="Central" data-i18n="regions.central">Central Region</option>
                        <option value="Ashanti" data-i18n="regions.ashanti">Ashanti Region</option>
                        <option value="Northern" data-i18n="regions.northern">Northern Region</option>
                    </select>
                </div>
                <div class="search-field">
                    <label><i class="fas fa-th-large"></i> <span data-i18n="search.category">Tour Type</span></label>
                    <select name="category">
                        <option value="" data-i18n="search.all_services">All Services</option>
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
                    <label><i class="fas fa-calendar"></i> <span data-i18n="search.date">Date</span></label>
                    <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="search-field">
                    <label><i class="fas fa-users"></i> <span data-i18n="search.travelers">Number of Travelers</span></label>
                    <input type="number" name="people" placeholder="1" min="1" value="1">
                </div>
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i>
                    <span data-i18n="search.search_button">Search</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Trust Indicators -->
    <div class="trust-section">
        <div class="trust-item">
            <i class="fas fa-star"></i>
            <strong data-i18n="trust.reviews">4.8/5 (10K+ Reviews)</strong>
        </div>
        <div class="trust-item">
            <i class="fas fa-users"></i>
            <strong data-i18n="trust.travelers">Trusted by 500K+ Travelers</strong>
        </div>
        <div class="trust-item">
            <i class="fas fa-lock"></i>
            <strong data-i18n="trust.secure">Secure Booking</strong>
        </div>
    </div>

    <!-- Featured Experiences -->
    <?php if (!empty($premium_services)): ?>
    <section class="featured-experiences-section">
        <div class="section-header">
            <h2>Featured Experiences</h2>
            <p>Handpicked experiences from trusted local hosts across Ghana.</p>
        </div>

        <div class="featured-slider-container">
            <button class="slider-arrow slider-arrow-left" aria-label="Previous experiences" onclick="slideFeaturedExperiences(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="featured-slider-wrapper">
                <div class="featured-slider" id="featuredSlider">
                    <?php foreach($premium_services as $service):
                        $images = json_decode($service['service_images'], true);
                        $first_image = $images[0] ?? 'default.jpg';
                        $rating = round($service['average_rating'] ?? 0, 1);
                        $is_verified = $service['verification_status'] === 'verified';
                    ?>
                    <div class="featured-card">
                        <div class="featured-card-image">
                            <img src="uploads/services/<?php echo htmlspecialchars($first_image); ?>"
                                 alt="<?php echo htmlspecialchars($service['service_title']); ?>">
                            <?php if ($is_verified): ?>
                            <div class="verified-badge">
                                <i class="fas fa-check-circle"></i> Verified
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="featured-card-content">
                            <div class="featured-category">
                                <?php echo htmlspecialchars($service['category_name']); ?>
                            </div>
                            <h3 class="featured-title"><?php echo htmlspecialchars($service['service_title']); ?></h3>
                            <div class="featured-rating">
                                <span class="stars">
                                    <?php for($i = 0; $i < 5; $i++): ?>
                                        <?php if ($i < floor($rating)): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i < ceil($rating)): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </span>
                                <span class="rating-value"><?php echo $rating; ?></span>
                                <span class="booking-count">(<?php echo $service['total_bookings']; ?> bookings)</span>
                            </div>
                            <div class="featured-provider">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($service['business_name']); ?>
                            </div>
                            <div class="featured-footer">
                                <div class="featured-price">
                                    <span class="price-amount">GH₵ <?php echo number_format($service['base_price'], 0); ?></span>
                                    <span class="price-unit">per <?php echo $service['pricing_unit'] === 'per_person' ? 'person' : ($service['pricing_unit'] === 'per_hour' ? 'hour' : 'day'); ?></span>
                                </div>
                                <a href="view/single_service.php?service_id=<?php echo $service['service_id']; ?>" class="btn-featured">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button class="slider-arrow slider-arrow-right" aria-label="Next experiences" onclick="slideFeaturedExperiences(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </section>
    <?php endif; ?>

    <!-- Why Choose TourLink -->
    <section class="why-choose">
        <div class="why-choose-container">
            <div>
                <h2 data-i18n="why_choose.title">Why Choose TourLink?</h2>
                <p data-i18n="why_choose.subtitle">We don't just book trips — we create unforgettable experiences. From personalized service to handpicked travel partners, we ensure every part of your journey is smooth, safe, and inspiring.</p>
            </div>
            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="feature-text">
                        <h4 data-i18n="why_choose.best_price">Best Price Guarantee</h4>
                        <p data-i18n="why_choose.best_price_desc">Found a better price elsewhere? We'll match it and give you an additional 10% off your booking.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-text">
                        <h4 data-i18n="why_choose.24_7_support">24/7 Customer Support</h4>
                        <p data-i18n="why_choose.24_7_support_desc">Our travel experts are available round-the-clock to assist you, no matter where you are in the world.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="feature-text">
                        <h4 data-i18n="why_choose.flexible_booking">Flexible Booking Options</h4>
                        <p data-i18n="why_choose.flexible_booking_desc">Flexible cancellation policies on most bookings with full refunds up to 48 hours before travel.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-text">
                        <h4 data-i18n="why_choose.verified_providers">Verified Travel Partners</h4>
                        <p data-i18n="why_choose.verified_providers_desc">Each hotel, guide, and experience partner is carefully vetted to meet our quality and safety standards.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Community Tourism Section -->
    <section class="community-section">
        <div class="community-container">
            <div class="community-header">
                <span class="community-badge">
                    <i class="fas fa-hands-helping"></i>
                    Supporting Local Communities
                </span>
                <h2>Village Experiences & Community Tourism</h2>
                <p>Immerse yourself in authentic Ghanaian culture. Visit rural communities, learn traditional crafts, and directly support local livelihoods through responsible tourism.</p>
                <a href="view/community_tourism.php" class="btn-view-all" style="margin-top: 24px;">
                    Explore All Experiences <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="community-grid">
                <div class="community-card">
                    <div class="community-image">
                        <img src="assets/images/experiences/kente-weaving.jpg" alt="Kente Weaving" onerror="this.parentElement.innerHTML='<i class=\'fas fa-palette\'></i>';">
                        <span class="community-tag">Craft Village</span>
                    </div>
                    <div class="community-content">
                        <h3>Kente Weaving Village Tour</h3>
                        <p>Visit Bonwire, the birthplace of Kente cloth. Watch master weavers create intricate patterns passed down through generations.</p>
                        <div class="community-meta">
                            <span><i class="fas fa-map-marker-alt"></i> Ashanti Region</span>
                            <span><i class="fas fa-users"></i> 12 families supported</span>
                        </div>
                    </div>
                </div>

                <div class="community-card">
                    <div class="community-image">
                        <img src="assets/images/experiences/cooking-class.jpg" alt="Cooking Class" onerror="this.parentElement.innerHTML='<i class=\'fas fa-utensils\'></i>';">
                        <span class="community-tag">Culinary</span>
                    </div>
                    <div class="community-content">
                        <h3>Farm-to-Table Cooking Class</h3>
                        <p>Learn to prepare authentic Ghanaian dishes with local mothers using fresh ingredients from community farms.</p>
                        <div class="community-meta">
                            <span><i class="fas fa-map-marker-alt"></i> Central Region</span>
                            <span><i class="fas fa-users"></i> 8 families supported</span>
                        </div>
                    </div>
                </div>

                <div class="community-card">
                    <div class="community-image">
                        <img src="assets/images/experiences/drumming-dance.jpg" alt="Traditional Drumming" onerror="this.parentElement.innerHTML='<i class=\'fas fa-drum\'></i>';">
                        <span class="community-tag">Cultural</span>
                    </div>
                    <div class="community-content">
                        <h3>Traditional Drumming & Dance</h3>
                        <p>Join village elders in learning the rhythms and movements of traditional Ghanaian ceremonies and celebrations.</p>
                        <div class="community-meta">
                            <span><i class="fas fa-map-marker-alt"></i> Greater Accra</span>
                            <span><i class="fas fa-users"></i> 15 artists supported</span>
                        </div>
                    </div>
                </div>

                <div class="community-card">
                    <div class="community-image">
                        <img src="assets/images/experiences/fishing-village.jpg" alt="Fishing Village" onerror="this.parentElement.innerHTML='<i class=\'fas fa-fish\'></i>';">
                        <span class="community-tag">Eco-Tourism</span>
                    </div>
                    <div class="community-content">
                        <h3>Fishing Village Experience</h3>
                        <p>Spend a day with traditional fishing communities. Learn age-old techniques and enjoy the freshest catch of the day.</p>
                        <div class="community-meta">
                            <span><i class="fas fa-map-marker-alt"></i> Central Region</span>
                            <span><i class="fas fa-users"></i> 20 families supported</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Festivals Section -->
    <?php if ($upcoming_festivals && count($upcoming_festivals) > 0): ?>
    <section class="festivals-section">
        <div class="festivals-container">
            <div class="festivals-header">
                <div>
                    <h2>Ghana Festival Calendar</h2>
                    <p>Experience the vibrant cultural celebrations across Ghana's regions</p>
                </div>
                <a href="view/festivals.php" class="btn-view-all">
                    View Full Calendar <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="festivals-grid">
                <?php foreach ($upcoming_festivals as $festival): ?>
                <div class="festival-card">
                    <div class="festival-date">
                        <div class="festival-date-box">
                            <div class="festival-date-day"><?php echo date('d', strtotime($festival['start_date'])); ?></div>
                            <div class="festival-date-month"><?php echo date('M', strtotime($festival['start_date'])); ?></div>
                        </div>
                        <span class="festival-type"><?php echo htmlspecialchars($festival['festival_type']); ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($festival['festival_name']); ?></h3>
                    <p class="festival-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($festival['location'] . ', ' . $festival['region']); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Provider Success Stories -->
    <section class="stories-section">
        <div class="stories-container">
            <div class="stories-header">
                <h2>Provider Success Stories</h2>
                <p>Meet the local entrepreneurs building sustainable tourism businesses across Ghana</p>
            </div>

            <div class="stories-grid">
                <div class="story-card">
                    <div class="story-image">
                        <img src="assets/images/providers/kwadwo-asante.jpg" alt="Kwadwo Asante" onerror="this.parentElement.innerHTML='<i class=\'fas fa-user\'></i>';">
                        <span class="story-badge">Tour Guide</span>
                    </div>
                    <div class="story-content">
                        <h3>Kwadwo Asante</h3>
                        <p class="story-location"><i class="fas fa-map-marker-alt"></i> Cape Coast, Central Region</p>
                        <p class="story-quote">"TourLink helped me turn my passion for history into a thriving business. I now employ 3 other guides and have hosted over 500 tourists from around the world."</p>
                        <div class="story-impact">
                            <div class="impact-item">
                                <strong>500+</strong>
                                <span>Tourists Guided</span>
                            </div>
                            <div class="impact-item">
                                <strong>3</strong>
                                <span>Jobs Created</span>
                            </div>
                            <div class="impact-item">
                                <strong>4.9</strong>
                                <span>Avg Rating</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="story-card">
                    <div class="story-image">
                        <img src="assets/images/providers/akosua-mensah.jpg" alt="Akosua Mensah" onerror="this.parentElement.innerHTML='<i class=\'fas fa-user\'></i>';">
                        <span class="story-badge">Accommodation</span>
                    </div>
                    <div class="story-content">
                        <h3>Akosua Mensah</h3>
                        <p class="story-location"><i class="fas fa-map-marker-alt"></i> Kumasi, Ashanti Region</p>
                        <p class="story-quote">"From a single guest room to a 12-room eco-lodge, TourLink connected me with tourists seeking authentic experiences. My children now help run the business."</p>
                        <div class="story-impact">
                            <div class="impact-item">
                                <strong>12</strong>
                                <span>Rooms</span>
                            </div>
                            <div class="impact-item">
                                <strong>5</strong>
                                <span>Family Members</span>
                            </div>
                            <div class="impact-item">
                                <strong>4.8</strong>
                                <span>Avg Rating</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="story-card">
                    <div class="story-image">
                        <img src="assets/images/providers/yaw-boateng.jpg" alt="Yaw Boateng" onerror="this.parentElement.innerHTML='<i class=\'fas fa-user\'></i>';">
                        <span class="story-badge">Transport</span>
                    </div>
                    <div class="story-content">
                        <h3>Yaw Boateng</h3>
                        <p class="story-location"><i class="fas fa-map-marker-alt"></i> Accra, Greater Accra</p>
                        <p class="story-quote">"Starting with one vehicle, I now operate a fleet of 5 tour buses. TourLink's platform gave me visibility and trust with international visitors."</p>
                        <div class="story-impact">
                            <div class="impact-item">
                                <strong>5</strong>
                                <span>Vehicles</span>
                            </div>
                            <div class="impact-item">
                                <strong>8</strong>
                                <span>Drivers Employed</span>
                            </div>
                            <div class="impact-item">
                                <strong>4.7</strong>
                                <span>Avg Rating</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="testimonials-container">
            <div class="section-header">
                <h2 data-i18n="testimonials.title">What Our Travelers Say</h2>
                <p data-i18n="testimonials.subtitle">Don't just take our word for it - hear from our satisfied customers</p>
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
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/index_tourlink.js"></script>
</body>
</html>
