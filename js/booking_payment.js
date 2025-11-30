// Note: bookingId, originalAmount should be set in the HTML via PHP
let discountAmount = 0;
let appliedDiscountCode = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('applyDiscountBtn')) {
        document.getElementById('applyDiscountBtn').addEventListener('click', applyDiscount);
    }
    if (document.getElementById('discountCode')) {
        document.getElementById('discountCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyDiscount();
            }
        });
    }
    if (document.getElementById('payButton')) {
        document.getElementById('payButton').addEventListener('click', initializePayment);
    }
});

async function applyDiscount() {
    const code = document.getElementById('discountCode').value.trim().toUpperCase();
    const messageEl = document.getElementById('discountMessage');
    const applyBtn = document.getElementById('applyDiscountBtn');

    if (!code) {
        showDiscountMessage('Please enter a discount code', 'error');
        return;
    }

    if (!window.bookingId || !window.originalAmount) {
        console.error('Booking data not initialized');
        return;
    }

    applyBtn.disabled = true;
    applyBtn.textContent = 'Checking...';

    try {
        const response = await fetch('../actions/validate_discount.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                booking_id: window.bookingId,
                discount_code: code,
                amount: window.originalAmount
            })
        });

        const data = await response.json();

        if (data.status === 'success') {
            discountAmount = parseFloat(data.discount_amount);
            appliedDiscountCode = code;

            document.getElementById('discountAmount').textContent = `- GH₵ ${discountAmount.toFixed(2)}`;
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('totalAmount').textContent = `GH₵ ${data.final_amount}`;

            showDiscountMessage(`Discount applied! You save GH₵ ${discountAmount.toFixed(2)}`, 'success');
            document.getElementById('discountCode').disabled = true;
            applyBtn.style.display = 'none';
        } else {
            showDiscountMessage(data.message || 'Invalid discount code', 'error');
        }

    } catch (error) {
        console.error('Discount validation error:', error);
        showDiscountMessage('Failed to validate discount code', 'error');
    } finally {
        applyBtn.disabled = false;
        applyBtn.textContent = 'Apply';
    }
}

function showDiscountMessage(message, type) {
    const messageEl = document.getElementById('discountMessage');
    messageEl.textContent = message;
    messageEl.className = `discount-message ${type}`;
}

async function initializePayment() {
    if (!window.userEmail) {
        alert('Email not found in session. Please logout and login again.');
        return;
    }

    if (!window.bookingId) {
        alert('Booking ID not found.');
        return;
    }

    const payBtn = document.getElementById('payButton');
    payBtn.disabled = true;
    payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    try {
        const response = await fetch('../actions/paystack_init_transaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                booking_id: window.bookingId,
                email: window.userEmail,
                discount_code: appliedDiscountCode
            })
        });

        const data = await response.json();

        if (data.status === 'success' && data.authorization_url) {
            window.location.href = data.authorization_url;
        } else {
            alert(data.message || 'Failed to initialize payment');
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="fas fa-lock"></i> Proceed to Payment';
        }

    } catch (error) {
        console.error('Payment initialization error:', error);
        alert('Connection error. Please try again.');
        payBtn.disabled = false;
        payBtn.innerHTML = '<i class="fas fa-lock"></i> Proceed to Payment';
    }
}
