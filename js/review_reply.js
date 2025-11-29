/**
 * Review Reply Handler
 * Shared JavaScript for handling provider replies to reviews
 * Used in: manage_reviews.php, single_service.php
 */

$(document).ready(function() {
    $('.reply-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const reviewId = form.data('review-id');
        const serviceId = form.data('service-id');
        const responseText = form.find('textarea[name="provider_response"]').val().trim();
        
        // Fallback if textarea selector doesn't work
        if (!responseText) {
            responseText = form.find('textarea').val().trim();
        }
        
        if (!responseText) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please enter a response',
                confirmButtonColor: '#0f766e'
            });
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Your response has been posted successfully',
                        confirmButtonColor: '#0f766e',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to post response. Please try again.',
                        confirmButtonColor: '#0f766e'
                    });
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#0f766e'
                });
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});

