<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign Up - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
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
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* Left Side - Tourism Visual */
        .auth-visual {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
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

        /* Tourism Feature Cards */
        .feature-carousel {
            display: flex;
            gap: 20px;
            animation: slide 20s linear infinite;
        }

        .feature-mini-card {
            min-width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            gap: 12px;
            animation: floatCard 3s infinite ease-in-out;
        }

        .feature-mini-card:nth-child(odd) {
            animation-delay: 0.5s;
        }

        .feature-mini-card i {
            font-size: 3rem;
        }

        .feature-mini-card span {
            font-size: 0.9rem;
            font-weight: 600;
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        @keyframes slide {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
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
            max-width: 500px;
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
            font-size: 1.1rem;
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

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 4px rgba(45, 106, 79, 0.1);
            outline: none;
        }

        .role-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .role-option {
            padding: 16px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option:hover {
            border-color: #74c69d;
            background: #f8f9fa;
        }

        .role-option input[type="radio"]:checked + .role-content {
            color: #2d6a4f;
        }

        .role-option input[type="radio"]:checked ~ {
            border-color: #2d6a4f;
            background: rgba(45, 106, 79, 0.05);
        }

        .role-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .role-content i {
            font-size: 2rem;
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
    <a href="#register-form" class="skip-link">Skip to registration form</a>
    <div class="auth-container">
        <!-- Left Side - Visual -->
        <div class="auth-visual">
            <div class="visual-content">
                <h1 class="visual-title">Start Your Adventure!</h1>
                <p class="visual-subtitle">Join TourLink and discover unforgettable experiences across Ghana's<br>most beautiful destinations</p>

                <div class="feature-carousel">
                    <div class="feature-mini-card">
                        <i class="fa fa-map-marked-alt"></i>
                        <span>Explore Ghana</span>
                    </div>
                    <div class="feature-mini-card">
                        <i class="fa fa-umbrella-beach"></i>
                        <span>Beach Tours</span>
                    </div>
                    <div class="feature-mini-card">
                        <i class="fa fa-mountain"></i>
                        <span>Adventures</span>
                    </div>
                    <div class="feature-mini-card">
                        <i class="fa fa-landmark"></i>
                        <span>Historical Sites</span>
                    </div>
                    <div class="feature-mini-card">
                        <i class="fa fa-map-marked-alt"></i>
                        <span>Explore Ghana</span>
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
                <h2 class="form-title">Create Tourist Account</h2>
                <p class="form-subtitle">Join thousands of travelers exploring Ghana</p>

                <form method="POST" action="" id="register-form" role="form" aria-label="Tourist registration form">
                    <input type="hidden" name="user_type" value="tourist">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">
                                <i class="fa fa-user" aria-hidden="true"></i> First Name
                            </label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   placeholder="John" required aria-required="true">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">
                                <i class="fa fa-user" aria-hidden="true"></i> Last Name
                            </label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   placeholder="Doe" required aria-required="true">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fa fa-envelope" aria-hidden="true"></i> Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="you@example.com" required aria-required="true">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="fa fa-phone" aria-hidden="true"></i> Phone Number (Optional)
                        </label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               placeholder="+233 24 123 4567" aria-describedby="phone-help">
                        <span id="phone-help" class="sr-only">Optional phone number for contact</span>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fa fa-lock" aria-hidden="true"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Minimum 8 characters" required aria-required="true" aria-describedby="password-help">
                        <span id="password-help" class="sr-only">Password must be at least 8 characters long</span>
                    </div>

                    <button type="submit" class="btn-register w-100" aria-label="Create tourist account">
                        <span>Create Tourist Account</span>
                    </button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Sign in here</a>
                    <br><br>
                    <small>Are you a service provider? <a href="register_provider.php">Register as Provider</a></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/accessibility.js"></script>
    <script>
        $(document).ready(function(){
            // Handle form submission
            $('#register-form').submit(function(e){
                e.preventDefault();

                var first_name = $('#first_name').val().trim();
                var last_name = $('#last_name').val().trim();
                var email = $('#email').val().trim();
                var phone = $('#phone').val().trim();
                var password = $('#password').val();
                var user_type = 'tourist'; // Fixed to tourist for this page

                // Validation
                if (first_name === '' || last_name === '' || email === '' || password === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Fields',
                        text: 'Please fill in all required fields!'
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

                if (phone !== '' && !/^[\d\s\+\-\(\)]{7,20}$/.test(phone)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Phone',
                        text: 'Please enter a valid phone number'
                    });
                    return;
                }

                // Submit via AJAX
                $.ajax({
                    url: '../actions/register_user_action.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        first_name: first_name,
                        last_name: last_name,
                        email: email,
                        phone: phone,
                        password: password,
                        user_type: user_type
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
