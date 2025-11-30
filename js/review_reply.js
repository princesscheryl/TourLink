/**
 * Review Reply Handler
 * Shared JavaScript for handling provider replies to reviews
 * Used in: manage_reviews.php, single_service.php
 */

// Prevent multiple initializations
if (typeof window.reviewReplyInitialized === 'undefined') {
    window.reviewReplyInitialized = true;
    
    // Use event delegation to prevent multiple listeners and improve performance
    $(document).ready(function() {
        // Remove any existing listeners first to prevent duplicates
        $(document).off('submit', '.reply-form');
        
        // Use event delegation - attach listener to document, not individual forms
        $(document).on('submit', '.reply-form', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const reviewId = form.data('review-id');
            const serviceId = form.data('service-id');
            
            // Get textarea value directly without jQuery traversal to improve performance
            const textarea = form.find('textarea[name="provider_response"]')[0] || form.find('textarea')[0];
            const responseText = textarea ? textarea.value.trim() : '';
        
        if (!responseText) {
            if (typeof Toast !== 'undefined') {
                Toast.error('Please enter a response', 'Error');
            } else {
                alert('Please enter a response');
            }
            return;
        }

        // Disable form
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true);
        
        // Update button text based on context
        if (submitBtn.find('i').hasClass('fa-paper-plane')) {
            submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Posting...');
        } else {
            submitBtn.text('Posting...');
        }

        // Determine the correct URL based on current page location
        let actionUrl = '../actions/respond_to_review_action.php';
        if (window.location.pathname.includes('/view/provider/')) {
            actionUrl = '../../actions/respond_to_review_action.php';
        } else if (window.location.pathname.includes('/view/')) {
            actionUrl = '../actions/respond_to_review_action.php';
        }

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: {
                review_id: reviewId,
                service_id: serviceId,
                provider_response: responseText
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (typeof Toast !== 'undefined') {
                        Toast.success(response.message || 'Your response has been posted successfully', 'Success');
                    }
                    // Reload after a short delay to show the toast
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    if (typeof Toast !== 'undefined') {
                        Toast.error(response.message || 'Failed to post response. Please try again.', 'Error');
                    } else {
                        alert(response.message || 'Failed to post response. Please try again.');
                    }
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                if (typeof Toast !== 'undefined') {
                    Toast.error('An error occurred. Please try again.', 'Error');
                } else {
                    alert('An error occurred. Please try again.');
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    });
}
