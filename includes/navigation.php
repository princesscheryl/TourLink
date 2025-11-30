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
    <div class="nav-container">
        <a href="<?php echo $base_path; ?>index_tourlink.php" class="logo" aria-label="TourLink Home">TourLink<span class="logo-dot">.</span></a>
        <ul class="nav-menu" id="navMenu" role="menubar">
            <li><a href="<?php echo $base_path; ?>index_tourlink.php" data-i18n="nav.home">Home</a></li>
            <li><a href="<?php echo $base_path; ?>view/all_services.php" data-i18n="nav.destinations">Browse Services</a></li>
            <li><a href="<?php echo $base_path; ?>view/community_tourism.php">Community Tourism</a></li>
            <li><a href="<?php echo $base_path; ?>view/festivals.php">Festivals</a></li>
            <li><a href="<?php echo $base_path; ?>view/about.php" data-i18n="nav.about">About</a></li>
            <li><a href="<?php echo $base_path; ?>view/contact.php" data-i18n="nav.contact">Contact</a></li>
        </ul>

        <!-- Search Bar -->
        <div class="nav-search">
            <form action="<?php echo $base_path; ?>view/search_services.php" method="GET" class="search-form">
                <div class="search-input-container">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text"
                           name="q"
                           id="navSearchInput"
                           class="search-input"
                           placeholder="Search destinations, tours..."
                           data-i18n-placeholder="search.placeholder"
                           autocomplete="off">
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>
            </form>
        </div>

        <div class="nav-actions">
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Profile Dropdown -->
            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-trigger" onclick="toggleProfileDropdown()">
                    <?php 
                    require_once __DIR__ . '/../classes/hosted_upload_class.php';
                    if(!empty($_SESSION['profile_image'])): 
                        $profile_img_url = HostedUpload::getImageUrl($_SESSION['profile_image'], $base_path);
                    ?>
                        <img src="<?php echo htmlspecialchars($profile_img_url); ?>" alt="Profile" class="profile-pic" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-pic-placeholder" style="display:none;">
                            <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                        </div>
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
                        Profile Settings
                    </a>
                    <a href="<?php echo $base_path; ?>view/my_favorites.php">
                        <i class="fas fa-heart"></i>
                        <span data-i18n="favorites.my_favorites">My Favorites</span>
                    </a>
                    <?php if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'provider'): ?>
                    <a href="<?php echo $base_path; ?>view/my_bookings.php">
                        <i class="fa fa-calendar-check"></i>
                        My Bookings
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo $base_path; ?>view/messages.php">
                        <i class="fa fa-envelope"></i>
                        Messages
                        <?php 
                        if (isset($_SESSION['user_id'])) {
                            require_once __DIR__ . '/../controllers/message_controller.php';
                            $unread_count = get_unread_message_count_ctr($_SESSION['user_id']);
                            if ($unread_count > 0): 
                        ?>
                            <span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem; margin-left: 8px;"><?php echo $unread_count; ?></span>
                        <?php 
                            endif;
                        }
                        ?>
                    </a>
                    <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'provider'): ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo $base_path; ?>admin/provider_dashboard.php">
                            <i class="fa fa-th-large"></i>
                            Dashboard
                        </a>
                        <a href="<?php echo $base_path; ?>admin/manage_services.php">
                            <i class="fa fa-briefcase"></i>
                            My Services
                        </a>
                        <a href="<?php echo $base_path; ?>view/provider/manage_bookings.php">
                            <i class="fa fa-calendar-check"></i>
                            Bookings
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0)" onclick="toggleTheme(); event.stopPropagation();" id="themeToggle">
                        <i class="fa fa-moon"></i>
                        <span class="toggle-text">Dark Mode</span>
                    </a>
                    <div class="dropdown-item-select">
                        <i class="fa fa-language"></i>
                        <select id="profileLanguageSelector" class="profile-language-selector" aria-label="Select language" onchange="changeLanguage(this.value)" onclick="event.stopPropagation();">
                            <option value="en">English</option>
                            <option value="fr">Français</option>
                            <option value="es">Español</option>
                            <option value="tw">Twi</option>
                            <option value="ga">Ga</option>
                        </select>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo $base_path; ?>login/logout.php">
                        <i class="fa fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo $base_path; ?>login/login.php" class="btn-signin" data-i18n="nav.sign_in">Sign In</a>
        <?php endif; ?>

        <?php if(!isset($_SESSION['user_id'])): ?>
        <!-- Language Switcher (shown when not logged in) -->
        <select id="languageSelector" class="language-selector" aria-label="Select language" onchange="changeLanguage(this.value)">
            <option value="en">English</option>
            <option value="fr">Français</option>
            <option value="es">Español</option>
            <option value="tw">Twi</option>
            <option value="ga">Ga</option>
        </select>
        <?php endif; ?>

        <!-- Hamburger Menu (Mobile) -->
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="navMenu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hamburger Menu Toggle
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // Close menu when clicking on a link
    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('click', () => {
            if (hamburger && navMenu) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    });
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

