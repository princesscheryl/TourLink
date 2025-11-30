/**
 * Book Service Page JavaScript
 * Handles discount code validation and payment initialization
 */

// Service data will be set via inline script
let serviceId, serviceDate, serviceTime, numberOfPeople, serviceDuration;
let totalAmount, originalAmount, discountAmount = 0;
let appliedDiscountCode = '';

// Initialize service data from window object
function initServiceData() {
    if (window.serviceBookingData) {
        serviceId = window.serviceBookingData.serviceId;
        serviceDate = window.serviceBookingData.serviceDate;
        serviceTime = window.serviceBookingData.serviceTime;
        numberOfPeople = window.serviceBookingData.numberOfPeople;
        serviceDuration = window.serviceBookingData.serviceDuration;
        totalAmount = window.serviceBookingData.totalAmount;
        originalAmount = window.serviceBookingData.originalAmount;
    }
}

// Discount code functionality
function showDiscountMessage(message, type) {
    const messageEl = document.getElementById('discountMessage');
    if (!messageEl) return;
    
    messageEl.textContent = message;
    messageEl.style.color = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#6c757d';
    messageEl.style.fontWeight = type === 'error' ? '600' : '400';
}

async function applyDiscount() {
    const codeInput = document.getElementById('discountCode');
    const applyBtn = document.getElementById('applyDiscountBtn');
    
    if (!codeInput || !applyBtn) return;
    
    const code = codeInput.value.trim().toUpperCase();

    if (!code) {
        showDiscountMessage('Please enter a discount code', 'error');
        return;
    }

    applyBtn.disabled = true;
    applyBtn.textContent = 'Checking...';

    try {
        const response = await fetch('../actions/validate_discount_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                discount_code: code,
                booking_amount: originalAmount,
                service_id: serviceId
            })
        });

        const data = await response.json();

        if (data.status === 'success') {
            discountAmount = parseFloat(data.discount_amount);
            appliedDiscountCode = code;
            totalAmount = originalAmount - discountAmount;

            // Update UI
            const discountAmountEl = document.getElementById('discountAmount');
            const discountRow = document.getElementById('discountRow');
            const totalAmountEl = document.getElementById('totalAmount');
            const removeBtn = document.getElementById('removeDiscountBtn');
            
            if (discountAmountEl) discountAmountEl.textContent = `- GH₵ ${discountAmount.toFixed(2)}`;
            if (discountRow) discountRow.style.display = 'flex';
            if (totalAmountEl) totalAmountEl.textContent = `GH₵ ${totalAmount.toFixed(2)}`;

            showDiscountMessage(`Discount applied! You save GH₵ ${discountAmount.toFixed(2)}`, 'success');
            codeInput.disabled = true;
            applyBtn.style.display = 'none';
            if (removeBtn) removeBtn.style.display = 'block';
        } else {
            showDiscountMessage(data.message || 'Invalid discount code', 'error');
            applyBtn.disabled = false;
            applyBtn.textContent = 'Apply';
        }
    } catch (error) {
        console.error('Discount validation error:', error);
        showDiscountMessage('Error validating discount code. Please try again.', 'error');
        applyBtn.disabled = false;
        applyBtn.textContent = 'Apply';
    }
}

function removeDiscount() {
    discountAmount = 0;
    appliedDiscountCode = '';
    totalAmount = originalAmount;

    // Reset UI
    const discountAmountEl = document.getElementById('discountAmount');
    const discountRow = document.getElementById('discountRow');
    const totalAmountEl = document.getElementById('totalAmount');
    const codeInput = document.getElementById('discountCode');
    const applyBtn = document.getElementById('applyDiscountBtn');
    const removeBtn = document.getElementById('removeDiscountBtn');
    
    if (discountAmountEl) discountAmountEl.textContent = '- GH₵ 0.00';
    if (discountRow) discountRow.style.display = 'none';
    if (totalAmountEl) totalAmountEl.textContent = `GH₵ ${totalAmount.toFixed(2)}`;
    if (codeInput) {
        codeInput.value = '';
        codeInput.disabled = false;
    }
    if (applyBtn) applyBtn.style.display = 'block';
    if (removeBtn) removeBtn.style.display = 'none';
    showDiscountMessage('', '');
}

async function submitBookingForm(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const alertBox = document.getElementById('alertBox');
    const form = document.getElementById('bookingForm');

    if (!form || !submitBtn || !alertBox) return;

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Initializing payment...';

    // Clear previous alerts
    alertBox.innerHTML = '';

    try {
        const formData = new FormData(form);

        // Collect guest details
        const guestDetails = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            booking_for: formData.get('booking_for'),
            travelling_for_work: formData.get('travelling_for_work') === 'yes',
            special_requests: formData.get('special_requests'),
            arrival_time: formData.get('arrival_time')
        };

        // Prepare payment data
        const paymentData = {
            service_id: serviceId,
            service_date: serviceDate,
            service_time: serviceTime,
            number_of_people: numberOfPeople,
            service_duration: serviceDuration,
            total_amount: totalAmount,
            original_amount: originalAmount,
            discount_code: appliedDiscountCode,
            discount_amount: discountAmount,
            email: formData.get('email'),
            guest_details: guestDetails
        };

        // Initialize Paystack payment
        const response = await fetch('../actions/paystack_init_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(paymentData)
        });

        const result = await response.json();

        if (result.status === 'success' && result.authorization_url) {
            // Redirect to Paystack payment gateway
            window.location.href = result.authorization_url;
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger">${result.message || 'Failed to initialize payment'}</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Proceed to Payment';
            window.scrollTo(0, 0);
        }
    } catch (error) {
        console.error('Payment initialization error:', error);
        alertBox.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Proceed to Payment';
        window.scrollTo(0, 0);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initServiceData();
    
    // Discount code button
    const applyBtn = document.getElementById('applyDiscountBtn');
    if (applyBtn) {
        applyBtn.addEventListener('click', applyDiscount);
    }
    
    // Remove discount button
    const removeBtn = document.getElementById('removeDiscountBtn');
    if (removeBtn) {
        removeBtn.addEventListener('click', removeDiscount);
    }
    
    // Allow Enter key to apply discount
    const codeInput = document.getElementById('discountCode');
    if (codeInput) {
        codeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !this.disabled) {
                e.preventDefault();
                applyDiscount();
            }
        });
    }
    
    // Form submission
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', submitBookingForm);
    }
});

