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
    <!-- Partnership Banner -->
    <div class="footer-partners">
        <div class="partners-container">
            <span class="partner-label">In Partnership With</span>
            <div class="partner-logos">
                <div class="partner-logo-item">
                    <img src="<?php echo $base_path; ?>assets/images/partners/gta-logo.png" alt="Ghana Tourism Authority" onerror="this.style.display='none'">
                </div>
                <div class="partner-logo-item">
                    <img src="<?php echo $base_path; ?>assets/images/partners/ministry-tourism-logo.png" alt="Ministry of Tourism" onerror="this.style.display='none'">
                </div>
                <div class="partner-logo-item">
                    <img src="<?php echo $base_path; ?>assets/images/partners/ghatof-logo.png" alt="GHATOF" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </div>

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
    <div class="footer-bottom">
        <p>&copy; 2025 <span data-i18n="app_name">TourLink</span>. <span data-i18n="footer.copyright">All rights reserved.</span></p>
        <p class="footer-tagline">Proudly supporting Ghana's tourism industry and local communities</p>
    </div>
</footer>

<style>
    .footer-partners {
        background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
        padding: 20px 40px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .partners-container {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 32px;
        flex-wrap: wrap;
    }

    .partner-label {
        color: rgba(255,255,255,0.7);
        font-size: 13px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .partner-logos {
        display: flex;
        align-items: center;
        gap: 40px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .partner-logo-item {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 60px;
    }

    .partner-logo-item img {
        max-height: 50px;
        max-width: 150px;
        height: auto;
        width: auto;
        object-fit: contain;
        filter: brightness(0) invert(1);
        opacity: 0.9;
        transition: all 0.3s;
    }

    .partner-logo-item img:hover {
        opacity: 1;
        transform: scale(1.05);
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

    @media (max-width: 768px) {
        .footer-partners {
            padding: 20px 20px;
        }

        .partners-container {
            flex-direction: column;
            gap: 20px;
        }

        .partner-logos {
            gap: 24px;
        }

        .partner-logo-item img {
            max-height: 40px;
            max-width: 120px;
        }
    }
</style>
