/**
 * Review Submission Handler
 * Handles tourist review form submission with AJAX
 */

// Prevent multiple initializations
if (typeof window.reviewSubmitInitialized === 'undefined') {
    window.reviewSubmitInitialized = true;
    
    $(document).ready(function() {
        // Handle review form submission
        const reviewForm = document.querySelector('form[action*="submit_review_action"]');
        
        if (reviewForm) {
            $(reviewForm).on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const formData = new FormData(this);
                
                // Get form values directly for better performance
                const serviceId = formData.get('service_id');
                const bookingId = formData.get('booking_id');
                const rating = formData.get('rating');
                const reviewTitle = formData.get('review_title');
                const reviewText = formData.get('review_text');
                
                // Validate
                if (!serviceId || !bookingId || !rating || !reviewTitle || !reviewText) {
                    if (typeof Toast !== 'undefined') {
                        Toast.error('Please fill in all required fields and select a rating', 'Error');
                    } else {
                        alert('Please fill in all required fields and select a rating');
                    }
                    return;
                }
                
                // Disable submit button
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');
                
                // Submit via AJAX
                $.ajax({
                    url: '../actions/submit_review_action.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            if (typeof Toast !== 'undefined') {
                                Toast.success(response.message || 'Thank you for your review!', 'Success');
                            }
                            // Reload after a short delay to show the toast
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            if (typeof Toast !== 'undefined') {
                                Toast.error(response.message || 'Failed to submit review. Please try again.', 'Error');
                            } else {
                                alert(response.message || 'Failed to submit review. Please try again.');
                            }
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Try to parse error response
                        let errorMessage = 'An error occurred. Please try again.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            // If not JSON, check for redirect or use default message
                            if (xhr.status === 200) {
                                // Might be a redirect, reload page
                                location.reload();
                                return;
                            }
                        }
                        
                        if (typeof Toast !== 'undefined') {
                            Toast.error(errorMessage, 'Error');
                        } else {
                            alert(errorMessage);
                        }
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        }
    });
}

