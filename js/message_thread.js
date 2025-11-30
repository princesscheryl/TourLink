// Message Thread JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('messageForm');
    const messageText = document.getElementById('messageText');
    const sendBtn = document.getElementById('sendBtn');
    const messagesArea = document.getElementById('messagesArea');

    if (!messageForm || !messageText || !sendBtn) {
        return;
    }

    // Auto-resize textarea
    messageText.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Scroll to bottom on load
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    // Handle form submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const text = messageText.value.trim();
        if (!text) {
            return;
        }

        // Disable send button
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Send message via AJAX
        const formData = new FormData();
        formData.append('receiver_id', window.receiverId);
        formData.append('message_text', text);
        if (window.serviceId) {
            formData.append('service_id', window.serviceId);
        }
        if (window.bookingId) {
            formData.append('booking_id', window.bookingId);
        }

        fetch('../actions/send_message_action.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                messageText.value = '';
                messageText.style.height = 'auto';

                // Reload page to show new message
                // In a real-time system, you'd append the message to the DOM
                window.location.reload();
            } else {
                Toast.error(data.message || 'Failed to send message');
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.error('An error occurred. Please try again.');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
    });

    // Allow Enter to send (Shift+Enter for new line)
    messageText.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });
});

