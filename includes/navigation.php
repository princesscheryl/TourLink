<?php
/**
 * Modular Navigation Component
 * Reusable navigation bar for all pages
 */

// Determine the correct base path
$current_file = basename($_SERVER['PHP_SELF']);
$in_view_folder = (strpos($_SERVER['PHP_SELF'], '/view/') !== false);
$in_admin_folder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);

// Set base path based on current location
if ($in_view_folder || $in_admin_folder) {
    $base_path = '../';
} else {
    $base_path = '';
}
?>

<!-- Navigation -->
<nav class="main-nav" role="navigation" aria-label="Main navigation">
    <ul class="nav-menu" id="navMenu">
        <li><a href="<?php echo $base_path; ?>index_tourlink.php" data-i18n="nav.home">Home</a></li>
        <li><a href="<?php echo $base_path; ?>view/all_services.php" data-i18n="nav.destinations">Browse Services</a></li>
        <li><a href="<?php echo $base_path; ?>view/about.php" data-i18n="nav.about">About</a></li>
        <li><a href="<?php echo $base_path; ?>view/contact.php" data-i18n="nav.contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Profile Dropdown -->
            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-trigger" onclick="toggleProfileDropdown()">
                    <?php if(!empty($_SESSION['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($base_path . $_SESSION['profile_image']); ?>" alt="Profile" class="profile-pic">
                    <?php else: ?>
                        <div class="profile-pic-placeholder">
                            <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                    <i class="fa fa-chevron-down dropdown-icon"></i>
                </div>
                <div class="dropdown-menu-custom">
                    <a href="<?php echo $base_path; ?>view/profile_settings.php">
                        <i class="fa fa-user-circle"></i>
                        <span data-i18n="nav.profile_settings">Profile Settings</span>
                    </a>
                    <a href="<?php echo $base_path; ?>view/my_favorites.php">
                        <i class="fas fa-heart"></i>
                        <span data-i18n="favorites.my_favorites">My Favorites</span>
                    </a>
                    <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'provider'): ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $base_path; ?>admin/provider_dashboard.php">
                            <i class="fa fa-th-large"></i>
                            <span data-i18n="nav.dashboard">Dashboard</span>
                        </a>
                        <a href="<?php echo $base_path; ?>admin/manage_services.php">
                            <i class="fa fa-briefcase"></i>
                            <span data-i18n="nav.my_services">My Services</span>
                        </a>
                        <a href="<?php echo $base_path; ?>admin/bookings.php">
                            <i class="fa fa-calendar-check"></i>
                            <span data-i18n="nav.bookings">Bookings</span>
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0)" onclick="toggleTheme(); event.stopPropagation();" id="themeToggle">
                        <i class="fa fa-moon"></i>
                        <span class="toggle-text" data-i18n="nav.dark_mode">Dark Mode</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo $base_path; ?>login/logout.php">
                        <i class="fa fa-sign-out-alt"></i>
                        <span data-i18n="nav.logout">Logout</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo $base_path; ?>login/login.php" class="btn-signin" data-i18n="nav.sign_in">Sign In</a>
            <a href="<?php echo $base_path; ?>login/register.php" class="btn-join" data-i18n="nav.join">Join</a>
        <?php endif; ?>

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

        <!-- Hamburger Menu (Mobile) -->
        <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<script>
// Profile dropdown toggle
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

// Mobile menu toggle
function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    const hamburger = document.getElementById('hamburger');
    navMenu.classList.toggle('active');
    hamburger.classList.toggle('active');
}
</script>
