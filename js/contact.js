// Form submission handler
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formAlert = document.getElementById('formAlert');
    const submitBtn = e.target.querySelector('.btn-submit');

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = window.Translator ? window.Translator.get('form.uploading') : 'Sending...';

    // Collect form data
    const formData = new FormData(e.target);

    try {
        const response = await fetch('../actions/contact_form_action.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            formAlert.className = 'alert alert-success show';
            formAlert.textContent = window.Translator ? window.Translator.get('contact.success_message') : result.message;
            e.target.reset();
        } else {
            formAlert.className = 'alert alert-error show';
            formAlert.textContent = window.Translator ? window.Translator.get('contact.error_message') : result.message;
        }
    } catch (error) {
        formAlert.className = 'alert alert-error show';
        formAlert.textContent = window.Translator ? window.Translator.get('contact.error_message') : 'An error occurred. Please try again.';
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = window.Translator ? window.Translator.get('contact.send_button') : 'Send Message';

        // Scroll to alert
        formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Hide alert after 5 seconds
        setTimeout(() => {
            formAlert.classList.remove('show');
        }, 5000);
    }
});

