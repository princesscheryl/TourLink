/**
 * Single Service Page JavaScript
 * Handles booking modal, pricing calculations, and gallery
 */

// Service data will be set via window object from inline script
let serviceData = window.serviceData || {
    basePrice: 0,
    pricingUnit: '',
    maxCapacity: null
};

let guestCount = 1;

function bookService() {
    const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
    modal.show();
}

function updateGuestCount(change) {
    const newCount = guestCount + change;
    const maxCapacity = serviceData.maxCapacity || 20;

    if (newCount >= 1 && newCount <= maxCapacity) {
        guestCount = newCount;
        const guestCountDisplay = document.getElementById('guestCountDisplay');
        const numberOfPeople = document.getElementById('number_of_people');
        const guestMultiplier = document.getElementById('guestMultiplier');
        
        if (guestCountDisplay) guestCountDisplay.textContent = guestCount;
        if (numberOfPeople) numberOfPeople.value = guestCount;
        if (guestMultiplier) guestMultiplier.textContent = guestCount;

        // Update decrease button state
        const decreaseBtn = document.getElementById('decreaseGuests');
        const increaseBtn = document.getElementById('increaseGuests');
        if (decreaseBtn) decreaseBtn.disabled = (guestCount <= 1);
        if (increaseBtn) increaseBtn.disabled = (guestCount >= maxCapacity);

        // Update pricing
        updatePricing();
    }
}

function updatePricing() {
    let total = serviceData.basePrice;
    let duration = 1;

    // Get duration if applicable
    const durationSelect = document.getElementById('service_duration');
    if (durationSelect) {
        duration = parseInt(durationSelect.value) || 1;
    }

    // Calculate based on pricing unit
    if (serviceData.pricingUnit === 'per_hour' || serviceData.pricingUnit === 'per_day') {
        total = serviceData.basePrice * duration;

        // Update duration display
        const durationMultiplier = document.getElementById('durationMultiplier');
        const durationSubtotal = document.getElementById('durationSubtotal');
        if (durationMultiplier) durationMultiplier.textContent = duration;
        if (durationSubtotal) durationSubtotal.textContent = 'GHS ' + formatNumber(total);
    }

    if (serviceData.pricingUnit === 'per_person') {
        total = serviceData.basePrice * guestCount;
        const subtotalEl = document.getElementById('subtotalAmount');
        const guestMultiplier = document.getElementById('guestMultiplier');
        if (guestMultiplier) guestMultiplier.textContent = guestCount;
        if (subtotalEl) subtotalEl.textContent = 'GHS ' + formatNumber(total);
    }

    const totalAmount = document.getElementById('totalAmount');
    if (totalAmount) totalAmount.textContent = 'GHS ' + formatNumber(total);
}

function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Gallery image switching
function changeGalleryImage(src, thumbElement) {
    const mainImage = document.getElementById('mainGalleryImage');
    if (mainImage) {
        mainImage.src = src;
    }
    // Update active state
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    if (thumbElement) {
        thumbElement.classList.add('active');
    }
}

function submitBooking() {
    const form = document.getElementById('bookingForm');
    if (!form) {
        console.error('Booking form not found');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Booking form not found. Please refresh the page and try again.',
                confirmButtonColor: '#2d6a4f'
            });
        } else {
            alert('Booking form not found. Please refresh the page and try again.');
        }
        return;
    }

    // Get form values - use form.querySelector as fallback if getElementById fails
    const serviceIdInput = document.getElementById('service_id') || form.querySelector('[name="service_id"]');
    const serviceDateInput = document.getElementById('service_date') || form.querySelector('[name="service_date"]');
    const serviceTimeInput = document.getElementById('service_time') || form.querySelector('[name="service_time"]');
    const numberOfPeopleInput = document.getElementById('number_of_people') || form.querySelector('[name="number_of_people"]');
    const serviceDurationInput = document.getElementById('service_duration') || form.querySelector('[name="service_duration"]');

    const serviceDate = serviceDateInput ? serviceDateInput.value : '';
    const serviceTime = serviceTimeInput ? serviceTimeInput.value : '';
    const serviceId = serviceIdInput ? serviceIdInput.value : '';
    const numberOfPeople = numberOfPeopleInput ? numberOfPeopleInput.value : '1';
    const serviceDuration = serviceDurationInput ? serviceDurationInput.value : '1';

    // Validate form
    if (!serviceDate) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Date Required',
                text: 'Please select a date for your booking.',
                confirmButtonColor: '#2d6a4f'
            });
        } else {
            alert('Please select a date for your booking.');
        }
        return;
    }

    if (!serviceTime) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Time Required',
                text: 'Please select a preferred time.',
                confirmButtonColor: '#2d6a4f'
            });
        } else {
            alert('Please select a preferred time.');
        }
        return;
    }

    if (!serviceId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Service ID is missing. Please refresh the page and try again.',
                confirmButtonColor: '#2d6a4f'
            });
        } else {
            alert('Service ID is missing. Please refresh the page and try again.');
        }
        return;
    }

    // Redirect to booking page with parameters
    const params = new URLSearchParams({
        service_id: serviceId,
        date: serviceDate,
        time: serviceTime,
        guests: numberOfPeople,
        duration: serviceDuration
    });

    window.location.href = 'book_service.php?' + params.toString();
}

// Initialize on page load
$(document).ready(function() {
    // Set minimum date to tomorrow
    const serviceDateInput = document.getElementById('service_date');
    if (serviceDateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        serviceDateInput.min = tomorrow.toISOString().split('T')[0];
    }

    // Initialize button states
    const decreaseBtn = document.getElementById('decreaseGuests');
    if (decreaseBtn) decreaseBtn.disabled = true;
    
    // Listen for duration changes
    const durationSelect = document.getElementById('service_duration');
    if (durationSelect) {
        durationSelect.addEventListener('change', function() {
            updatePricing();
        });
    }
});

// Update button text when favorite is toggled
$(document).on('favoriteToggled', function(event, data) {
    const currentServiceId = window.serviceId || null;
    
    if (currentServiceId && data.serviceId == currentServiceId) {
        // Update large favorite button (top of page)
        const buttonLarge = $('.favorite-btn-large');
        const span = buttonLarge.find('span');
        const iconLarge = buttonLarge.find('i');

        if (data.action === 'added') {
            if (span.length) span.text('Saved');
            iconLarge.removeClass('far').addClass('fas');
            buttonLarge.addClass('active');
        } else {
            if (span.length) span.text('Save');
            iconLarge.removeClass('fas').addClass('far');
            buttonLarge.removeClass('active');
        }

        // Update sidebar favorite button
        const buttonSidebar = $('.favorite-btn-sidebar');
        const iconSidebar = buttonSidebar.find('i');

        if (data.action === 'added') {
            buttonSidebar.html('<i class="fas fa-heart"></i> Saved');
            buttonSidebar.addClass('active');
            buttonSidebar.attr('aria-label', 'Remove from favorites');
        } else {
            buttonSidebar.html('<i class="far fa-heart"></i> Add to Favorites');
            buttonSidebar.removeClass('active');
            buttonSidebar.attr('aria-label', 'Add to favorites');
        }
    }
});

