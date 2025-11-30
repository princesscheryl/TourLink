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
    <link href="../css/about.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/accessibility.js"></script>
    <script src="../js/about.js"></script>
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
                    <div class="member-avatar">PAD</div>
                    <div class="member-name">Princess Asiedu-Donkor</div>
                    <div class="member-role">Chief Executive Officer</div>
                    <p class="member-bio">With a background in Management Information Systems, she combines business insight with technical understanding to ensure that TourLink supports inclusive economic growth across Ghana’s tourism sector.</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">DO</div>
                    <div class="member-name">David Ofori</div>
                    <div class="member-role">Chief Technology Officer</div>
                    <p class="member-bio">Expert in digital platforms and marketplace solutions. Leads our technical innovation to deliver seamless user experiences.</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">KA</div>
                    <div class="member-name">Kelly Asante</div>
                    <div class="member-role">Chief Operations Officer</div>
                    <p class="member-bio">Ensures quality standards across all service providers. Manages partnerships and maintains operational excellence.</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">RB</div>
                    <div class="member-name">Rima Baitie</div>
                    <div class="member-role">Chief Financial Officer</div>
                    <p class="member-bio">Oversees company budgeting, cost management, and grant reporting for funding bodies such as the African Digital Fund.</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar">SB</div>
                    <div class="member-name">Sean Boateng</div>
                    <div class="member-role">Chief Marketing and Partnerships Officer</div>
                    <p class="member-bio">Dedicated to ensuring exceptional experiences for both travelers and service providers through outstanding support.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
