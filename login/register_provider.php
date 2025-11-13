<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Provider Sign Up - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: #f8f9fa;
        }

        .auth-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* Left Side - Business Visual */
        .auth-visual {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
        }

        .visual-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
        }

        .visual-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 24px;
            animation: slideInRight 0.8s ease-out;
        }

        .visual-subtitle {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 40px;
            animation: slideInRight 1s ease-out;
            line-height: 1.6;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Benefits List */
        .benefits-list {
            max-width: 500px;
            margin: 0 auto;
            text-align: left;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            animation: slideInRight 1.2s ease-out;
        }

        .benefit-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .benefit-icon i {
            font-size: 20px;
        }

        .benefit-text {
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Right Side - Form */
        .auth-form-side {
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            overflow-y: auto;
        }

        .form-container {
            max-width: 550px;
            width: 100%;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-text {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .logo-dot {
            color: #2d6a4f;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .form-subtitle {
            color: #666;
            margin-bottom: 32px;
            font-size: 1.05rem;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            padding: 12px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 4px rgba(45, 106, 79, 0.1);
            outline: none;
        }

        .info-box {
            background: rgba(45, 106, 79, 0.05);
            border-left: 4px solid #2d6a4f;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .info-box p {
            margin: 0;
            font-size: 0.9rem;
            color: #333;
            line-height: 1.6;
        }

        .info-box strong {
            color: #2d6a4f;
        }

        .btn-register {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-register:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(45, 106, 79, 0.4);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            color: #666;
        }

        .auth-footer a {
            color: #2d6a4f;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .back-link {
            color: #666;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 32px;
            font-weight: 600;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #2d6a4f;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .auth-container {
                grid-template-columns: 1fr;
            }

            .auth-visual {
                display: none;
            }

            .auth-form-side {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Visual -->
        <div class="auth-visual">
            <div class="visual-content">
                <h1 class="visual-title">Grow Your Business!</h1>
                <p class="visual-subtitle">Join TourLink as a service provider and reach thousands<br>of travelers across Ghana</p>

                <div class="benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="benefit-text">
                            <strong>Reach More Customers</strong><br>
                            Access to 500K+ active travelers
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <div class="benefit-text">
                            <strong>Boost Your Revenue</strong><br>
                            List your services and get bookings
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fa fa-shield-alt"></i>
                        </div>
                        <div class="benefit-text">
                            <strong>Secure Payments</strong><br>
                            Safe and reliable payment processing
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fa fa-headset"></i>
                        </div>
                        <div class="benefit-text">
                            <strong>24/7 Support</strong><br>
                            Dedicated support team for providers
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-side">
            <div class="form-container">
                <a href="../index_tourlink.php" class="back-link">
                    <i class="fa fa-arrow-left"></i> Back to Home
                </a>

                <div class="logo-text">TourLink<span class="logo-dot">.</span></div>
                <h2 class="form-title">Provider Registration</h2>
                <p class="form-subtitle">Start offering your tourism services today</p>

                <div class="info-box">
                    <p><i class="fa fa-info-circle"></i> <strong>One-time registration fee: GHS 20</strong></p>
                    <p style="margin-top: 8px; font-size: 0.85rem;">Your account will be reviewed and verified within 24-48 hours after payment.</p>
                </div>

                <form method="POST" action="" id="register-provider-form">
                    <input type="hidden" name="user_type" value="provider">

                    <h6 style="font-weight: 700; margin-bottom: 16px; color: #2d6a4f;">Personal Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">
                                <i class="fa fa-user"></i> First Name *
                            </label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">
                                <i class="fa fa-user"></i> Last Name *
                            </label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fa fa-envelope"></i> Email Address *
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="fa fa-phone"></i> Phone Number *
                        </label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="+233 24 123 4567" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fa fa-lock"></i> Password *
                        </label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimum 8 characters" required>
                    </div>

                    <h6 style="font-weight: 700; margin-bottom: 16px; color: #2d6a4f; margin-top: 32px;">Business Information</h6>

                    <div class="mb-3">
                        <label for="business_name" class="form-label">
                            <i class="fa fa-building"></i> Business/Service Name *
                        </label>
                        <input type="text" class="form-control" id="business_name" name="business_name" placeholder="e.g., Accra Tours & Adventures" required>
                    </div>

                    <div class="mb-3">
                        <label for="business_description" class="form-label">
                            <i class="fa fa-align-left"></i> Business Description *
                        </label>
                        <textarea class="form-control" id="business_description" name="business_description" rows="3" placeholder="Brief description of your tourism services..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="business_location" class="form-label">
                            <i class="fa fa-map-marker-alt"></i> Business Location *
                        </label>
                        <input type="text" class="form-control" id="business_location" name="business_location" placeholder="e.g., Accra, Greater Accra Region" required>
                    </div>

                    <div class="mb-4">
                        <label for="primary_region" class="form-label">
                            <i class="fa fa-map"></i> Primary Business Region *
                        </label>
                        <select class="form-select" id="primary_region" name="primary_region" required>
                            <option value="">Select your primary region</option>
                            <option value="Greater Accra">Greater Accra</option>
                            <option value="Central">Central Region</option>
                            <option value="Ashanti">Ashanti Region</option>
                            <option value="Northern">Northern Region</option>
                            <option value="Volta">Volta Region</option>
                            <option value="Eastern">Eastern Region</option>
                            <option value="Western">Western Region</option>
                            <option value="Upper East">Upper East Region</option>
                            <option value="Upper West">Upper West Region</option>
                            <option value="Bono">Bono Region</option>
                        </select>
                        <small class="text-muted">This is your main business location. You can offer services in other regions when creating listings.</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Next Steps:</strong> After registration, you'll be redirected to pay the GHS 20 onboarding fee. Once payment is confirmed, our team will review and verify your account within 24-48 hours.
                    </div>

                    <button type="submit" class="btn-register w-100">
                        <span>Register as Provider</span>
                    </button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Sign in here</a>
                    <br><br>
                    <small>Are you a tourist? <a href="register.php">Register as Tourist</a></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function(){
            // Handle form submission
            $('#register-provider-form').submit(function(e){
                e.preventDefault();

                var first_name = $('#first_name').val().trim();
                var last_name = $('#last_name').val().trim();
                var email = $('#email').val().trim();
                var phone = $('#phone').val().trim();
                var password = $('#password').val();
                var business_name = $('#business_name').val().trim();
                var business_description = $('#business_description').val().trim();
                var business_location = $('#business_location').val().trim();
                var primary_region = $('#primary_region').val();
                var user_type = 'provider';

                // Validation
                if (first_name === '' || last_name === '' || email === '' || phone === '' || password === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Fields',
                        text: 'Please fill in all required personal information!'
                    });
                    return;
                }

                if (business_name === '' || business_description === '' || business_location === '' || primary_region === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Business Info',
                        text: 'Please fill in all required business information!'
                    });
                    return;
                }

                if (!/^[a-zA-Z\s\-']{2,50}$/.test(first_name)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Name',
                        text: 'First name must be 2-50 characters (letters only)'
                    });
                    return;
                }

                if (!/^[a-zA-Z\s\-']{2,50}$/.test(last_name)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Name',
                        text: 'Last name must be 2-50 characters (letters only)'
                    });
                    return;
                }

                var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(email) || email.length > 100) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Email',
                        text: 'Please enter a valid email address'
                    });
                    return;
                }

                if (password.length < 8) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Weak Password',
                        text: 'Password must be at least 8 characters long'
                    });
                    return;
                }

                if (!/^[\d\s\+\-\(\)]{7,20}$/.test(phone)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Phone',
                        text: 'Please enter a valid phone number'
                    });
                    return;
                }

                // Submit via AJAX
                $.ajax({
                    url: '../actions/register_provider_action.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        first_name: first_name,
                        last_name: last_name,
                        email: email,
                        phone: phone,
                        password: password,
                        user_type: user_type,
                        business_name: business_name,
                        business_description: business_description,
                        business_location: business_location,
                        primary_region: primary_region
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message
                            }).then((result) => {
                                if (result.isConfirmed && response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Registration Failed',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Registration error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: 'Unable to connect to server. Please try again.'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
