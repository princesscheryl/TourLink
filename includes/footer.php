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
        </div>
        <div>
            <h4 data-i18n="footer.quick_links">Quick Links</h4>
            <ul>
                <li><a href="<?php echo $base_path; ?>view/about.php" data-i18n="footer.about_us">About Us</a></li>
                <li><a href="#faq" data-i18n="footer.faqs">FAQs</a></li>
                <li><a href="<?php echo $base_path; ?>view/contact.php" data-i18n="footer.contact_us">Contact Us</a></li>
                <li><a href="#privacy" data-i18n="footer.privacy_policy">Privacy Policy</a></li>
                <li><a href="#terms" data-i18n="footer.terms_conditions">Terms & Conditions</a></li>
            </ul>
        </div>
        <div>
            <h4 data-i18n="footer.social_media">Social Media</h4>
            <ul>
                <li><a href="#" aria-label="Visit our Facebook page">Facebook</a></li>
                <li><a href="#" aria-label="Visit our Instagram page">Instagram</a></li>
                <li><a href="#" aria-label="Visit our Twitter page">Twitter</a></li>
                <li><a href="#" aria-label="Visit our LinkedIn page">LinkedIn</a></li>
            </ul>
        </div>
        <div>
            <h4 data-i18n="footer.contact_info">Contact Info</h4>
            <p data-i18n="footer.location">Ashesi University, Berekuso<br>Ghana</p>
            <p data-i18n="footer.email">info@tourlink.com.gh</p>
            <p data-i18n="footer.phone">+233 50 123 4567</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 <span data-i18n="app_name">TourLink</span>. <span data-i18n="footer.copyright">All rights reserved.</span></p>
    </div>
</footer>
