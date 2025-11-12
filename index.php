<?php
require_once 'settings/core.php';
require_once 'controllers/product_controller.php';
require_once 'controllers/category_controller.php';

// Get data for homepage
$featured_products = view_all_products_ctr();
$categories = get_all_categories_ctr();

// Limit to 6 featured products
if ($featured_products && count($featured_products) > 6) {
    $featured_products = array_slice($featured_products, 0, 6);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Home - Shopify</title>
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
                <a href="index.php" class="logo">shopify<span class="logo-dot">.</span></a>
            </div>
            <div class="nav-right">
                <a href="view/all_product.php" class="nav-link">All Products</a>
                <a href="view/cart.php" class="nav-link" style="position: relative; margin-right: 20px;">
                    <i class="fa fa-shopping-cart"></i> Cart
                    <span class="cart-count" style="position: absolute; top: -8px; right: -10px; background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold;">0</span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="nav-user">
                        <i class="fa fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <?php if($_SESSION['user_role']==1): ?>
                        <a href="admin/category.php" class="nav-link">Category</a>
                        <a href="admin/brand.php" class="nav-link">Brand</a>
                        <a href="admin/product.php" class="nav-link">Manage Products</a>
                    <?php endif; ?>
                    <a href="login/logout.php" class="btn-nav btn-nav-logout">Logout</a>
                <?php else: ?>
                    <a href="login/login.php" class="nav-link">Sign in</a>
                    <a href="login/register.php" class="btn-nav btn-nav-join">Join</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Logged in user dashboard -->
        <section class="hero-section hero-dashboard">
            <div class="hero-container-logged">
                <div class="hero-content-logged">
                    <h1 class="hero-title-logged">
                        Welcome back, <span class="highlight"><?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span> 👋
                    </h1>
                    <p class="hero-subtitle">
                        <?php echo $_SESSION['user_role']==1 ? 'Manage your products and grow your business' : 'Discover amazing products from top brands'; ?>
                    </p>

                    <?php if($_SESSION['user_role']==1): ?>
                    <!-- Admin Quick Actions -->
                    <div class="admin-actions-hero">
                        <a href="admin/category.php" class="hero-action-btn">
                            <i class="fa fa-tags"></i>
                            <span>Categories</span>
                        </a>
                        <a href="admin/brand.php" class="hero-action-btn">
                            <i class="fa fa-bookmark"></i>
                            <span>Brands</span>
                        </a>
                        <a href="admin/product.php" class="hero-action-btn">
                            <i class="fa fa-box"></i>
                            <span>Products</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Customer Search -->
                    <form action="view/product_search_result.php" method="GET" class="hero-search">
                        <input type="text" name="q" placeholder="Search for products..." class="search-input" required>
                        <button type="submit" class="search-btn">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- User Info Grid -->
                    <div class="user-info-grid">
                        <div class="user-info-item-compact">
                            <i class="fa fa-envelope"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                        </div>
                        <div class="user-info-item-compact">
                            <i class="fa fa-phone"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_contact']); ?></span>
                        </div>
                        <div class="user-info-item-compact">
                            <i class="fa fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_city'].', '.$_SESSION['user_country']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Pinterest-style Image Carousel for logged-in users too -->
                <div class="pinterest-carousel">
                    <div class="carousel-column carousel-column-1">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=500&fit=crop" alt="Headphones">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=600&fit=crop" alt="Watch">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop" alt="Sunglasses">
                        </div>
                    </div>
                    <div class="carousel-column carousel-column-2">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=550&fit=crop" alt="Shoes">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=400&h=450&fit=crop" alt="Sneakers">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=400&h=500&fit=crop" alt="Perfume">
                        </div>
                    </div>
                    <div class="carousel-column carousel-column-3">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=600&fit=crop" alt="Red Shoes">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=400&h=400&fit=crop" alt="Backpack">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1611312449408-fcece27cdbb7?w=400&h=550&fit=crop" alt="Camera">
                        </div>
                    </div>
                    <div class="carousel-column carousel-column-4">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=400&h=500&fit=crop" alt="Smart Watch">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1598532163257-ae3c6b2524b6?w=400&h=600&fit=crop" alt="Glasses">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1525904097878-94fb15835963?w=400&h=450&fit=crop" alt="Coffee">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products Section (for logged-in users) -->
        <section class="featured-section">
            <div class="features-container">
                <h2 class="section-title"><?php echo $_SESSION['user_role']==1 ? 'Platform Features' : 'Why Shop With Us?'; ?></h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-shield-alt"></i>
                        </div>
                        <h3>Secure Platform</h3>
                        <p>Your data and transactions are protected with industry-leading security</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-truck"></i>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Get your products delivered quickly and efficiently</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-star"></i>
                        </div>
                        <h3>Quality Products</h3>
                        <p>Only the best products from trusted brands</p>
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
    <?php else: ?>
        <!-- Hero Section for guests -->
        <section class="hero-section">
            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Get your next <span class="highlight carousel-text" id="carouselText">amazing product</span>
                    </h1>
                    <p class="hero-subtitle">
                        Discover amazing products from top brands and categories
                    </p>
                    <form action="view/product_search_result.php" method="GET" class="hero-search">
                        <input type="text" name="q" placeholder="Search for products, brands, or categories..." class="search-input" required>
                        <button type="submit" class="search-btn">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                    <div class="hero-tags">
                        <span>Popular:</span>
                        <a href="#" class="tag">Electronics</a>
                        <a href="#" class="tag">Fashion</a>
                        <a href="#" class="tag">Home & Garden</a>
                        <a href="#" class="tag">Sports</a>
                    </div>
                </div>

                <!-- Pinterest-style Image Carousel -->
                <div class="pinterest-carousel">
                    <div class="carousel-column carousel-column-1">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=500&fit=crop" alt="Headphones">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=600&fit=crop" alt="Watch">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop" alt="Sunglasses">
                        </div>
                    </div>
                    <div class="carousel-column carousel-column-2">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=550&fit=crop" alt="Shoes">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=400&h=450&fit=crop" alt="Sneakers">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=400&h=500&fit=crop" alt="Perfume">
                        </div>
                    </div>
                    <div class="carousel-column carousel-column-3">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=600&fit=crop" alt="Red Shoes">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=400&h=400&fit=crop" alt="Backpack">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1611312449408-fcece27cdbb7?w=400&h=550&fit=crop" alt="Camera">
                        </div>
                    </div>
                    <div class="carousel-column carousel-column-4">
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=400&h=500&fit=crop" alt="Smart Watch">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1598532163257-ae3c6b2524b6?w=400&h=600&fit=crop" alt="Glasses">
                        </div>
                        <div class="carousel-image">
                            <img src="https://images.unsplash.com/photo-1525904097878-94fb15835963?w=400&h=450&fit=crop" alt="Coffee">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section with Counter Animation -->
        <section class="stats-section fade-in-section">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number" data-target="15000">0</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $featured_products ? count(view_all_products_ctr()) : 500; ?>">0</div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $categories ? count($categories) : 20; ?>">0</div>
                    <div class="stat-label">Categories</div>
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
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Explore our wide range of product categories</p>
                <div class="categories-grid">
                    <?php
                    $category_icons = [
                        'Electronics' => 'fa-laptop',
                        'Fashion' => 'fa-tshirt',
                        'Home Decor' => 'fa-home',
                        'Home & Outdoors' => 'fa-running',
                        'Sports' => 'fa-futbol',
                        'Books' => 'fa-book'
                    ];
                    $count = 0;
                    foreach($categories as $category):
                        if($count >= 6) break;
                        $cat_name = $category['cat_name'];
                        $icon = 'fa-tag'; // default icon
                        foreach($category_icons as $key => $val) {
                            if(stripos($cat_name, $key) !== false) {
                                $icon = $val;
                                break;
                            }
                        }
                        $count++;
                    ?>
                    <a href="view/all_product.php?category=<?php echo $category['cat_id']; ?>" class="category-card ripple-effect">
                        <div class="category-icon">
                            <i class="fa <?php echo $icon; ?>"></i>
                        </div>
                        <h3 class="category-name"><?php echo htmlspecialchars($category['cat_name']); ?></h3>
                        <div class="category-arrow">
                            <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Featured Products Section -->
        <?php if($featured_products && count($featured_products) > 0): ?>
        <section class="featured-products-section fade-in-section">
            <div class="featured-container">
                <h2 class="section-title">Featured Products</h2>
                <p class="section-subtitle">Discover our top picks just for you</p>
                <div class="products-grid">
                    <?php foreach($featured_products as $product): ?>
                    <div class="product-card-home ripple-effect">
                        <a href="view/single_product.php?id=<?php echo $product['product_id']; ?>" class="product-link">
                            <?php if (!empty($product['product_image'])): ?>
                                <div class="product-image-wrapper">
                                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>"
                                         alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                         class="product-img">
                                </div>
                            <?php else: ?>
                                <div class="product-image-placeholder-home">
                                    <i class="fa fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-details">
                                <div class="product-category-badge">
                                    <?php echo htmlspecialchars($product['cat_name']); ?>
                                </div>
                                <h3 class="product-title-home"><?php echo htmlspecialchars($product['product_title']); ?></h3>
                                <div class="product-brand-home">
                                    <i class="fa fa-bookmark"></i> <?php echo htmlspecialchars($product['brand_name']); ?>
                                </div>
                                <div class="product-price-home">
                                    $<?php echo number_format($product['product_price'], 2); ?>
                                </div>
                            </div>
                        </a>
                        <button class="btn-add-cart-home ripple-btn" onclick="event.preventDefault(); alert('Add to cart functionality coming soon!');">
                            <i class="fa fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="view-all-container">
                    <a href="view/all_product.php" class="btn-view-all ripple-btn">
                        View All Products <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Features Section -->
        <section class="features-section fade-in-section">
            <div class="features-container">
                <h2 class="section-title">Why choose our platform?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-shield-alt"></i>
                        </div>
                        <h3>Secure Shopping</h3>
                        <p>Your purchases are protected with our money-back guarantee</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-truck"></i>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Get your products delivered quickly and efficiently</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-star"></i>
                        </div>
                        <h3>Quality Products</h3>
                        <p>Only the best products from trusted brands</p>
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
                <h2>Ready to get started?</h2>
                <p>Join thousands of satisfied customers today</p>
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
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Text carousel that changes every few seconds
        const carouselTexts = [
            { text: 'amazing product', color: '#679267' },
            { text: 'fashion inspiration', color: '#ff6b6b' },
            { text: 'tech gadget', color: '#3b82f6' },
            { text: 'home décor idea', color: '#8b5cf6' },
            { text: 'lifestyle upgrade', color: '#f59e0b' }
        ];

        let currentIndex = 0;
        const carouselTextElement = document.getElementById('carouselText');

        if (carouselTextElement) {
            function changeCarouselText() {
                // Fade out
                carouselTextElement.style.opacity = '0';

                setTimeout(() => {
                    currentIndex = (currentIndex + 1) % carouselTexts.length;
                    carouselTextElement.textContent = carouselTexts[currentIndex].text;
                    carouselTextElement.style.color = carouselTexts[currentIndex].color;

                    // Fade in
                    carouselTextElement.style.opacity = '1';
                }, 300);
            }

            // Change text every 3 seconds
            setInterval(changeCarouselText, 3000);
        }

        // ===== SCROLL-TRIGGERED FADE-IN ANIMATIONS =====
        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        document.querySelectorAll('.fade-in-section').forEach(section => {
            fadeInObserver.observe(section);
        });

        // ===== COUNTER ANIMATION =====
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

        // ===== PARALLAX SCROLL EFFECT =====
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

        // ===== RIPPLE EFFECT ON CLICK =====
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

        // ===== REDUCED MOTION SUPPORT =====
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (prefersReducedMotion.matches) {
            document.documentElement.style.setProperty('--animation-duration', '0.01ms');
        }

        // ===== CART COUNT LOADER =====
        // Load cart count when page loads
        fetch('actions/get_cart_count_action.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(element => {
                        element.textContent = data.cart_count;
                    });
                }
            })
            .catch(error => console.error('Error loading cart count:', error));
    </script>
</body>
</html>