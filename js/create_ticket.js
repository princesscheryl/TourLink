// Create Ticket Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const ticketForm = document.getElementById('ticketForm');
    const descriptionTextarea = document.getElementById('description');
    const categorySelect = document.getElementById('category');

    if (!ticketForm) {
        return;
    }

    // Auto-resize description textarea
    if (descriptionTextarea) {
        descriptionTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 300) + 'px';
        });
    }

    // Form submission handling
    ticketForm.addEventListener('submit', function(e) {
        const subject = document.getElementById('subject').value.trim();
        const description = descriptionTextarea.value.trim();
        const category = categorySelect.value;

        // Client-side validation
        if (!subject) {
            e.preventDefault();
            Toast.error('Please enter a subject for your ticket');
            return;
        }

        if (!category) {
            e.preventDefault();
            Toast.error('Please select a category');
            return;
        }

        if (!description || description.length < 10) {
            e.preventDefault();
            Toast.error('Please provide a detailed description (at least 10 characters)');
            return;
        }
    });
});

