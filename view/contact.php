<?php
require_once '../settings/core.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title data-i18n="contact.title">Contact Us | TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/translator.js"></script>
    <script src="../js/accessibility.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #1a1a1a;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        /* Navigation */
        .main-nav {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2d6a4f;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-menu a {
            text-decoration: none;
            color: #1a1a1a;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-menu a:hover {
            color: #2d6a4f;
        }

        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .language-selector {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #1a1a1a;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 100px;
        }

        .language-selector:hover {
            border-color: #2d6a4f;
        }

        .theme-toggle-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            color: #1a1a1a;
        }

        .theme-toggle-btn:hover {
            border-color: #2d6a4f;
            background: #f0f7f4;
        }

        .btn-sign-in {
            padding: 10px 24px;
            background: transparent;
            color: #2d6a4f;
            border: 2px solid #2d6a4f;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-sign-in:hover {
            background: #2d6a4f;
            color: white;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            padding: 80px 0 60px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Contact Section */
        .contact-section {
            max-width: 1400px;
            margin: -40px auto 60px;
            padding: 0 40px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }

        .contact-form-container,
        .contact-info-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .contact-form-container h2,
        .contact-info-container h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .btn-submit {
            background: #2d6a4f;
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Contact Info */
        .info-item {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            align-items: flex-start;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: #f0f7f4;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2d6a4f;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .info-content h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .info-content p {
            color: #666;
            margin: 0;
            line-height: 1.6;
        }

        .info-content a {
            color: #2d6a4f;
            text-decoration: none;
        }

        .info-content a:hover {
            text-decoration: underline;
        }

        .business-hours {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }

        .business-hours h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .hours-list {
            list-style: none;
        }

        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: #666;
        }

        .hours-list li strong {
            color: #1a1a1a;
        }

        /* Map Section */
        .map-section {
            max-width: 1400px;
            margin: 0 auto 60px;
            padding: 0 40px;
        }

        .map-container {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .map-container h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .map-embed {
            width: 100%;
            height: 400px;
            border: none;
            border-radius: 12px;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            text-align: center;
        }

        .footer p {
            margin: 10px 0;
            opacity: 0.8;
        }

        /* Dark Mode Styles */
        [data-theme="dark"] body {
            background: #1a1a1a !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .main-nav {
            background: #2d2d2d !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3) !important;
        }

        [data-theme="dark"] .logo {
            color: #52b788 !important;
        }

        [data-theme="dark"] .nav-menu a {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .nav-menu a:hover {
            color: #52b788 !important;
        }

        [data-theme="dark"] .language-selector {
            background: #3d3d3d !important;
            border-color: #505050 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .theme-toggle-btn {
            background: #3d3d3d !important;
            border-color: #505050 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .theme-toggle-btn:hover {
            background: #4d4d4d !important;
        }

        [data-theme="dark"] .btn-sign-in {
            color: #52b788 !important;
            border-color: #52b788 !important;
        }

        [data-theme="dark"] .btn-sign-in:hover {
            background: #52b788 !important;
            color: #1a1a1a !important;
        }

        [data-theme="dark"] .contact-form-container,
        [data-theme="dark"] .contact-info-container,
        [data-theme="dark"] .map-container {
            background: #2d2d2d !important;
        }

        [data-theme="dark"] .contact-form-container h2,
        [data-theme="dark"] .contact-info-container h2,
        [data-theme="dark"] .map-container h2,
        [data-theme="dark"] .info-content h3 {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .form-group label {
            color: #d0d0d0 !important;
        }

        [data-theme="dark"] .form-group input,
        [data-theme="dark"] .form-group textarea {
            background: #3d3d3d !important;
            border-color: #505050 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .info-icon {
            background: #3d3d3d !important;
            color: #52b788 !important;
        }

        [data-theme="dark"] .info-content p {
            color: #b0b0b0 !important;
        }

        [data-theme="dark"] .info-content a {
            color: #52b788 !important;
        }

        [data-theme="dark"] .hours-list li {
            color: #b0b0b0 !important;
        }

        [data-theme="dark"] .hours-list li strong {
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .business-hours {
            border-top-color: #404040 !important;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .nav-container {
                padding: 0 20px;
            }

            .nav-menu {
                display: none;
            }

            .nav-actions {
                gap: 8px;
            }

            .language-selector {
                min-width: 80px;
                font-size: 0.8rem;
                padding: 6px 8px;
            }

            .theme-toggle-btn {
                width: 36px;
                height: 36px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .page-header p {
                font-size: 1rem;
            }

            .contact-section,
            .map-section {
                padding: 0 20px;
            }

            .contact-form-container,
            .contact-info-container,
            .map-container {
                padding: 25px;
            }

            .map-embed {
                height: 300px;
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 1.4rem;
            }

            .language-selector {
                min-width: 70px;
                font-size: 0.75rem;
                padding: 5px 6px;
            }

            .theme-toggle-btn {
                width: 32px;
                height: 32px;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .contact-form-container h2,
            .contact-info-container h2,
            .map-container h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/navigation.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <h1 data-i18n="contact.title">Contact Us</h1>
        <p data-i18n="contact.subtitle">We'd love to hear from you</p>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2 data-i18n="contact.form_title">Send us a Message</h2>

                <div id="formAlert" class="alert"></div>

                <form id="contactForm" method="POST" action="../actions/contact_form_action.php">
                    <div class="form-group">
                        <label for="name" data-i18n="contact.name">Your Name</label>
                        <input type="text" id="name" name="name" required data-i18n-placeholder="contact.name">
                    </div>

                    <div class="form-group">
                        <label for="email" data-i18n="contact.email">Your Email</label>
                        <input type="email" id="email" name="email" required data-i18n-placeholder="contact.email">
                    </div>

                    <div class="form-group">
                        <label for="subject" data-i18n="contact.subject">Subject</label>
                        <input type="text" id="subject" name="subject" required data-i18n-placeholder="contact.subject">
                    </div>

                    <div class="form-group">
                        <label for="message" data-i18n="contact.message">Message</label>
                        <textarea id="message" name="message" required data-i18n-placeholder="contact.message"></textarea>
                    </div>

                    <button type="submit" class="btn-submit" data-i18n="contact.send_button">Send Message</button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info-container">
                <h2 data-i18n="contact.contact_info">Contact Information</h2>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3 data-i18n="contact.location_title">Location</h3>
                        <p>Ashesi University, Berekuso<br>Ghana</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3 data-i18n="contact.email_title">Email</h3>
                        <p><a href="mailto:info@tourlink.com.gh" data-i18n="footer.email">info@tourlink.com.gh</a></p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h3 data-i18n="contact.phone_title">Phone</h3>
                        <p><a href="tel:+233501234567" data-i18n="footer.phone">+233 50 123 4567</a></p>
                    </div>
                </div>

                <div class="business-hours">
                    <h3 data-i18n="contact.business_hours">Business Hours</h3>
                    <ul class="hours-list">
                        <li>
                            <strong data-i18n="contact.monday_friday">Monday - Friday:</strong>
                            <span>8:00 AM - 6:00 PM</span>
                        </li>
                        <li>
                            <strong data-i18n="contact.saturday">Saturday:</strong>
                            <span>9:00 AM - 4:00 PM</span>
                        </li>
                        <li>
                            <strong data-i18n="contact.sunday">Sunday:</strong>
                            <span data-i18n="contact.closed">Closed</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="map-container">
            <h2 data-i18n="contact.find_us">Find Us</h2>
            <iframe
                class="map-embed"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.0726826634986!2d-0.13847368577515385!3d5.759088595753134!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdf9c7ebaeabe93%3A0x794е558f6a3!2sAshesi%20University!5e0!3m2!1sen!2sgh!4v1629814567890!5m2!1sen!2sgh"
                allowfullscreen=""
                loading="lazy">
            </iframe>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2025 <span data-i18n="app_name">TourLink</span>. <span data-i18n="footer.copyright">All rights reserved.</span></p>
        </div>
    </footer>

    <script>
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
    </script>
</body>
</html>
