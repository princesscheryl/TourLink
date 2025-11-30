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

        /* Dark Mode Styles */
        [data-theme="dark"] body {
            background: #1a1a1a !important;
        }

        [data-theme="dark"] .content-card {
            background: #2d2d2d !important;
        }

        [data-theme="dark"] .content-card h2 {
            color: #52b788 !important;
            border-bottom-color: #52b788 !important;
        }

        [data-theme="dark"] .content-card h3 {
            color: #74c69d !important;
        }

        [data-theme="dark"] .content-card p {
            color: #c0c0c0 !important;
        }

        [data-theme="dark"] .content-card ul {
            color: #c0c0c0 !important;
        }

        [data-theme="dark"] .content-card ul li {
            color: #c0c0c0 !important;
        }

        [data-theme="dark"] .team-member {
            background: #3d3d3d !important;
        }

        [data-theme="dark"] .member-name {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .member-role {
            color: #52b788 !important;
        }

        [data-theme="dark"] .member-bio {
            color: #b0b0b0 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <h1>About TourLink</h1>
        <p>Connecting travelers with unforgettable experiences across Ghana</p>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <div class="content-card">
            <h2>Our Story</h2>
            <p>Founded in 2025, TourLink Technologies Ltd. is revolutionizing the tourism industry in Ghana by creating a seamless digital marketplace that connects travelers with verified local tour providers. Our platform empowers both people seeking authentic Ghanaian experiences and service providers looking to expand their reach.</p>

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
                    <div class="member-name">Princess Asiedu-Donkor</div>
                    <div class="member-role">Chief Executive Officer</div>
                    <p class="member-bio">With a background in Management Information Systems, she combines business insight with technical understanding to ensure that TourLink supports inclusive economic growth across Ghana’s tourism sector.</p>

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
