function cancelBooking(bookingId) {
    Swal.fire({
        title: 'Cancel Booking?',
        text: 'Are you sure you want to cancel this booking?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel it',
        input: 'textarea',
        inputLabel: 'Reason for cancellation (optional)',
        inputPlaceholder: 'Enter your reason...'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../actions/update_booking_status_action.php',
                method: 'POST',
                data: {
                    booking_id: bookingId,
                    status: 'cancelled',
                    reason: result.value || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Cancelled', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to cancel booking', 'error');
                }
            });
        }
    });
}

