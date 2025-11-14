/**
 * TourLink Accessibility JavaScript
 * Keyboard navigation, focus management, and accessibility features
 */

(function() {
    'use strict';

    // ===================================
    // KEYBOARD VS MOUSE DETECTION
    // ===================================
    let isKeyboardUser = false;

    // Detect keyboard usage
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            isKeyboardUser = true;
            document.body.classList.add('keyboard-nav');
            document.body.classList.remove('mouse-nav');
        }
    });

    // Detect mouse usage
    document.addEventListener('mousedown', function() {
        isKeyboardUser = false;
        document.body.classList.add('mouse-nav');
        document.body.classList.remove('keyboard-nav');
    });

    // ===================================
    // KEYBOARD NAVIGATION SHORTCUTS
    // ===================================
    document.addEventListener('keydown', function(e) {
        // ESC key closes dropdowns and modals
        if (e.key === 'Escape') {
            closeAllDropdowns();
            closeAllModals();
        }

        // Alt+H = Home
        if (e.altKey && e.key === 'h') {
            e.preventDefault();
            window.location.href = '/tourlink/index_tourlink.php';
        }

        // Alt+S = Search (focus search input)
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"], input[name="search"]');
            if (searchInput) searchInput.focus();
        }

        // Alt+M = Main content (skip to main)
        if (e.altKey && e.key === 'm') {
            e.preventDefault();
            const mainContent = document.querySelector('main, #main-content');
            if (mainContent) {
                mainContent.setAttribute('tabindex', '-1');
                mainContent.focus();
            }
        }
    });

    // ===================================
    // DROPDOWN KEYBOARD NAVIGATION
    // ===================================
    function setupDropdownKeyboardNav() {
        const dropdowns = document.querySelectorAll('.profile-dropdown');

        dropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.profile-trigger');
            const menu = dropdown.querySelector('.dropdown-menu-custom');
            const menuItems = menu ? menu.querySelectorAll('a') : [];

            if (!trigger || !menu) return;

            // Enter or Space to toggle dropdown
            trigger.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    dropdown.classList.toggle('active');

                    if (dropdown.classList.contains('active') && menuItems.length > 0) {
                        menuItems[0].focus();
                    }
                }

                // Arrow down opens and focuses first item
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    dropdown.classList.add('active');
                    if (menuItems.length > 0) {
                        menuItems[0].focus();
                    }
                }
            });

            // Arrow navigation within dropdown
            menuItems.forEach((item, index) => {
                item.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const nextItem = menuItems[index + 1] || menuItems[0];
                        nextItem.focus();
                    }

                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const prevItem = menuItems[index - 1] || menuItems[menuItems.length - 1];
                        prevItem.focus();
                    }

                    if (e.key === 'Home') {
                        e.preventDefault();
                        menuItems[0].focus();
                    }

                    if (e.key === 'End') {
                        e.preventDefault();
                        menuItems[menuItems.length - 1].focus();
                    }
                });
            });
        });
    }

    // ===================================
    // CLOSE DROPDOWNS HELPER
    // ===================================
    function closeAllDropdowns() {
        const dropdowns = document.querySelectorAll('.profile-dropdown.active');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }

    // ===================================
    // CLOSE MODALS HELPER
    // ===================================
    function closeAllModals() {
        // Close SweetAlert modals
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        // Close Bootstrap modals
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }

    // ===================================
    // FOCUS TRAP FOR MODALS
    // ===================================
    function trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        element.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                // Shift + Tab
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                // Tab
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    }

    // ===================================
    // ANNOUNCE TO SCREEN READERS
    // ===================================
    function announceToScreenReader(message, priority = 'polite') {
        const announcement = document.createElement('div');
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', priority);
        announcement.classList.add('sr-only');
        announcement.textContent = message;

        document.body.appendChild(announcement);

        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }

    // Make it available globally
    window.announceToScreenReader = announceToScreenReader;

    // ===================================
    // HIGH CONTRAST MODE TOGGLE
    // ===================================
    window.toggleHighContrast = function() {
        const currentMode = document.documentElement.getAttribute('data-contrast');
        const newMode = currentMode === 'high' ? 'normal' : 'high';

        document.documentElement.setAttribute('data-contrast', newMode);
        localStorage.setItem('tourlink-contrast', newMode);

        announceToScreenReader(
            newMode === 'high' ? 'High contrast mode enabled' : 'High contrast mode disabled'
        );

        return newMode;
    };

    // ===================================
    // FONT SIZE ADJUSTMENT
    // ===================================
    window.adjustFontSize = function(size) {
        const validSizes = ['small', 'medium', 'large', 'xlarge'];
        if (!validSizes.includes(size)) return;

        document.documentElement.setAttribute('data-font-size', size);
        localStorage.setItem('tourlink-font-size', size);

        announceToScreenReader('Font size changed to ' + size);
    };

    // ===================================
    // INITIALIZE SAVED PREFERENCES
    // ===================================
    function initAccessibilityPreferences() {
        // High contrast
        const savedContrast = localStorage.getItem('tourlink-contrast');
        if (savedContrast) {
            document.documentElement.setAttribute('data-contrast', savedContrast);
        }

        // Font size
        const savedFontSize = localStorage.getItem('tourlink-font-size');
        if (savedFontSize) {
            document.documentElement.setAttribute('data-font-size', savedFontSize);
        }
    }

    // ===================================
    // FORM VALIDATION ACCESSIBILITY
    // ===================================
    function enhanceFormAccessibility() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            // Add novalidate to use custom validation
            form.setAttribute('novalidate', '');

            // Required fields
            const requiredInputs = form.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                const label = form.querySelector(`label[for="${input.id}"]`);
                if (label && !label.classList.contains('required')) {
                    label.classList.add('required');
                }

                // Add aria-required
                input.setAttribute('aria-required', 'true');
            });

            // Validate on submit
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const firstInvalid = form.querySelector('[aria-invalid="true"]');

                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.setAttribute('aria-invalid', 'true');
                        isValid = false;

                        // Add error message if not exists
                        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-message')) {
                            const error = document.createElement('div');
                            error.classList.add('error-message');
                            error.textContent = 'This field is required';
                            error.setAttribute('role', 'alert');
                            input.parentNode.insertBefore(error, input.nextSibling);
                        }
                    } else {
                        input.setAttribute('aria-invalid', 'false');
                        const errorMsg = input.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('error-message')) {
                            errorMsg.remove();
                        }
                    }
                });

                if (!isValid && firstInvalid) {
                    firstInvalid.focus();
                }
            });
        });
    }

    // ===================================
    // INITIALIZE ON PAGE LOAD
    // ===================================
    document.addEventListener('DOMContentLoaded', function() {
        initAccessibilityPreferences();
        setupDropdownKeyboardNav();
        enhanceFormAccessibility();

        // Add ARIA labels to common elements if missing
        const hamburger = document.getElementById('hamburger');
        if (hamburger && !hamburger.getAttribute('aria-label')) {
            hamburger.setAttribute('aria-label', 'Toggle navigation menu');
            hamburger.setAttribute('aria-expanded', 'false');
        }

        // Announce page load to screen readers
        announceToScreenReader('Page loaded');
    });

    // ===================================
    // EXPORT UTILITIES
    // ===================================
    window.AccessibilityUtils = {
        announce: announceToScreenReader,
        trapFocus: trapFocus,
        closeDropdowns: closeAllDropdowns,
        closeModals: closeAllModals
    };

})();
