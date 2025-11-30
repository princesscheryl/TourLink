<?php
require_once '../settings/core.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Provider - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <link href="../css/become_provider.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/translator.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="../index_tourlink.php" class="logo">TourLink<span class="logo-dot">.</span></a>
            <div class="nav-actions">
                <!-- Language Switcher -->
                <select id="languageSelector" class="language-selector" aria-label="Select language" onchange="changeLanguage(this.value)">
                    <option value="en">English</option>
                    <option value="fr">Français</option>
                    <option value="es">Español</option>
                    <option value="tw">Twi</option>
                    <option value="ga">Ga</option>
                </select>

                <!-- Theme Toggle -->
                <button onclick="toggleTheme()" class="theme-toggle-btn" id="publicThemeToggle" aria-label="Toggle dark mode">
                    <i class="fa fa-moon"></i>
                </button>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="../admin/provider_dashboard.php" class="btn-signin">Dashboard</a>
                <?php else: ?>
                    <a href="../login/login.php" class="btn-signin">Already a partner? Sign in</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>List <span class="highlight">anything</span> on TourLink</h1>
                <p>Join Ghana's leading tourism marketplace and reach thousands of travelers looking for authentic experiences across the country.</p>
                <div class="stats-badge">
                    <i class="fa fa-users"></i>
                    Join 500+ active providers already on TourLink
                </div>
            </div>

            <!-- Registration Card -->
            <div class="register-card">
                <h3>Register for free</h3>
                <div class="benefit-item">
                    <i class="fa fa-check-circle"></i>
                    <span><strong>Quick setup:</strong> List your services in minutes</span>
                </div>
                <div class="benefit-item">
                    <i class="fa fa-check-circle"></i>
                    <span><strong>Choose your terms:</strong> Set your own prices and availability</span>
                </div>
                <div class="benefit-item">
                    <i class="fa fa-check-circle"></i>
                    <span><strong>Secure payments:</strong> We handle payments and protect your earnings</span>
                </div>
                <button onclick="window.location.href='../login/register_provider.php'" class="btn-register">
                    Get started now <i class="fa fa-arrow-right"></i>
                </button>
                <div class="register-note">
                    Already started a registration? <a href="../login/login.php">Continue your registration</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <h2 class="section-title">Host worry-free. We've got your back</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fa fa-shield-alt"></i>
                    </div>
                    <h3>Your service, your rules</h3>
                    <p>Accept or decline bookings with Request to Book. Manage your guests' expectations by setting up clear service terms and policies.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <h3>Get to know your guests</h3>
                    <p>Communicate with your guests before accepting their booking with pre-booking messaging. Build relationships and ensure mutual understanding.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fa fa-lock"></i>
                    </div>
                    <h3>Stay protected</h3>
                    <p>Up to GHS 50,000 liability protection against claims from guests at no extra cost with TourLink Provider Protection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Take control of your business with TourLink</h2>
            <div class="features-grid">
                <div class="feature-box">
                    <h4><i class="fa fa-money-bill-wave"></i> Payments made easy</h4>
                    <p>We <a href="#">facilitate the payment process</a> for you, freeing up your time to grow your business and provide excellent service.</p>
                </div>
                <div class="feature-box">
                    <h4><i class="fa fa-clock"></i> Quick payouts</h4>
                    <p>Get payouts faster! We'll send your payments within 48 hours after guest checkout for completed bookings.</p>
                </div>
                <div class="feature-box">
                    <h4><i class="fa fa-chart-line"></i> Greater revenue security</h4>
                    <p>Whenever guests complete prepaid reservations for your service and pay online, you are guaranteed payment.</p>
                </div>
                <div class="feature-box">
                    <h4><i class="fa fa-wallet"></i> More control over your cash flow</h4>
                    <p>Choose your payout method and timing based on regional availability to better manage your business finances.</p>
                </div>
                <div class="feature-box">
                    <h4><i class="fa fa-briefcase"></i> One platform for all listings</h4>
                    <p>Save time managing multiple services with <a href="#">unified dashboard</a> and <a href="#">consolidated reporting</a>.</p>
                </div>
                <div class="feature-box">
                    <h4><i class="fa fa-check-double"></i> Reduced risk</h4>
                    <p>We help you stay compliant with regulatory changes and <a href="#">reduce the risk</a> of fraud and chargebacks.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title">Reach a unique customer base across Ghana</h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <h2>10,000+</h2>
                    <p>Active travelers searching for experiences monthly</p>
                </div>
                <div class="stat-box">
                    <h2>1 in 5</h2>
                    <p>Bookings in 2024 were from repeat customers</p>
                </div>
                <div class="stat-box">
                    <h2>GHS 2M+</h2>
                    <p>Paid out to providers in 2024</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="section-title">Success Stories from Our Providers</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Within 6 months of joining TourLink, I expanded from solo tours to hiring 3 additional guides. The platform connected me with tourists from all over the world who wanted authentic Accra experiences."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">KM</div>
                        <div class="author-info">
                            <h5>Kwame Mensah</h5>
                            <p>Accra Heritage Tours</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"TourLink connected me with international tourists who appreciate authentic Ghanaian crafts. My income tripled in the first year, and now I employ 5 other artisans from my community."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AO</div>
                        <div class="author-info">
                            <h5>Abena Osei</h5>
                            <p>Kumasi Traditional Crafts</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Started with one car, now managing a fleet of five vehicles serving tourists across Ghana. TourLink gave me the platform to scale my business beyond what I ever imagined."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">YB</div>
                        <div class="author-info">
                            <h5>Yaw Boateng</h5>
                            <p>SafeRide Ghana</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"TourLink enabled me to create employment opportunities for women in my community. We now offer cultural tours that showcase Ga traditions, and our bookings are consistently full."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">ED</div>
                        <div class="author-info">
                            <h5>Efua Dadzie</h5>
                            <p>Women of Ghana Tours</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Your questions answered</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>How much does it cost to list on TourLink?</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>There's a one-time registration fee of GHS 20. After that, we only charge a small commission (15%) on confirmed bookings. No monthly fees, no hidden costs.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>When will my service go live?</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>After registration and payment, your account will be reviewed and verified within 24-48 hours. Once verified, you can immediately start listing your services.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>How do I receive payments?</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Payments are processed automatically and sent to your bank account or mobile money within 48 hours after the service is completed and confirmed.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        <span>Can I manage bookings and availability?</span>
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! Your provider dashboard gives you full control over your calendar, pricing, service availability, and booking management.</p>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <p style="color: #666; margin-bottom: 20px;">Still have questions? Find answers to all your questions on our <a href="#" style="color: #2d6a4f; font-weight: 600; text-decoration: none;">FAQ</a></p>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Sign up and start welcoming guests today!</h2>
            <p>Join hundreds of providers who are already growing their business with TourLink</p>
            <a href="../login/register_provider.php" class="btn-cta">
                Get started now <i class="fa fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 TourLink. All rights reserved.</p>
    </footer>

    <script src="../js/become_provider.js"></script>
</body>
</html>
