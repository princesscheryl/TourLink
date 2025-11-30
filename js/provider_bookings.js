/**
 * Provider Bookings Management
 * Handles booking status updates
 */

function updateStatus(bookingId, status) {
    let title, text, confirmText;

    switch(status) {
        case 'confirmed':
            title = 'Approve Booking?';
            text = 'This will confirm the booking and notify the customer.';
            confirmText = 'Yes, approve it';
            break;
        case 'in_progress':
            title = 'Start Service?';
            text = 'This will mark the service as in progress.';
            confirmText = 'Yes, start it';
            break;
        case 'completed':
            title = 'Complete Booking?';
            text = 'This will mark the booking as completed.';
            confirmText = 'Yes, complete it';
            break;
        default:
            return;
    }

    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0f766e',
        cancelButtonColor: '#64748b',
        confirmButtonText: confirmText
    }).then((result) => {
        if (result.isConfirmed) {
            submitStatusUpdate(bookingId, status);
        }
    });
}

function rejectBooking(bookingId) {
    Swal.fire({
        title: 'Reject Booking?',
        text: 'Please provide a reason for rejection:',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Reason for rejection...',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Reject Booking',
        inputValidator: (value) => {
            if (!value) return 'Please provide a reason';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitStatusUpdate(bookingId, 'rejected', result.value);
        }
    });
}

function cancelBooking(bookingId) {
    Swal.fire({
        title: 'Cancel Booking?',
        text: 'Are you sure you want to cancel this booking?',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Reason for cancellation',
        inputPlaceholder: 'Enter reason...',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, cancel it'
    }).then((result) => {
        if (result.isConfirmed) {
            submitStatusUpdate(bookingId, 'cancelled', result.value || 'Cancelled by provider');
        }
    });
}

function submitStatusUpdate(bookingId, status, reason = '') {
    $.ajax({
        url: '../../actions/update_booking_status_action.php',
        method: 'POST',
        data: { booking_id: bookingId, status: status, reason: reason },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    confirmButtonColor: '#0f766e'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonColor: '#0f766e'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.',
                confirmButtonColor: '#0f766e'
            });
        }
    });
}