// Search Autocomplete
const searchInput = document.getElementById('navSearchInput');
const searchSuggestions = document.getElementById('searchSuggestions');
let searchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchSuggestions.innerHTML = '';
            searchSuggestions.classList.remove('active');
            return;
        }

        // Debounce the search
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !searchSuggestions.contains(event.target)) {
            searchSuggestions.classList.remove('active');
        }
    });

    // Handle form submission
    searchInput.closest('form').addEventListener('submit', function(e) {
        if (!searchInput.value.trim()) {
            e.preventDefault();
        }
    });
}

function fetchSearchSuggestions(query) {
    // Create base path for AJAX request
    const currentPath = window.location.pathname;
    const isInSubdir = currentPath.includes('/view/') || currentPath.includes('/admin/') || currentPath.includes('/login/');
    const basePath = isInSubdir ? '../' : '';

    fetch(`${basePath}actions/search_suggestions.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchSuggestions(data);
        })
        .catch(error => {
            console.error('Search suggestions error:', error);
        });
}

function displaySearchSuggestions(data) {
    if (!data || data.length === 0) {
        searchSuggestions.innerHTML = '<div class="search-no-results">No results found</div>';
        searchSuggestions.classList.add('active');
        return;
    }

    // Calculate base path based on current location
    const currentPath = window.location.pathname;
    const isInSubdir = currentPath.includes('/view/') || currentPath.includes('/admin/') || currentPath.includes('/login/');
    const viewPath = isInSubdir ? '' : 'view/';
    const basePath = isInSubdir ? '../view/' : 'view/';

    let html = '';

    // Group by type
    const services = data.filter(item => item.type === 'service');
    const categories = data.filter(item => item.type === 'category');
    const recent = data.filter(item => item.type === 'recent');

    if (recent.length > 0) {
        html += '<div class="suggestions-group"><div class="suggestions-header">Recent Searches</div>';
        recent.forEach(item => {
            html += `<a href="${basePath}search_services.php?q=${encodeURIComponent(item.text)}" class="suggestion-item recent">
                <i class="fa fa-history"></i>
                <span>${escapeHtml(item.text)}</span>
            </a>`;
        });
        html += '</div>';
    }

    if (services.length > 0) {
        html += '<div class="suggestions-group"><div class="suggestions-header">Services</div>';
        services.slice(0, 5).forEach(item => {
            html += `<a href="${basePath}single_service.php?id=${item.id}" class="suggestion-item service">
                <i class="fa fa-map-marker-alt"></i>
                <span>${escapeHtml(item.text)}</span>
                <span class="suggestion-category">${escapeHtml(item.category || '')}</span>
            </a>`;
        });
        html += '</div>';
    }

    if (categories.length > 0) {
        html += '<div class="suggestions-group"><div class="suggestions-header">Categories</div>';
        categories.forEach(item => {
            html += `<a href="${basePath}all_services.php?category=${item.id}" class="suggestion-item category">
                <i class="fa fa-th-large"></i>
                <span>${escapeHtml(item.text)}</span>
            </a>`;
        });
        html += '</div>';
    }

    searchSuggestions.innerHTML = html;
    searchSuggestions.classList.add('active');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
