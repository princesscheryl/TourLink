/**
 * TourLink Translator - Multi-language Support
 * Supports: English, French, Spanish, Twi, Ga
 */

(function() {
    'use strict';

    const Translator = {
        // Available languages
        languages: {
            'en': 'English',
            'fr': 'Français',
            'es': 'Español',
            'tw': 'Twi',
            'ga': 'Ga'
        },

        // Current language
        currentLang: 'en',

        // Translations cache
        translations: {},

        // Initialize translator
        init: function() {
            // Get saved language or use default
            this.currentLang = this.getSavedLanguage();

            // Load translations
            this.loadTranslations(this.currentLang).then(() => {
                this.translatePage();
                this.updateLanguageSelector();
            });
        },

        // Get saved language from localStorage
        getSavedLanguage: function() {
            const saved = localStorage.getItem('tourlink-language');
            return saved && this.languages[saved] ? saved : 'en';
        },

        // Save language to localStorage
        saveLanguage: function(lang) {
            localStorage.setItem('tourlink-language', lang);
        },

        // Load translation JSON file
        loadTranslations: async function(lang) {
            // If already loaded, return
            if (this.translations[lang]) {
                return Promise.resolve(this.translations[lang]);
            }

            try {
                // Determine correct path based on current location
                let basePath = '';
                const currentPath = window.location.pathname;

                if (currentPath.includes('/view/')) {
                    basePath = '../';
                } else if (currentPath.includes('/admin/')) {
                    basePath = '../';
                } else if (currentPath.includes('/login/')) {
                    basePath = '../';
                } else if (currentPath.includes('/actions/')) {
                    basePath = '../';
                }

                const response = await fetch(`${basePath}languages/${lang}.json`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                this.translations[lang] = data;
                return data;
            } catch (error) {
                console.error(`Error loading ${lang} translations:`, error);
                // Fall back to English if loading fails
                if (lang !== 'en') {
                    return this.loadTranslations('en');
                }
                return {};
            }
        },

        // Get translation by key (supports nested keys like "nav.home")
        get: function(key) {
            const keys = key.split('.');
            let value = this.translations[this.currentLang];

            for (const k of keys) {
                if (value && typeof value === 'object') {
                    value = value[k];
                } else {
                    return key; // Return key if translation not found
                }
            }

            return value || key;
        },

        // Translate entire page
        translatePage: function() {
            // Translate elements with data-i18n attribute
            const elements = document.querySelectorAll('[data-i18n]');

            elements.forEach(element => {
                const key = element.getAttribute('data-i18n');
                const translation = this.get(key);

                // Handle different element types
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    if (element.placeholder) {
                        element.placeholder = translation;
                    } else {
                        element.value = translation;
                    }
                } else if (element.tagName === 'IMG') {
                    element.alt = translation;
                } else {
                    // Check if element has HTML content (like <br>)
                    if (translation.includes('<br>')) {
                        element.innerHTML = translation;
                    } else {
                        element.textContent = translation;
                    }
                }
            });

            // Translate elements with data-i18n-placeholder
            const placeholderElements = document.querySelectorAll('[data-i18n-placeholder]');
            placeholderElements.forEach(element => {
                const key = element.getAttribute('data-i18n-placeholder');
                element.placeholder = this.get(key);
            });

            // Translate elements with data-i18n-title (tooltips)
            const titleElements = document.querySelectorAll('[data-i18n-title]');
            titleElements.forEach(element => {
                const key = element.getAttribute('data-i18n-title');
                element.title = this.get(key);
            });

            // Translate elements with data-i18n-aria-label
            const ariaElements = document.querySelectorAll('[data-i18n-aria-label]');
            ariaElements.forEach(element => {
                const key = element.getAttribute('data-i18n-aria-label');
                element.setAttribute('aria-label', this.get(key));
            });

            // Update page title
            const titleElement = document.querySelector('title[data-i18n]');
            if (titleElement) {
                const key = titleElement.getAttribute('data-i18n');
                document.title = this.get(key);
            }
        },

        // Change language
        changeLanguage: async function(lang) {
            if (!this.languages[lang]) {
                console.error(`Language ${lang} not supported`);
                return;
            }

            this.currentLang = lang;
            this.saveLanguage(lang);

            // Load new translations
            await this.loadTranslations(lang);

            // Translate page
            this.translatePage();

            // Update language selector
            this.updateLanguageSelector();

            // Announce to screen reader if available
            if (window.announceToScreenReader) {
                window.announceToScreenReader(`Language changed to ${this.languages[lang]}`);
            }

            // Trigger custom event for other scripts to respond to language change
            window.dispatchEvent(new CustomEvent('languageChanged', {
                detail: { language: lang }
            }));
        },

        // Update language selector UI
        updateLanguageSelector: function() {
            const selector = document.getElementById('languageSelector');
            if (!selector) return;

            // Update selector value if it's a select element
            if (selector.tagName === 'SELECT') {
                selector.value = this.currentLang;
            }

            // Update any displayed language name
            const langDisplay = document.querySelector('.current-language');
            if (langDisplay) {
                langDisplay.textContent = this.languages[this.currentLang];
            }

            // Update flag or icon if present
            const langFlag = document.querySelector('.language-flag');
            if (langFlag) {
                langFlag.className = `language-flag flag-${this.currentLang}`;
            }
        },

        // Create language switcher HTML (can be called to generate switcher)
        createLanguageSwitcher: function(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const select = document.createElement('select');
            select.id = 'languageSelector';
            select.className = 'language-selector';
            select.setAttribute('aria-label', 'Select language');

            // Add options
            for (const [code, name] of Object.entries(this.languages)) {
                const option = document.createElement('option');
                option.value = code;
                option.textContent = name;
                if (code === this.currentLang) {
                    option.selected = true;
                }
                select.appendChild(option);
            }

            // Add change event listener
            select.addEventListener('change', (e) => {
                this.changeLanguage(e.target.value);
            });

            container.appendChild(select);
        },

        // Helper method to translate a specific string
        translate: function(key, fallback = '') {
            return this.get(key) || fallback;
        },

        // Format number based on language (for prices, dates, etc.)
        formatNumber: function(number, options = {}) {
            const locales = {
                'en': 'en-GH',
                'fr': 'fr-FR',
                'es': 'es-ES',
                'tw': 'en-GH', // Use Ghana locale for Twi
                'ga': 'en-GH'  // Use Ghana locale for Ga
            };

            return new Intl.NumberFormat(locales[this.currentLang] || 'en-GH', options).format(number);
        },

        // Format currency (GHS)
        formatCurrency: function(amount) {
            return this.formatNumber(amount, {
                style: 'currency',
                currency: 'GHS'
            });
        },

        // Format date based on language
        formatDate: function(date, options = {}) {
            const locales = {
                'en': 'en-GH',
                'fr': 'fr-FR',
                'es': 'es-ES',
                'tw': 'en-GH',
                'ga': 'en-GH'
            };

            const dateObj = date instanceof Date ? date : new Date(date);
            return new Intl.DateTimeFormat(locales[this.currentLang] || 'en-GH', options).format(dateObj);
        }
    };

    // Initialize translator when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => Translator.init());
    } else {
        Translator.init();
    }

    // Make translator available globally
    window.Translator = Translator;

    // Global shorthand for translation
    window.t = function(key) {
        return Translator.get(key);
    };

    // Global function to change language (for onclick events)
    window.changeLanguage = function(lang) {
        return Translator.changeLanguage(lang);
    };

})();
