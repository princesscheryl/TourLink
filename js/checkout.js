/**
 * Checkout process JavaScript
 * Handles the checkout flow and simulated payment
 */

// Function to show payment modal
function showPaymentModal() {
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Confirm Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-credit-card fa-4x mb-3 text-primary"></i>
                        <h4>Simulated Payment</h4>
                        <p class="text-muted">This is a simulated payment for demonstration purposes.</p>
                        <p>Have you completed the payment?</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmPaymentBtn">Yes, I've Paid</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('paymentModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Show modal
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();

    // Handle confirm payment button
    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        processCheckout();
        paymentModal.hide();
    });
}

// Function to process checkout
function processCheckout() {
    // Show loading state
    showLoadingModal();

    fetch('../actions/process_checkout_action.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading modal
        hideLoadingModal();

        if (data.success) {
            // Show success modal with order details
            showSuccessModal(data);
        } else {
            // Show error modal
            showErrorModal(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoadingModal();
        showErrorModal('An error occurred while processing your order. Please try again.');
    });
}

// Function to show loading modal
function showLoadingModal() {
    const loadingHTML = `
        <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5>Processing your order...</h5>
                        <p class="text-muted">Please wait</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', loadingHTML);
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
}

// Function to hide loading modal
function hideLoadingModal() {
    const loadingModal = document.getElementById('loadingModal');
    if (loadingModal) {
        const modalInstance = bootstrap.Modal.getInstance(loadingModal);
        if (modalInstance) {
            modalInstance.hide();
        }
        loadingModal.remove();
    }
}

// Function to show success modal
function showSuccessModal(data) {
    const successHTML = `
        <div class="modal fade" id="successModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle me-2"></i>Order Successful!
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h4>Thank You for Your Order!</h4>
                        <p class="text-muted">Your order has been placed successfully.</p>
                        <div class="order-details mt-4">
                            <div class="row mb-2">
                                <div class="col-6 text-end"><strong>Order ID:</strong></div>
                                <div class="col-6 text-start">#${data.order_id}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-end"><strong>Invoice No:</strong></div>
                                <div class="col-6 text-start">${data.invoice_no}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-end"><strong>Total Amount:</strong></div>
                                <div class="col-6 text-start">${data.currency} ${data.total_amount}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-end"><strong>Order Date:</strong></div>
                                <div class="col-6 text-start">${data.order_date}</div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <small>A confirmation email will be sent to your registered email address.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', successHTML);
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
}

// Function to show error modal
function showErrorModal(message) {
    const errorHTML = `
        <div class="modal fade" id="errorModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-circle me-2"></i>Payment Failed
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-times-circle fa-5x text-danger mb-3"></i>
                        <h4>Order Processing Failed</h4>
                        <p class="text-muted">${message}</p>
                        <p>Please try again or contact support if the problem persists.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="showPaymentModal()">Try Again</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', errorHTML);
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
}

// Event listener for checkout button
document.addEventListener('DOMContentLoaded', function() {
    const checkoutBtn = document.getElementById('proceed-to-checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showPaymentModal();
        });
    }
});
