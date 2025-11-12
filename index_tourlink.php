<?php
require_once 'settings/core.php';
require_once 'controllers/service_controller.php';
require_once 'controllers/service_category_controller.php';

// Get data for homepage
$featured_services = get_all_services_ctr();
$categories = get_all_service_categories_ctr();

// Limit to 6 featured services
if ($featured_services && count($featured_services) > 6) {
    $featured_services = array_slice($featured_services, 0, 6);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Home - TourLink | Connecting Ghana, One Experience at a Time</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
</head>
<body>
    <!-- Animated Background Elements -->
    <div class="animated-bg">
        <div class="dots-pattern"></div>
        <div class="parallax-shape parallax-shape-1"></div>
        <div class="parallax-shape parallax-shape-2"></div>
        <div class="parallax-shape parallax-shape-3"></div>
    </div>

    <!-- Navigation Header -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="view/all_services.php" class="nav-link">Browse Services</a>
                <a href="view/cart.php" class="nav-link" style="position: relative; margin-right: 20px;">
                    <i class="fa fa-shopping-cart"></i> Cart
                    <span class="cart-count" style="position: absolute; top: -8px; right: -10px; background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold;">0</span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="nav-user">
                        <i class="fa fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
                <?php else: ?>
                    <a href="login/login.php" class="nav-link">Sign in</a>
                    <a href="login/register.php" class="btn-nav btn-nav-join">Join</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if(!isset($_SESSION['user_id'])): ?>
        <!-- Hero Section for guests -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Discover Ghana's <span class="highlight carousel-text" id="carouselText">Hidden Gems</span>
                    </h1>
                    <p class="hero-subtitle">
                        Connect with local tour guides, drivers, artisans, and cultural experts
                    </p>
                    <form action="view/search_services.php" method="GET" class="hero-search">
                        <input type="text" name="q" placeholder="Search for services, tour guides, drivers..." class="search-input" required>
                        <button type="submit" class="search-btn">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                    <div class="hero-tags">
                        <span>Popular:</span>
                        <a href="view/all_services.php?category=1" class="tag">Tour Guides</a>
                        <a href="view/all_services.php?category=2" class="tag">Drivers</a>
                        <a href="view/all_services.php?category=3" class="tag">Catering</a>
                        <a href="view/all_services.php?category=4" class="tag">Translation</a>
                    </div>
                </div>

                <!-- Pinterest-style Image Carousel -->
                <div class="pinterest-carousel">
                    <div class="carousel-column carousel-column-1">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=400&h=500&fit=crop" alt="Ghana Tourism">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1609137144813-7d9921338f24?w=400&h=600&fit=crop" alt="African Culture">
                        </div>
                    </div>
                    <div class="carousel-column carousel-column-2">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=400&h=550&fit=crop" alt="Tour Guide">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=450&fit=crop" alt="Local Market">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section with Counter Animation -->
        <section class="stats-section fade-in-section">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number" data-target="500">0</div>
                    <div class="stat-label">Happy Tourists</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $featured_services ? count($featured_services) : 50; ?>">0</div>
                    <div class="stat-label">Tourism Services</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $categories ? count($categories) : 7; ?>">0</div>
                    <div class="stat-label">Service Categories</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="99">0</div>
                    <div class="stat-label">Satisfaction Rate</div>
                    <span class="stat-suffix">%</span>
                </div>
            </div>
        </section>

        <!-- Shop by Category Section -->
        <?php if($categories && count($categories) > 0): ?>
        <section class="categories-section fade-in-section">
            <div class="categories-container">
                <h2 class="section-title">Browse by Service Category</h2>
                <p class="section-subtitle">Explore our wide range of tourism services</p>
                <div class="categories-grid">
                    <?php
                    $category_icons = [
                        'Tour Guides' => 'fa-map-marked-alt',
                        'Drivers' => 'fa-car',
                        'Transportation' => 'fa-bus',
                        'Catering' => 'fa-utensils',
                        'Translation' => 'fa-language',
                        'Cultural' => 'fa-music',
                        'Artisans' => 'fa-paint-brush',
                        'Accommodation' => 'fa-hotel'
                    ];
                    foreach($categories as $category):
                        $cat_name = $category['category_name'];
                        $icon = 'fa-tag'; // default icon
                        foreach($category_icons as $key => $val) {
                            if(stripos($cat_name, $key) !== false) {
                                $icon = $val;
                                break;
                            }
                        }
                    ?>
                    <a href="view/all_services.php?category=<?php echo $category['category_id']; ?>" class="category-card ripple-effect">
                        <div class="category-icon">
                            <i class="fa <?php echo $icon; ?>"></i>
                        </div>
                        <h3 class="category-name"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                        <div class="category-arrow">
                            <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Featured Services Section -->
        <?php if($featured_services && count($featured_services) > 0): ?>
        <section class="featured-products-section fade-in-section">
            <div class="featured-container">
                <h2 class="section-title">Featured Tourism Services</h2>
                <p class="section-subtitle">Discover top-rated services from local providers</p>
                <div class="products-grid">
                    <?php foreach($featured_services as $service): ?>
                    <div class="product-card-home ripple-effect">
                        <a href="view/single_service.php?id=<?php echo $service['service_id']; ?>" class="product-link">
                            <?php
                            $images = json_decode($service['service_images'], true);
                            $first_image = is_array($images) && !empty($images) ? $images[0] : null;
                            ?>
                            <?php if ($first_image): ?>
                                <div class="product-image-wrapper">
                                    <img src="<?php echo htmlspecialchars($first_image); ?>"
                                         alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                         class="product-img">
                                </div>
                            <?php else: ?>
                                <div class="product-image-placeholder-home">
                                    <i class="fa fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-details">
                                <div class="product-category-badge">
                                    <?php echo htmlspecialchars($service['category_name']); ?>
                                </div>
                                <h3 class="product-title-home"><?php echo htmlspecialchars($service['service_title']); ?></h3>
                                <div class="product-brand-home">
                                    <i class="fa fa-user"></i> <?php echo htmlspecialchars($service['provider_name'] ?: ($service['provider_first_name'] . ' ' . $service['provider_last_name'])); ?>
                                </div>
                                <div class="product-price-home">
                                    GHS <?php echo number_format($service['base_price'], 2); ?> / <?php echo htmlspecialchars($service['pricing_unit']); ?>
                                </div>
                            </div>
                        </a>
                        <button class="btn-add-cart-home ripple-btn" onclick="event.preventDefault(); alert('Please sign in to book this service');">
                            <i class="fa fa-calendar-check"></i> Book Now
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="view-all-container">
                    <a href="view/all_services.php" class="btn-view-all ripple-btn">
                        View All Services <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Features Section -->
        <section class="features-section fade-in-section">
            <div class="features-container">
                <h2 class="section-title">Why choose TourLink?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-shield-alt"></i>
                        </div>
                        <h3>Secure Payments</h3>
                        <p>Escrow-based payments protect both tourists and service providers</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <h3>Verified Providers</h3>
                        <p>All service providers are verified for your safety and peace of mind</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-star"></i>
                        </div>
                        <h3>Rated & Reviewed</h3>
                        <p>Read authentic reviews from fellow tourists</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-headset"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Our team is here to help you anytime, anywhere</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-container">
                <h2>Ready to explore Ghana?</h2>
                <p>Join hundreds of satisfied tourists and service providers today</p>
                <div class="cta-buttons">
                    <a href="login/register.php" class="btn-cta btn-cta-primary">
                        <i class="fa fa-user-plus"></i> Register Now
                    </a>
                    <a href="login/login.php" class="btn-cta btn-cta-secondary">
                        <i class="fa fa-sign-in-alt"></i> Sign In
                    </a>
                </div>
            </div>
        </section>
    <?php else: ?>
        <!-- Logged in user view -->
        <section class="hero-section hero-dashboard">
            <div class="hero-container-logged">
                <div class="hero-content-logged">
                    <h1 class="hero-title-logged">
                        Welcome back, <span class="highlight"><?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span> 👋
                    </h1>
                    <p class="hero-subtitle">
                        Ready to explore Ghana's amazing tourism services?
                    </p>

                    <!-- Search -->
                    <form action="view/search_services.php" method="GET" class="hero-search">
                        <input type="text" name="q" placeholder="Search for services..." class="search-input" required>
                        <button type="submit" class="search-btn">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Services Section for logged-in users -->
        <?php if($featured_services && count($featured_services) > 0): ?>
        <section class="featured-products-section">
            <div class="featured-container">
                <h2 class="section-title">Available Tourism Services</h2>
                <div class="products-grid">
                    <?php foreach($featured_services as $service): ?>
                    <div class="product-card-home ripple-effect">
                        <a href="view/single_service.php?id=<?php echo $service['service_id']; ?>" class="product-link">
                            <?php
                            $images = json_decode($service['service_images'], true);
                            $first_image = is_array($images) && !empty($images) ? $images[0] : null;
                            ?>
                            <?php if ($first_image): ?>
                                <div class="product-image-wrapper">
                                    <img src="<?php echo htmlspecialchars($first_image); ?>"
                                         alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                                         class="product-img">
                                </div>
                            <?php else: ?>
                                <div class="product-image-placeholder-home">
                                    <i class="fa fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-details">
                                <div class="product-category-badge">
                                    <?php echo htmlspecialchars($service['category_name']); ?>
                                </div>
                                <h3 class="product-title-home"><?php echo htmlspecialchars($service['service_title']); ?></h3>
                                <div class="product-brand-home">
                                    <i class="fa fa-user"></i> <?php echo htmlspecialchars($service['provider_name'] ?: ($service['provider_first_name'] . ' ' . $service['provider_last_name'])); ?>
                                </div>
                                <div class="product-price-home">
                                    GHS <?php echo number_format($service['base_price'], 2); ?> / <?php echo htmlspecialchars($service['pricing_unit']); ?>
                                </div>
                            </div>
                        </a>
                        <button class="btn-add-cart-home ripple-btn" onclick="event.preventDefault(); addToCart(<?php echo $service['service_id']; ?>);">
                            <i class="fa fa-calendar-check"></i> Book Now
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Text carousel
        const carouselTexts = [
            { text: 'Hidden Gems', color: '#679267' },
            { text: 'Cultural Experiences', color: '#ff6b6b' },
            { text: 'Local Guides', color: '#3b82f6' },
            { text: 'Authentic Tours', color: '#8b5cf6' },
            { text: 'Adventure Awaits', color: '#f59e0b' }
        ];

        let currentIndex = 0;
        const carouselTextElement = document.getElementById('carouselText');

        if (carouselTextElement) {
            function changeCarouselText() {
                carouselTextElement.style.opacity = '0';
                setTimeout(() => {
                    currentIndex = (currentIndex + 1) % carouselTexts.length;
                    carouselTextElement.textContent = carouselTexts[currentIndex].text;
                    carouselTextElement.style.color = carouselTexts[currentIndex].color;
                    carouselTextElement.style.opacity = '1';
                }, 300);
            }
            setInterval(changeCarouselText, 3000);
        }

        // Scroll animations
        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-visible');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.fade-in-section').forEach(section => {
            fadeInObserver.observe(section);
        });

        // Counter animation
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    entry.target.classList.add('counted');
                    animateCounter(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-number').forEach(counter => {
            counterObserver.observe(counter);
        });

        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;

            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    element.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target.toLocaleString();
                }
            };
            updateCounter();
        }

        // Parallax effect
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const scrolled = window.pageYOffset;
                    const parallaxShapes = document.querySelectorAll('.parallax-shape');
                    parallaxShapes.forEach((shape, index) => {
                        const speed = 0.3 + (index * 0.1);
                        const yPos = -(scrolled * speed);
                        shape.style.transform = `translateY(${yPos}px)`;
                    });
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Ripple effect
        document.querySelectorAll('.ripple-btn, .ripple-effect').forEach(element => {
            element.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add to cart function
        function addToCart(serviceId) {
            // This will be implemented with actual cart functionality
            alert('Add to cart functionality coming soon! Service ID: ' + serviceId);
        }
    </script>
</body>
</html>
