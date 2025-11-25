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

        /* Why Visit Ghana Section */
        .why-ghana-section {
            padding: 80px 0;
            background: #f8f9fa;
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
            <div style="max-width: 900px; margin: 0 auto;">
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
            </div>
        </div>
    </section>

    <!-- Why Visit Ghana Section -->
    <section class="why-ghana-section">
        <div class="container-main">
            <div class="section-header" style="text-align: center; max-width: 800px; margin: 0 auto 60px;">
                <span class="header-badge" style="background: rgba(212, 160, 23, 0.1); color: #d4a017; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-star"></i> Discover Ghana
                </span>
                <h2 style="font-size: 2.5rem; font-weight: 800; margin: 20px 0; color: #1b4332;">Why Visit Ghana</h2>
                <p style="font-size: 1.1rem; color: #666; line-height: 1.8;">Experience the heartbeat of West Africa - where ancient kingdoms meet modern vibrancy, golden beaches stretch endlessly, and every corner tells a story of resilience, culture, and warm hospitality.</p>
            </div>

            <!-- History & Culture -->
            <div style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); border-radius: 24px; padding: 60px 48px; margin-bottom: 48px; position: relative; overflow: hidden;">
                <div style="position: relative; z-index: 1;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center;">
                        <div>
                            <div style="display: inline-block; background: rgba(212, 160, 23, 0.2); border: 2px solid #d4a017; padding: 12px 24px; border-radius: 30px; margin-bottom: 24px;">
                                <span style="color: #d4a017; font-weight: 700; font-size: 16px; letter-spacing: 1px;">🇬🇭 GATEWAY TO AFRICA</span>
                            </div>
                            <h3 style="font-size: 2.2rem; font-weight: 800; color: white; margin-bottom: 20px; line-height: 1.2;">A Nation of Firsts & Pioneers</h3>
                            <p style="color: rgba(255,255,255,0.9); font-size: 1.05rem; line-height: 1.8; margin-bottom: 24px;">
                                Ghana was the <strong>first sub-Saharan African nation to gain independence</strong> in 1957, led by the visionary Kwame Nkrumah. This spirit of liberation continues to define Ghanaian identity - proud, welcoming, and forward-thinking.
                            </p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 32px;">
                                <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; border-left: 4px solid #d4a017;">
                                    <div style="font-size: 2rem; font-weight: 800; color: #d4a017;">1957</div>
                                    <div style="color: white; font-size: 0.95rem; margin-top: 4px;">Independence Year</div>
                                </div>
                                <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; border-left: 4px solid #d4a017;">
                                    <div style="font-size: 2rem; font-weight: 800; color: #d4a017;">70+</div>
                                    <div style="color: white; font-size: 0.95rem; margin-top: 4px;">Ethnic Groups</div>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <!-- Replace with real image: assets/images/ghana/independence-square.jpg -->
                            <img src="../assets/images/ghana/independence-square.jpg" alt="Independence Square Accra"
                                 style="width: 100%; height: 400px; object-fit: cover; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);"
                                 onerror="this.style.background='linear-gradient(135deg, rgba(212,160,23,0.3), rgba(27,67,50,0.3))'; this.alt='Independence Square - Accra, Ghana';">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cultural Heritage Grid -->
            <div style="margin-bottom: 60px;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #1b4332; margin-bottom: 32px; text-align: center;">
                    <i class="fas fa-heart" style="color: #d4a017;"></i> Rich Cultural Tapestry
                </h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
                    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s;">
                        <!-- Replace with real image: assets/images/ghana/kente-weaving.jpg -->
                        <img src="../assets/images/ghana/kente-weaving.jpg" alt="Kente Cloth Weaving"
                             style="width: 100%; height: 220px; object-fit: cover;"
                             onerror="this.style.background='linear-gradient(135deg, #d4a017, #f4c430)'; this.alt='Kente Weaving - Bonwire, Ghana';">
                        <div style="padding: 24px;">
                            <h4 style="font-size: 1.3rem; font-weight: 700; color: #1b4332; margin-bottom: 12px;">Kente Cloth</h4>
                            <p style="color: #666; line-height: 1.7; font-size: 0.95rem;">Handwoven silk and cotton fabric native to the Akan people. Each pattern tells a story and carries deep symbolic meaning passed down through generations.</p>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s;">
                        <!-- Replace with real image: assets/images/ghana/adinkra-symbols.jpg -->
                        <img src="../assets/images/ghana/adinkra-symbols.jpg" alt="Adinkra Symbols"
                             style="width: 100%; height: 220px; object-fit: cover;"
                             onerror="this.style.background='linear-gradient(135deg, #2d6a4f, #1b4332)'; this.alt='Adinkra Symbols - Ghana';">
                        <div style="padding: 24px;">
                            <h4 style="font-size: 1.3rem; font-weight: 700; color: #1b4332; margin-bottom: 12px;">Adinkra Symbols</h4>
                            <p style="color: #666; line-height: 1.7; font-size: 0.95rem;">Visual symbols representing concepts and proverbs. Sankofa ("go back and fetch it") teaches us to learn from the past to build the future.</p>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.3s;">
                        <!-- Replace with real image: assets/images/ghana/drumming-dancing.jpg -->
                        <img src="../assets/images/ghana/drumming-dancing.jpg" alt="Traditional Drumming"
                             style="width: 100%; height: 220px; object-fit: cover;"
                             onerror="this.style.background='linear-gradient(135deg, #d4a017, #1b4332)'; this.alt='Traditional Drumming - Ghana';">
                        <div style="padding: 24px;">
                            <h4 style="font-size: 1.3rem; font-weight: 700; color: #1b4332; margin-bottom: 12px;">Music & Dance</h4>
                            <p style="color: #666; line-height: 1.7; font-size: 0.95rem;">From the rhythmic beats of the djembe to the energetic Azonto dance, Ghanaian music is the soul of West African culture and has influenced genres worldwide.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Must-Visit Destinations -->
            <div>
                <h3 style="font-size: 2rem; font-weight: 700; color: #1b4332; margin-bottom: 16px; text-align: center;">
                    <i class="fas fa-map-marked-alt" style="color: #d4a017;"></i> Must-Visit Destinations
                </h3>
                <p style="text-align: center; color: #666; margin-bottom: 40px; font-size: 1.05rem;">From UNESCO World Heritage Sites to pristine beaches and wildlife reserves</p>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px;">
                    <!-- Cape Coast Castle -->
                    <div style="position: relative; border-radius: 20px; overflow: hidden; height: 360px; box-shadow: 0 8px 32px rgba(0,0,0,0.12);">
                        <!-- Replace with real image: assets/images/ghana/cape-coast-castle.jpg -->
                        <img src="../assets/images/ghana/cape-coast-castle.jpg" alt="Cape Coast Castle"
                             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;"
                             onerror="this.style.background='linear-gradient(135deg, rgba(27,67,50,0.8), rgba(45,106,79,0.9))'; this.alt='Cape Coast Castle - Ghana';">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.85)); padding: 32px 28px;">
                            <span style="background: #d4a017; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">UNESCO Site</span>
                            <h4 style="color: white; font-size: 1.6rem; font-weight: 800; margin: 12px 0 8px;">Cape Coast Castle</h4>
                            <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem; line-height: 1.6;">A powerful testament to the transatlantic slave trade. Walk through the "Door of No Return" and honor the memory of millions who passed through these walls.</p>
                        </div>
                    </div>

                    <!-- Kakum National Park -->
                    <div style="position: relative; border-radius: 20px; overflow: hidden; height: 360px; box-shadow: 0 8px 32px rgba(0,0,0,0.12);">
                        <!-- Replace with real image: assets/images/ghana/kakum-canopy-walkway.jpg -->
                        <img src="../assets/images/ghana/kakum-canopy-walkway.jpg" alt="Kakum Canopy Walkway"
                             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;"
                             onerror="this.style.background='linear-gradient(135deg, rgba(45,106,79,0.8), rgba(27,67,50,0.9))'; this.alt='Kakum Canopy Walkway - Ghana';">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.85)); padding: 32px 28px;">
                            <span style="background: #2d6a4f; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Nature</span>
                            <h4 style="color: white; font-size: 1.6rem; font-weight: 800; margin: 12px 0 8px;">Kakum National Park</h4>
                            <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem; line-height: 1.6;">Walk 40 meters above the rainforest floor on suspended canopy bridges. Spot rare birds, butterflies, and forest elephants in this pristine ecosystem.</p>
                        </div>
                    </div>

                    <!-- Mole National Park -->
                    <div style="position: relative; border-radius: 20px; overflow: hidden; height: 360px; box-shadow: 0 8px 32px rgba(0,0,0,0.12);">
                        <!-- Replace with real image: assets/images/ghana/mole-national-park.jpg -->
                        <img src="../assets/images/ghana/mole-national-park.jpg" alt="Mole National Park Elephants"
                             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;"
                             onerror="this.style.background='linear-gradient(135deg, rgba(212,160,23,0.7), rgba(244,196,48,0.8))'; this.alt='Mole National Park - Ghana';">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.85)); padding: 32px 28px;">
                            <span style="background: #d4a017; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Wildlife</span>
                            <h4 style="color: white; font-size: 1.6rem; font-weight: 800; margin: 12px 0 8px;">Mole National Park</h4>
                            <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem; line-height: 1.6;">Ghana's largest wildlife refuge. See elephants, antelopes, baboons, and over 300 bird species in their natural savanna habitat.</p>
                        </div>
                    </div>

                    <!-- Labadi Beach -->
                    <div style="position: relative; border-radius: 20px; overflow: hidden; height: 360px; box-shadow: 0 8px 32px rgba(0,0,0,0.12);">
                        <!-- Replace with real image: assets/images/ghana/labadi-beach.jpg -->
                        <img src="../assets/images/ghana/labadi-beach.jpg" alt="Labadi Beach Accra"
                             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;"
                             onerror="this.style.background='linear-gradient(135deg, rgba(45,106,79,0.7), rgba(212,160,23,0.6))'; this.alt='Labadi Beach - Accra, Ghana';">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.85)); padding: 32px 28px;">
                            <span style="background: #2d6a4f; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Beach</span>
                            <h4 style="color: white; font-size: 1.6rem; font-weight: 800; margin: 12px 0 8px;">Labadi Beach</h4>
                            <p style="color: rgba(255,255,255,0.9); font-size: 0.95rem; line-height: 1.6;">Feel the Atlantic breeze at Accra's most vibrant beach. Enjoy live music, fresh coconuts, and stunning sunsets while locals play beach football.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Why Choose Ghana Call-out -->
            <div style="background: linear-gradient(135deg, rgba(212,160,23,0.1), rgba(27,67,50,0.05)); border: 2px solid #d4a017; border-radius: 20px; padding: 48px; margin-top: 60px; text-align: center;">
                <h3 style="font-size: 2rem; font-weight: 800; color: #1b4332; margin-bottom: 20px;">Experience "Akwaaba" - The Spirit of Welcome</h3>
                <p style="font-size: 1.15rem; color: #2d6a4f; line-height: 1.9; max-width: 900px; margin: 0 auto 32px;">
                    Ghanaians are renowned for their warm hospitality. "Akwaaba" means "welcome" in Twi, and you'll hear it everywhere you go. From bustling markets in Accra to serene villages in the north, Ghanaians embrace visitors as family.
                </p>
                <div style="display: flex; justify-content: center; gap: 40px; margin-top: 32px; flex-wrap: wrap;">
                    <div>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #d4a017;">Safe</div>
                        <div style="color: #666; font-size: 1rem; margin-top: 4px;">Most stable democracy in West Africa</div>
                    </div>
                    <div>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #d4a017;">English</div>
                        <div style="color: #666; font-size: 1rem; margin-top: 4px;">Official language - easy communication</div>
                    </div>
                    <div>
                        <div style="font-size: 2.5rem; font-weight: 800; color: #d4a017;">Year-Round</div>
                        <div style="color: #666; font-size: 1rem; margin-top: 4px;">Tropical climate perfect for travel</div>
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
