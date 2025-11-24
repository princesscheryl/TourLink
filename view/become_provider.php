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
    <script src="../js/dark-mode.js"></script>
    <script src="../js/translator.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
        }

        /* Navigation */
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
            font-size: 1.8rem;
            font-weight: 800;
            color: #2d6a4f;
            text-decoration: none;
        }

        .logo-dot {
            color: #ffd700;
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
            background: #f0f7f4;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 80px 40px;
            position: relative;
            overflow: hidden;
        }


        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content h1 .highlight {
            color: #ffd700;
        }

        .hero-content p {
            font-size: 1.3rem;
            opacity: 0.95;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .stats-badge {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Registration Card */
        .register-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 3px solid #ffd700;
        }

        .register-card h3 {
            color: #1a1a1a;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
            color: #333;
        }

        .benefit-item i {
            color: #2d6a4f;
            font-size: 1.2rem;
            margin-top: 3px;
        }

        .btn-register {
            width: 100%;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 16px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 106, 79, 0.3);
        }

        .register-note {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        .register-note a {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 600;
        }

        /* Benefits Section */
        .benefits-section {
            padding: 80px 40px;
            background: white;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 60px;
            color: #1a1a1a;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .benefit-card {
            text-align: center;
            padding: 30px;
        }

        .benefit-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: white;
            font-size: 2rem;
        }

        .benefit-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .benefit-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Features Section */
        .features-section {
            padding: 80px 40px;
            background: #f8f9fa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 40px;
        }

        .feature-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 4px solid #2d6a4f;
        }

        .feature-box h4 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feature-box i {
            color: #2d6a4f;
        }

        .feature-box p {
            color: #666;
            line-height: 1.6;
        }

        .feature-box a {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 600;
        }

        /* Statistics Section */
        .stats-section {
            padding: 80px 40px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 60px;
            max-width: 1200px;
            margin: 60px auto 0;
        }

        .stat-box h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: #ffd700;
        }

        .stat-box p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Testimonials Section */
        .testimonials-section {
            padding: 80px 40px;
            background: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
        }

        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 2px solid #f0f0f0;
        }

        .testimonial-card:hover {
            border-color: #2d6a4f;
        }

        .testimonial-text {
            color: #333;
            font-style: italic;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .author-info h5 {
            font-weight: 600;
            margin-bottom: 3px;
            color: #1a1a1a;
        }

        .author-info p {
            font-size: 0.9rem;
            color: #666;
        }

        /* FAQ Section */
        .faq-section {
            padding: 80px 40px;
            background: #f8f9fa;
        }

        .faq-list {
            max-width: 900px;
            margin: 40px auto 0;
        }

        .faq-item {
            background: white;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .faq-question {
            padding: 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #1a1a1a;
            background: white;
            transition: all 0.3s;
        }

        .faq-question:hover {
            background: #f8f9fa;
        }

        .faq-question i {
            color: #2d6a4f;
            transition: transform 0.3s;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s;
            padding: 0 25px;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
            padding: 0 25px 25px;
        }

        .faq-answer p {
            color: #666;
            line-height: 1.7;
        }

        /* CTA Section */
        .cta-section {
            padding: 80px 40px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 40px;
        }

        .btn-cta {
            background: #ffd700;
            color: #1b4332;
            padding: 18px 50px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
            color: #1b4332;
        }

        /* Footer */
        footer {
            background: #1a1a1a;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
            }

            .benefits-grid,
            .testimonials-grid {
                grid-template-columns: 1fr 1fr;
            }

            .features-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .benefits-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 2rem;
            }

            .hero {
                padding: 60px 20px;
            }

            .benefits-section,
            .features-section,
            .stats-section,
            .testimonials-section,
            .faq-section,
            .cta-section {
                padding: 60px 20px;
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
        }

        @media (max-width: 480px) {
            .language-selector {
                min-width: 70px;
                font-size: 0.75rem;
                padding: 5px 6px;
            }

            .theme-toggle-btn {
                width: 32px;
                height: 32px;
            }
        }

        /* Dark Mode Styles */
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

        [data-theme="dark"] .theme-toggle-btn:hover {
            background: #4d4d4d !important;
        }
    </style>
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
            <h2 class="section-title">What providers like you say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"I was able to list within 15 minutes, and no more than two hours later, I had my first booking!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">KA</div>
                        <div class="author-info">
                            <h5>Kwame Asante</h5>
                            <p>Accra-based tour guide</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"TourLink is the most straightforward platform to work with. Everything is clear. It's easy. And it frees us up to focus on the guest experience."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AM</div>
                        <div class="author-info">
                            <h5>Ama Mensah</h5>
                            <p>Cultural Experience Provider</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"After joining TourLink and setting up the listing, my bookings went up significantly and bookings were coming in five to six months in advance."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">FB</div>
                        <div class="author-info">
                            <h5>Fiifi Boateng</h5>
                            <p>Cape Coast Adventure Tours</p>
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

    <script>
        // FAQ Toggle
        function toggleFAQ(element) {
            const faqItem = element.parentElement;
            const isActive = faqItem.classList.contains('active');

            // Close all FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });

            // Open clicked FAQ if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
            }
        }
    </script>
</body>
</html>
