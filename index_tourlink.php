<?php
// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once 'settings/core.php';
require_once 'controllers/service_controller.php';
require_once 'controllers/service_category_controller.php';
require_once 'classes/festival_class.php';
require_once 'classes/hosted_upload_class.php';

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
                            <?php 
                            require_once 'classes/hosted_upload_class.php';
                            if(!empty($_SESSION['profile_image'])): 
                                $profile_img_url = HostedUpload::getImageUrl($_SESSION['profile_image'], '');
                            ?>
                                <img src="<?php echo htmlspecialchars($profile_img_url); ?>" alt="Profile" class="profile-pic" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="profile-pic-placeholder" style="display:none;">
                                    <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                                </div>
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
            <strong data-i18n="trust.reviews">4.8/5 (200+ Reviews)</strong>
        </div>
        <div class="trust-item">
            <i class="fas fa-users"></i>
            <strong data-i18n="trust.travelers">Trusted by 500+ Travelers</strong>
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
                        $first_image = $images[0] ?? null;
                        $image_url = $first_image ? HostedUpload::getImageUrl($first_image, '') : null;
                        $rating = round($service['average_rating'] ?? 0, 1);
                        $is_verified = $service['verification_status'] === 'verified';
                    ?>
                    <div class="featured-card">
                        <div class="featured-card-image">
                            <?php if ($image_url): ?>
                            <img src="<?php echo htmlspecialchars($image_url); ?>"
                                 alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="featured-image-placeholder" style="display:none; background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); height: 100%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                            </div>
                            <?php else: ?>
                            <div class="featured-image-placeholder" style="background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%); height: 100%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-image fa-3x" style="color: white; opacity: 0.5;"></i>
                            </div>
                            <?php endif; ?>
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
                        <h4 data-i18n="why_choose.24_7_support">Customer Support</h4>
                        <p data-i18n="why_choose.24_7_support_desc">Our travel experts are available to assist you, no matter where you are in the world.</p>
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
