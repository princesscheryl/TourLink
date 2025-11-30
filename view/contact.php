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
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <link href="../css/contact.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/accessibility.js"></script>
    <script src="../js/contact.js"></script>
</head>
<body>
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
    <?php include '../includes/footer.php'; ?>

</body>
</html>
