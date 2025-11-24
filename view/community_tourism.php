<?php
require_once '../settings/core.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Tourism - Village Experiences | TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
        }

        .page-header {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=1600') center/cover;
            opacity: 0.15;
        }

        .header-content {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(212, 160, 23, 0.2);
            border: 1px solid #d4a017;
            color: #d4a017;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            line-height: 1.7;
        }

        .container-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Impact Stats Bar */
        .impact-bar {
            background: white;
            margin-top: -50px;
            position: relative;
            z-index: 10;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 32px;
        }

        .impact-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .impact-stat {
            text-align: center;
            padding: 16px;
            border-right: 1px solid #e5e7eb;
        }

        .impact-stat:last-child {
            border-right: none;
        }

        .impact-stat i {
            font-size: 32px;
            color: #1b4332;
            margin-bottom: 12px;
        }

        .impact-stat h3 {
            font-size: 28px;
            font-weight: 800;
            color: #1b4332;
            margin-bottom: 4px;
        }

        .impact-stat p {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }

        /* Why Community Tourism */
        .why-section {
            padding: 80px 0;
            background: white;
        }

        .why-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .why-content h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 20px;
        }

        .why-content p {
            font-size: 15px;
            color: #4b5563;
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .benefits-list {
            list-style: none;
        }

        .benefits-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .benefits-list li i {
            color: #d4a017;
            font-size: 18px;
            margin-top: 2px;
        }

        .benefits-list li span {
            font-size: 14px;
            color: #374151;
        }

        .why-image {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .why-image-card {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            border-radius: 16px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .why-image-card:first-child {
            grid-row: span 2;
            height: 100%;
        }

        .why-image-card i {
            font-size: 48px;
            color: rgba(255,255,255,0.2);
        }

        .why-image-card span {
            position: absolute;
            bottom: 16px;
            left: 16px;
            color: white;
            font-size: 13px;
            font-weight: 600;
        }

        /* Experiences Section */
        .experiences-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .section-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .section-header p {
            font-size: 16px;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }

        .experiences-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .experience-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            transition: all 0.3s;
        }

        .experience-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }

        .experience-image {
            height: 200px;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .experience-image i {
            font-size: 56px;
            color: rgba(255,255,255,0.2);
        }

        .experience-tag {
            position: absolute;
            top: 16px;
            left: 16px;
            background: #d4a017;
            color: white;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .experience-duration {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
        }

        .experience-content {
            padding: 24px;
        }

        .experience-content h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .experience-location {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .experience-content p {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .experience-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .experience-price {
            font-size: 18px;
            font-weight: 700;
            color: #1b4332;
        }

        .experience-price span {
            font-size: 12px;
            font-weight: 400;
            color: #6b7280;
        }

        .btn-book {
            background: #1b4332;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-book:hover {
            background: #143728;
            color: white;
        }

        /* Community Partners */
        .partners-section {
            padding: 80px 0;
            background: white;
        }

        .partners-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .partner-card {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 28px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .partner-card:hover {
            border-color: #1b4332;
            background: white;
        }

        .partner-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .partner-avatar i {
            font-size: 32px;
            color: rgba(255,255,255,0.8);
        }

        .partner-card h4 {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .partner-card .location {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .partner-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .partner-stats span {
            font-size: 11px;
            color: #6b7280;
        }

        .partner-stats span strong {
            color: #1b4332;
        }

        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            text-align: center;
            color: white;
        }

        .cta-section h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .cta-section p {
            font-size: 16px;
            opacity: 0.9;
            max-width: 500px;
            margin: 0 auto 32px;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta-primary {
            background: #d4a017;
            color: #1b4332;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-cta-primary:hover {
            background: #b8860b;
            color: white;
            transform: translateY(-2px);
        }

        .btn-cta-secondary {
            background: transparent;
            color: white;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid rgba(255,255,255,0.3);
            transition: all 0.3s;
        }

        .btn-cta-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: white;
            color: white;
        }

        @media (max-width: 1024px) {
            .impact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .experiences-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .partners-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            .why-grid {
                grid-template-columns: 1fr;
            }
            .why-image {
                display: none;
            }
            .impact-grid,
            .experiences-grid,
            .partners-grid {
                grid-template-columns: 1fr;
            }
            .impact-stat {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }
            .impact-stat:last-child {
                border-bottom: none;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="page-header">
        <div class="header-content">
            <span class="header-badge">
                <i class="fas fa-hands-helping"></i>
                Responsible Tourism
            </span>
            <h1>Community Tourism & Village Experiences</h1>
            <p>Immerse yourself in authentic Ghanaian culture while directly supporting local communities. Every visit creates lasting economic impact for rural families.</p>
        </div>
    </div>

    <div class="container-main">
        <div class="impact-bar">
            <div class="impact-grid">
                <div class="impact-stat">
                    <i class="fas fa-users"></i>
                    <h3>55+</h3>
                    <p>Families Supported</p>
                </div>
                <div class="impact-stat">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>12</h3>
                    <p>Partner Communities</p>
                </div>
                <div class="impact-stat">
                    <i class="fas fa-hand-holding-usd"></i>
                    <h3>85%</h3>
                    <p>Revenue to Communities</p>
                </div>
                <div class="impact-stat">
                    <i class="fas fa-heart"></i>
                    <h3>2,500+</h3>
                    <p>Tourists Impacted</p>
                </div>
            </div>
        </div>
    </div>

    <section class="why-section">
        <div class="container-main">
            <div class="why-grid">
                <div class="why-content">
                    <h2>Why Choose Community Tourism?</h2>
                    <p>Community-based tourism puts local people at the heart of the travel experience. Unlike conventional tourism, your money goes directly to the communities you visit, creating sustainable livelihoods and preserving cultural heritage.</p>
                    <ul class="benefits-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Direct Impact:</strong> 85% of your payment goes directly to community members</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Authentic Experiences:</strong> Learn from local experts and participate in real traditions</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Cultural Preservation:</strong> Help maintain traditional crafts, languages, and customs</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Small Groups:</strong> Intimate experiences with meaningful connections</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Sustainable Development:</strong> Support education, healthcare, and infrastructure</span>
                        </li>
                    </ul>
                </div>
                <div class="why-image">
                    <div class="why-image-card">
                        <i class="fas fa-palette"></i>
                        <span>Kente Weaving</span>
                    </div>
                    <div class="why-image-card">
                        <i class="fas fa-utensils"></i>
                        <span>Local Cuisine</span>
                    </div>
                    <div class="why-image-card">
                        <i class="fas fa-drum"></i>
                        <span>Traditional Music</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="experiences-section">
        <div class="container-main">
            <div class="section-header">
                <h2>Featured Village Experiences</h2>
                <p>Curated experiences that connect you with Ghana's rich cultural heritage</p>
            </div>

            <div class="experiences-grid">
                <div class="experience-card">
                    <div class="experience-image">
                        <i class="fas fa-palette"></i>
                        <span class="experience-tag">Craft Village</span>
                        <span class="experience-duration">Full Day</span>
                    </div>
                    <div class="experience-content">
                        <h3>Kente Weaving Masterclass</h3>
                        <p class="experience-location">
                            <i class="fas fa-map-marker-alt"></i>
                            Bonwire, Ashanti Region
                        </p>
                        <p>Learn the ancient art of Kente weaving from master craftsmen. Create your own strip to take home as a meaningful souvenir.</p>
                        <div class="experience-footer">
                            <span class="experience-price">GH&#8373; 250 <span>/ person</span></span>
                            <a href="all_services.php?region=Ashanti" class="btn-book">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="experience-card">
                    <div class="experience-image">
                        <i class="fas fa-utensils"></i>
                        <span class="experience-tag">Culinary</span>
                        <span class="experience-duration">Half Day</span>
                    </div>
                    <div class="experience-content">
                        <h3>Farm-to-Table Cooking</h3>
                        <p class="experience-location">
                            <i class="fas fa-map-marker-alt"></i>
                            Elmina, Central Region
                        </p>
                        <p>Visit local farms, harvest fresh ingredients, and learn to prepare traditional Ghanaian dishes with local mothers.</p>
                        <div class="experience-footer">
                            <span class="experience-price">GH&#8373; 180 <span>/ person</span></span>
                            <a href="all_services.php?region=Central" class="btn-book">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="experience-card">
                    <div class="experience-image">
                        <i class="fas fa-drum"></i>
                        <span class="experience-tag">Cultural</span>
                        <span class="experience-duration">3 Hours</span>
                    </div>
                    <div class="experience-content">
                        <h3>Drumming & Dance Workshop</h3>
                        <p class="experience-location">
                            <i class="fas fa-map-marker-alt"></i>
                            Kokrobite, Greater Accra
                        </p>
                        <p>Learn traditional Ga rhythms and dance movements from village elders at the famous Academy of African Music.</p>
                        <div class="experience-footer">
                            <span class="experience-price">GH&#8373; 150 <span>/ person</span></span>
                            <a href="all_services.php?region=Greater+Accra" class="btn-book">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="experience-card">
                    <div class="experience-image">
                        <i class="fas fa-fish"></i>
                        <span class="experience-tag">Eco-Tourism</span>
                        <span class="experience-duration">Full Day</span>
                    </div>
                    <div class="experience-content">
                        <h3>Fishing Village Experience</h3>
                        <p class="experience-location">
                            <i class="fas fa-map-marker-alt"></i>
                            Ada Foah, Greater Accra
                        </p>
                        <p>Join local fishermen on the Volta River, learn traditional net casting, and enjoy the freshest catch prepared by village women.</p>
                        <div class="experience-footer">
                            <span class="experience-price">GH&#8373; 200 <span>/ person</span></span>
                            <a href="all_services.php?region=Greater+Accra" class="btn-book">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="experience-card">
                    <div class="experience-image">
                        <i class="fas fa-mortar-pestle"></i>
                        <span class="experience-tag">Heritage</span>
                        <span class="experience-duration">Half Day</span>
                    </div>
                    <div class="experience-content">
                        <h3>Shea Butter Making</h3>
                        <p class="experience-location">
                            <i class="fas fa-map-marker-alt"></i>
                            Tamale, Northern Region
                        </p>
                        <p>Visit women's cooperatives and learn the traditional process of making shea butter, from nut harvesting to final product.</p>
                        <div class="experience-footer">
                            <span class="experience-price">GH&#8373; 160 <span>/ person</span></span>
                            <a href="all_services.php?region=Northern" class="btn-book">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="experience-card">
                    <div class="experience-image">
                        <i class="fas fa-home"></i>
                        <span class="experience-tag">Immersive</span>
                        <span class="experience-duration">2 Days</span>
                    </div>
                    <div class="experience-content">
                        <h3>Village Homestay</h3>
                        <p class="experience-location">
                            <i class="fas fa-map-marker-alt"></i>
                            Larabanga, Northern Region
                        </p>
                        <p>Stay with a local family, participate in daily activities, and experience rural Ghanaian life from the inside.</p>
                        <div class="experience-footer">
                            <span class="experience-price">GH&#8373; 350 <span>/ person</span></span>
                            <a href="all_services.php?region=Northern" class="btn-book">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="partners-section">
        <div class="container-main">
            <div class="section-header">
                <h2>Our Community Partners</h2>
                <p>Meet the local leaders making community tourism possible</p>
            </div>

            <div class="partners-grid">
                <div class="partner-card">
                    <div class="partner-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Nana Yaw Asare</h4>
                    <p class="location">Bonwire Kente Village</p>
                    <div class="partner-stats">
                        <span><strong>45</strong> tours hosted</span>
                        <span><strong>4.9</strong> rating</span>
                    </div>
                </div>

                <div class="partner-card">
                    <div class="partner-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Mama Efua</h4>
                    <p class="location">Elmina Cooking School</p>
                    <div class="partner-stats">
                        <span><strong>120</strong> tours hosted</span>
                        <span><strong>5.0</strong> rating</span>
                    </div>
                </div>

                <div class="partner-card">
                    <div class="partner-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Kofi Mensah</h4>
                    <p class="location">Kokrobite Drum Academy</p>
                    <div class="partner-stats">
                        <span><strong>200+</strong> tours hosted</span>
                        <span><strong>4.8</strong> rating</span>
                    </div>
                </div>

                <div class="partner-card">
                    <div class="partner-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4>Alhassan Ibrahim</h4>
                    <p class="location">Larabanga Homestays</p>
                    <div class="partner-stats">
                        <span><strong>80</strong> tours hosted</span>
                        <span><strong>4.9</strong> rating</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container-main">
            <h2>Ready to Make a Difference?</h2>
            <p>Book a community experience and create lasting memories while supporting local families.</p>
            <div class="cta-buttons">
                <a href="all_services.php" class="btn-cta-primary">
                    <i class="fas fa-search"></i> Browse Experiences
                </a>
                <a href="become_provider.php" class="btn-cta-secondary">
                    <i class="fas fa-handshake"></i> Become a Partner
                </a>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
