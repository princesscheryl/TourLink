/**
 * Dark Mode JavaScript for TourLink
 * Handles theme switching and persistence
 */

(function() {
    'use strict';

    // Theme management
    const ThemeManager = {
        // Get current theme from localStorage or system preference
        getCurrentTheme: function() {
            const savedTheme = localStorage.getItem('tourlink-theme');
            if (savedTheme) {
                return savedTheme;
            }

            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return 'dark';
            }

            return 'light';
        },

        // Apply theme to document
        applyTheme: function(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }

            // Update toggle button icon if it exists
            this.updateToggleButton(theme);
        },

        // Save theme preference
        saveTheme: function(theme) {
            localStorage.setItem('tourlink-theme', theme);
        },

        // Toggle between themes
        toggleTheme: function() {
            const currentTheme = this.getCurrentTheme();
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            this.applyTheme(newTheme);
            this.saveTheme(newTheme);

            return newTheme;
        },

        // Update toggle button appearance
        updateToggleButton: function(theme) {
            const toggleBtn = document.getElementById('themeToggle');
            if (!toggleBtn) return;

            const icon = toggleBtn.querySelector('i');
            const text = toggleBtn.querySelector('.toggle-text');

            if (theme === 'dark') {
                if (icon) {
                    icon.className = 'fa fa-sun';
                }
                if (text) {
                    text.textContent = 'Light Mode';
                }
            } else {
                if (icon) {
                    icon.className = 'fa fa-moon';
                }
                if (text) {
                    text.textContent = 'Dark Mode';
                }
            }
        },

        // Initialize theme on page load
        init: function() {
            const theme = this.getCurrentTheme();
            this.applyTheme(theme);

            // Listen for system theme changes
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    // Only auto-switch if user hasn't set a preference
                    if (!localStorage.getItem('tourlink-theme')) {
                        this.applyTheme(e.matches ? 'dark' : 'light');
                    }
                });
            }
        }
    };

    // Initialize theme immediately (before page renders)
    ThemeManager.init();

    // Make toggleTheme available globally
    window.toggleTheme = function() {
        return ThemeManager.toggleTheme();
    };

    // Export ThemeManager for potential use in other scripts
    window.ThemeManager = ThemeManager;

})();
