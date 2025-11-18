<?php
require_once '../settings/core.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/translator.js"></script>
    <script src="../js/accessibility.js"></script>
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
        }

        .nav-link:hover {
            color: #2d6a4f;
        }

        .btn-nav {
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-nav-primary {
            background: #2d6a4f;
            color: white;
        }

        .btn-nav-primary:hover {
            background: #1b4332;
        }

        /* Navigation Menu */
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

        /* Responsive */
        @media (max-width: 1024px) {
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

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 140px 0 80px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Main Content */
        .main-container {
            max-width: 1200px;
            margin: -40px auto 80px;
            padding: 0 30px;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            padding: 60px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .content-card h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #1b4332;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px solid #2d6a4f;
        }

        .content-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d6a4f;
            margin: 32px 0 16px;
        }

        .content-card p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 20px;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
            margin-top: 40px;
        }

        .team-member {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(45, 106, 79, 0.2);
        }

        .member-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
        }

        .member-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .member-role {
            font-size: 0.95rem;
            color: #2d6a4f;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .member-bio {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.6;
        }

        /* Footer */
        .simple-footer {
            background: #1a1a1a;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <a href="../index_tourlink.php" class="logo" aria-label="TourLink Home">TourLink<span class="logo-dot">.</span></a>
            <ul class="nav-menu" id="navMenu" role="menubar">
                <li><a href="../index_tourlink.php" data-i18n="nav.home">Home</a></li>
                <li><a href="all_services.php" data-i18n="nav.destinations">Browse Services</a></li>
                <li><a href="about.php" class="active" data-i18n="nav.about">About</a></li>
                <li><a href="contact.php" data-i18n="nav.contact">Contact</a></li>
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
                            <a href="profile_settings.php">
                                <i class="fa fa-user-circle"></i>
                                Profile Settings
                            </a>
                            <a href="my_favorites.php">
                                <i class="fas fa-heart"></i>
                                <span data-i18n="favorites.my_favorites">My Favorites</span>
                            </a>
                            <?php if($_SESSION['user_type'] === 'provider'): ?>
                                <div class="dropdown-divider"></div>
                                <a href="../admin/provider_dashboard.php">
                                    <i class="fa fa-th-large"></i>
                                    Dashboard
                                </a>
                                <a href="../admin/manage_services.php">
                                    <i class="fa fa-briefcase"></i>
                                    My Services
                                </a>
                                <a href="../admin/bookings.php">
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
                            <a href="../login/logout.php">
                                <i class="fa fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../login/login.php" class="btn-signin" data-i18n="nav.sign_in">Sign In</a>
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

    <!-- Page Header -->
    <div class="page-header">
        <h1>About TourLink</h1>
        <p>Connecting travelers with unforgettable experiences across Ghana</p>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <div class="content-card">
            <h2>Our Story</h2>
            <p>Founded in 2025, TourLink Technologies Ltd. is revolutionizing the tourism industry in Ghana by creating a seamless digital marketplace that connects travelers with verified local tour providers. Our platform empowers both tourists seeking authentic Ghanaian experiences and service providers looking to expand their reach.</p>

            <h3>Our Mission</h3>
            <p>To make Ghana's rich cultural heritage, natural beauty, and adventure opportunities accessible to travelers worldwide while supporting local tourism businesses through innovative technology solutions.</p>

            <h3>What We Do</h3>
            <p>TourLink serves as a comprehensive tourism marketplace where:</p>
            <ul style="font-size: 1.05rem; line-height: 1.8; color: #555; margin-left: 30px; margin-bottom: 20px;">
                <li>Travelers can discover, compare, and book verified tourism services across Ghana</li>
                <li>Local service providers can showcase their offerings and reach a broader audience</li>
                <li>Secure payment processing ensures safe and transparent transactions</li>
                <li>Real-time reviews and ratings build trust within our community</li>
            </ul>

            <h3>Our Growth</h3>
            <p>Starting with our core regions of Greater Accra, Central, Ashanti, and Northern Ghana, we are rapidly expanding our network of verified service providers. Our platform currently features tours, cultural experiences, adventure activities, and accommodation options that showcase the best of Ghanaian hospitality.</p>
        </div>

        <div class="content-card">
            <h2>Meet Our Leadership Team</h2>
            <p style="text-align: center; margin-bottom: 40px;">Passionate professionals dedicated to transforming Ghana's tourism landscape</p>

            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar">KA</div>
                    <div class="member-name">Kwame Asante</div>
                    <div class="member-role">Chief Executive Officer</div>
                    <p class="member-bio">Tourism industry veteran with 15+ years of experience. Passionate about leveraging technology to promote Ghana's tourism sector globally.</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">AM</div>
                    <div class="member-name">Ama Mensah</div>
                    <div class="member-role">Chief Technology Officer</div>
                    <p class="member-bio">Expert in digital platforms and marketplace solutions. Leads our technical innovation to deliver seamless user experiences.</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">KO</div>
                    <div class="member-name">Kofi Owusu</div>
                    <div class="member-role">Head of Operations</div>
                    <p class="member-bio">Ensures quality standards across all service providers. Manages partnerships and maintains operational excellence.</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">EA</div>
                    <div class="member-name">Efua Agyeman</div>
                    <div class="member-role">Marketing Director</div>
                    <p class="member-bio">Creative strategist driving TourLink's brand presence. Connects with travelers and showcases Ghana's tourism potential.</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">YB</div>
                    <div class="member-name">Yaw Boateng</div>
                    <div class="member-role">Customer Success Manager</div>
                    <p class="member-bio">Dedicated to ensuring exceptional experiences for both travelers and service providers through outstanding support.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile Menu Toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Profile Dropdown Toggle
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
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
    </script>
</body>
</html>
