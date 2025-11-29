<?php
/**
 * Modular Footer Component
 * Reusable footer for all pages
 */

// Determine the correct base path
$current_file = basename($_SERVER['PHP_SELF']);
$in_view_folder = (strpos($_SERVER['PHP_SELF'], '/view/') !== false);
$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$in_login_folder = (strpos($_SERVER['PHP_SELF'], '/login/') !== false);

// Set base path based on current location
if ($in_view_folder || $in_admin_folder || $in_login_folder) {
    $base_path = '../';
} else {
    $base_path = '';
}
?>

<footer class="footer" id="contact">
    <div class="footer-container">
        <div>
            <h4 data-i18n="app_name">TourLink</h4>
            <p data-i18n="footer.description">Your trusted partner for unforgettable travel experiences. We specialize in curated tours and personalized travel solutions around Ghana.</p>
            <div class="footer-certifications">
                <span class="cert-badge"><i class="fas fa-check-circle"></i> Licensed Tour Operator</span>
                <span class="cert-badge"><i class="fas fa-shield-alt"></i> Verified Platform</span>
            </div>
        </div>
        <div>
            <h4 data-i18n="footer.quick_links">Quick Links</h4>
            <ul>
                <li><a href="<?php echo $base_path; ?>view/about.php" data-i18n="footer.about_us">About Us</a></li>
                <li><a href="<?php echo $base_path; ?>view/festivals.php">Festival Calendar</a></li>
                <li><a href="<?php echo $base_path; ?>view/contact.php" data-i18n="footer.contact_us">Contact Us</a></li>
                <li><a href="#privacy" data-i18n="footer.privacy_policy">Privacy Policy</a></li>
                <li><a href="#terms" data-i18n="footer.terms_conditions">Terms & Conditions</a></li>
            </ul>
        </div>
        <div>
            <h4>Explore Ghana</h4>
            <ul>
                <li><a href="<?php echo $base_path; ?>view/all_services.php?region=Greater+Accra">Greater Accra</a></li>
                <li><a href="<?php echo $base_path; ?>view/all_services.php?region=Ashanti">Ashanti Region</a></li>
                <li><a href="<?php echo $base_path; ?>view/all_services.php?region=Central">Central Region</a></li>
                <li><a href="<?php echo $base_path; ?>view/all_services.php?region=Northern">Northern Region</a></li>
            </ul>
        </div>
        <div>
            <h4 data-i18n="footer.contact_info">Contact Info</h4>
            <p data-i18n="footer.location">Ashesi University, Berekuso<br>Ghana</p>
            <p data-i18n="footer.email">info@tourlink.com.gh</p>
            <p data-i18n="footer.phone">+233 50 123 4567</p>
            <div class="footer-social">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>
    <!-- Kente Pattern Decoration -->
    <div class="kente-pattern-top"></div>
    
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <div class="footer-copyright">
                <?php 
                // Include Adinkra symbols
                if (file_exists(__DIR__ . '/adinkra_symbols.php')) {
                    include __DIR__ . '/adinkra_symbols.php';
                }
                ?>
                <p>
                    <?php if (function_exists('get_adinkra_symbol')): ?>
                        <span class="footer-adinkra"><?php echo get_adinkra_symbol('akoma', '16px', '#d4a017'); ?></span>
                    <?php endif; ?>
                    &copy; 2025 <span data-i18n="app_name">TourLink</span>. <span data-i18n="footer.copyright">All rights reserved.</span>
                    <?php if (function_exists('get_adinkra_symbol')): ?>
                        <span class="footer-adinkra"><?php echo get_adinkra_symbol('gye_nyame', '16px', '#d4a017'); ?></span>
                    <?php endif; ?>
                </p>
                <p class="footer-tagline">
                    <span class="cultural-phrase">Medaase</span> - Proudly supporting Ghana's tourism industry and local communities
                </p>
            </div>
            <div class="footer-partners">
                <span class="partner-label">In Partnership With</span>
                <div class="partner-logos">
                    <div class="partner-logo-item">
                        <img src="<?php echo $base_path; ?>assets/images/partners/gta-logo.png" alt="Ghana Tourism Authority" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2240%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22rgba(255,255,255,0.6)%22 font-size=%2210%22 font-family=%22Arial%22%3EGTA%3C/text%3E%3C/svg%3E';">
                    </div>
                    <div class="partner-logo-item">
                        <img src="<?php echo $base_path; ?>assets/images/partners/ministry-tourism-logo.png" alt="Ministry of Tourism" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2240%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22rgba(255,255,255,0.6)%22 font-size=%228%22 font-family=%22Arial%22%3EMinistry of Tourism%3C/text%3E%3C/svg%3E';">
                    </div>
                    <div class="partner-logo-item">
                        <img src="<?php echo $base_path; ?>assets/images/partners/ghatof-logo.png" alt="GHATOF" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2240%22%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22rgba(255,255,255,0.6)%22 font-size=%2210%22 font-family=%22Arial%22%3EGHATOF%3C/text%3E%3C/svg%3E';">
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 32px 40px;
    }

    .footer-bottom-content {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    .footer-copyright {
        flex: 1;
    }

    .footer-copyright p {
        margin: 0;
        color: rgba(255,255,255,0.7);
        font-size: 14px;
    }

    .footer-partners {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .partner-label {
        color: rgba(255,255,255,0.5);
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        white-space: nowrap;
    }

    .partner-logos {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .partner-logo-item {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 40px;
    }

    .partner-logo-item img {
        max-height: 35px;
        max-width: 100px;
        height: auto;
        width: auto;
        object-fit: contain;
        filter: brightness(0) invert(1);
        opacity: 0.7;
        transition: all 0.3s;
    }

    .partner-logo-item img:hover {
        opacity: 1;
    }

    .footer-certifications {
        display: flex;
        gap: 12px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .cert-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.1);
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 11px;
        color: rgba(255,255,255,0.8);
    }

    .cert-badge i {
        color: #d4a017;
    }

    .footer-social {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }

    .footer-social a {
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
    }

    .footer-social a:hover {
        background: #d4a017;
        transform: translateY(-2px);
    }

    .footer-tagline {
        font-size: 12px;
        color: rgba(255,255,255,0.5);
        margin-top: 8px;
    }

    .footer-adinkra {
        display: inline-block;
        vertical-align: middle;
        margin: 0 4px;
        opacity: 0.8;
    }

    /* Kente Pattern CSS */
    .kente-pattern-top {
        height: 8px;
        background: linear-gradient(
            90deg,
            #000000 0%,
            #000000 12.5%,
            #FF0000 12.5%,
            #FF0000 25%,
            #FFD700 25%,
            #FFD700 37.5%,
            #000000 37.5%,
            #000000 50%,
            #FF0000 50%,
            #FF0000 62.5%,
            #FFD700 62.5%,
            #FFD700 75%,
            #000000 75%,
            #000000 87.5%,
            #FF0000 87.5%,
            #FF0000 100%
        );
        background-size: 40px 8px;
        position: relative;
        margin-bottom: 0;
    }

    .kente-pattern-top::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: repeating-linear-gradient(
            90deg,
            #000000 0px,
            #000000 5px,
            #FFD700 5px,
            #FFD700 10px,
            #000000 10px,
            #000000 15px,
            #FF0000 15px,
            #FF0000 20px
        );
        opacity: 0.6;
    }

    .cultural-phrase {
        color: #d4a017;
        font-weight: 600;
        font-style: italic;
    }

    @media (max-width: 768px) {
        .footer-bottom {
            padding: 24px 20px;
        }

        .footer-bottom-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 24px;
        }

        .footer-partners {
            flex-direction: column;
            gap: 16px;
        }

        .partner-logos {
            gap: 20px;
            justify-content: center;
        }

        .partner-logo-item {
            height: 35px;
        }

        .partner-logo-item img {
            max-height: 30px;
            max-width: 80px;
        }
    }
</style>
