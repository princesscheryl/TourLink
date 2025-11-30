const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
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

// Close menu when clicking on a link
document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Rotating text functionality for hero section
(function() {
    const rotatingTexts = document.querySelectorAll('.rotating-text');
    if (rotatingTexts.length === 0) return;

    let currentIndex = 0;
    const totalTexts = rotatingTexts.length;

    function rotateText() {
        // Remove active class from current text
        rotatingTexts[currentIndex].classList.remove('active');
        
        // Move to next text
        currentIndex = (currentIndex + 1) % totalTexts;
        
        // Add active class to new text
        rotatingTexts[currentIndex].classList.add('active');
    }

    // Start rotation after 6 seconds, then repeat every 6 seconds
    setInterval(rotateText, 6000);
})();

// Close menu when clicking outside
document.addEventListener('click', (e) => {
    if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }
});

// Category Tabs
const tabButtons = document.querySelectorAll('.tab-btn');

tabButtons.forEach(button => {
    button.addEventListener('click', () => {
        tabButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        // In a real application, this would filter the destinations
        // For now, it just changes the active state
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Featured Experiences Slider
let featuredSliderPosition = 0;
const featuredSlider = document.getElementById('featuredSlider');

function slideFeaturedExperiences(direction) {
    if (!featuredSlider) return;
    
    const cardWidth = 374; // 350px card + 24px gap
    const containerWidth = featuredSlider.parentElement.offsetWidth;
    const maxScroll = featuredSlider.scrollWidth - containerWidth;
    
    featuredSliderPosition += direction * cardWidth;
    
    // Clamp position
    if (featuredSliderPosition < 0) {
        featuredSliderPosition = 0;
    }
    if (featuredSliderPosition > maxScroll) {
        featuredSliderPosition = maxScroll;
    }
    
    featuredSlider.style.transform = `translateX(-${featuredSliderPosition}px)`;
}

// Keyboard navigation for slider arrows
document.querySelectorAll('.slider-arrow').forEach(arrow => {
    arrow.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            const direction = this.classList.contains('slider-arrow-left') ? -1 : 1;
            slideFeaturedExperiences(direction);
        }
    });
});
