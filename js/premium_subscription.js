/**
 * Premium Subscription Page JavaScript
 * Handles subscription management and cancellation
 */

function subscribePremium(event) {
    // Get the button element from the event
    const btn = event ? event.target : null;
    
    // Disable button and show loading state if we have the button
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    // Redirect to payment initialization
    window.location.href = '../actions/initiate_premium_subscription.php';
}

function showErrorMessage(message) {
    const errorDiv = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    if (errorDiv && errorText) {
        errorText.textContent = message;
        errorDiv.style.display = 'flex';
        
        // Auto-hide after 8 seconds
        setTimeout(() => {
            closeErrorMessage();
        }, 8000);
    }
}

function showSuccessMessage(message) {
    const successDiv = document.getElementById('successMessage');
    const successText = document.getElementById('successText');
    if (successDiv && successText) {
        successText.textContent = message;
        successDiv.style.display = 'flex';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            closeSuccessMessage();
        }, 5000);
    }
}

function closeErrorMessage() {
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function closeSuccessMessage() {
    const successDiv = document.getElementById('successMessage');
    if (successDiv) {
        successDiv.style.display = 'none';
    }
}

function cancelSubscription(event) {
    if (confirm('Are you sure you want to cancel your premium subscription?\n\nYour benefits will continue until the end of the current billing period.')) {
        // Disable button and show loading
        const btn = event ? event.target : null;
        let originalText = '';
        if (btn) {
            originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
        }
        
        // Hide any previous messages
        closeErrorMessage();
        closeSuccessMessage();
        
        fetch('../actions/cancel_premium_subscription.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            // Try to parse JSON even if response is not ok
            return response.text().then(text => {
                try {
                    return { ok: response.ok, data: JSON.parse(text), status: response.status };
                } catch (e) {
                    // If JSON parsing fails, return the text as error
                    return { 
                        ok: false, 
                        data: { 
                            success: false, 
                            message: 'Server returned an invalid response. Status: ' + response.status + '. Response: ' + text.substring(0, 200)
                        },
                        status: response.status
                    };
                }
            });
        })
        .then(result => {
            if (result.ok && result.data.success) {
                showSuccessMessage('Subscription cancelled successfully. Your benefits will continue until the end of the current billing period.');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                // Show detailed error message
                let errorMsg = result.data.message || 'Failed to cancel subscription';
                if (result.data.error) {
                    errorMsg += ' (Error: ' + result.data.error + ')';
                }
                if (result.status && result.status !== 200) {
                    errorMsg += ' [HTTP ' + result.status + ']';
                }
                showErrorMessage(errorMsg);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            }
        })
        .catch(error => {
            console.error('Error cancelling subscription:', error);
            showErrorMessage('Network error: ' + error.message + '. Please check your internet connection and try again.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }
}

// Initialize on page load - check for URL error parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error) {
        let errorMessage = 'An error occurred';
        switch(error) {
            case 'already_subscribed':
                errorMessage = 'You already have an active premium subscription.';
                break;
            case 'payment_failed':
                errorMessage = 'Payment processing failed. Please try again.';
                break;
            default:
                errorMessage = decodeURIComponent(error);
        }
        showErrorMessage(errorMessage);
    }
});

