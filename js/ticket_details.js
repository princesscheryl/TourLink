// Ticket Details Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const replyForm = document.getElementById('replyForm');
    const messageTextarea = replyForm ? replyForm.querySelector('textarea[name="message"]') : null;

    // Auto-resize reply textarea
    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });
    }

    // Handle reply form submission
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            const message = messageTextarea.value.trim();

            if (!message) {
                e.preventDefault();
                Toast.error('Please enter a message');
                return;
            }

            if (message.length < 5) {
                e.preventDefault();
                Toast.error('Message must be at least 5 characters');
                return;
            }
        });
    }

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert-success, .alert-danger');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Scroll to reply form if there's an error
    const errorAlert = document.querySelector('.alert-danger');
    if (errorAlert && replyForm) {
        replyForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

