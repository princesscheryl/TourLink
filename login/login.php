<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign In - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../css/dark-mode.css" rel="stylesheet">
    <link href="../css/accessibility.css" rel="stylesheet">
    <script src="../js/dark-mode.js"></script>
    <script src="../js/accessibility.js"></script>
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

        /* Left Side - Welcome Visual */
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
            animation: slideInLeft 0.8s ease-out;
        }

        .visual-subtitle {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 40px;
            animation: slideInLeft 1s ease-out;
            line-height: 1.6;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Features List */
        .features-list {
            max-width: 500px;
            margin: 0 auto;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            animation: slideInLeft 1.2s ease-out;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-icon i {
            font-size: 20px;
        }

        .feature-text {
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
            max-width: 450px;
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

        .forgot-password {
            text-align: right;
            margin-bottom: 24px;
        }

        .forgot-password a {
            color: #2d6a4f;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn-login {
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

        .btn-login::before {
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

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover {
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

        /* Dark Mode for language/theme controls */
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

        /* Header controls container */
        .header-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .language-selector {
                min-width: 80px;
                font-size: 0.8rem;
                padding: 6px 8px;
            }

            .theme-toggle-btn {
                width: 36px;
                height: 36px;
            }

            .header-controls {
                top: 15px;
                right: 15px;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .language-selector {
                min-width: 70px;
                font-size: 0.75rem;
                padding: 5px 6px;
            }

            .theme-toggle-btn {
                width: 32px;
                height: 32px;
            }

            .header-controls {
                top: 10px;
                right: 10px;
            }
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
    <a href="#login-form" class="skip-link">Skip to login form</a>

    <!-- Header Controls -->
    <div class="header-controls">

        <!-- Theme Toggle -->
        <button onclick="toggleTheme()" class="theme-toggle-btn" id="publicThemeToggle" aria-label="Toggle dark mode">
            <i class="fa fa-moon"></i>
        </button>
    </div>

    <div class="auth-container">
        <!-- Left Side - Visual -->
        <div class="auth-visual">
            <div class="visual-content">
                <h1 class="visual-title">Welcome Back!</h1>
                <p class="visual-subtitle">Sign in to continue your adventure with TourLink<br>and explore beautiful Ghana</p>

                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa fa-map-marked-alt"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Discover Amazing Places</strong><br>
                            Access exclusive tours across Ghana
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa fa-bookmark"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Manage Your Bookings</strong><br>
                            Track and manage all your reservations
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa fa-star"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Exclusive Deals</strong><br>
                            Get access to member-only discounts
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-side">
            <div class="form-container">
                <a href="../index_tourlink.php" class="back-link">
                    <i class="fa fa-arrow-left"></i> <span data-i18n="nav.back_to_home">Back to Home</span>
                </a>

                <div class="logo-text">TourLink<span class="logo-dot">.</span></div>
                <h2 class="form-title" data-i18n="auth.sign_in_title">Sign In</h2>
                <p class="form-subtitle" data-i18n="auth.sign_in_subtitle">Enter your credentials to access your account</p>

                <form method="POST" action="" id="login-form" role="form" aria-label="Sign in form">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fa fa-envelope" aria-hidden="true"></i> <span data-i18n="auth.email">Email Address</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required aria-required="true" aria-describedby="email-help">
                        <span id="email-help" class="sr-only">Enter your email address to sign in</span>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fa fa-lock" aria-hidden="true"></i> <span data-i18n="auth.password">Password</span>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required aria-required="true" aria-describedby="password-help">
                        <span id="password-help" class="sr-only">Enter your password to sign in</span>
                    </div>

                    <div class="forgot-password">
                        <a href="#forgot" data-i18n="auth.forgot_password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-login w-100" aria-label="Sign in to your account">
                        <span data-i18n="auth.sign_in">Sign In</span>
                    </button>
                </form>

                <div class="auth-footer">
                    <span data-i18n="auth.no_account">Don't have an account?</span> <a href="register.php" data-i18n="auth.sign_up_tourist">Sign up as Tourist</a>
                    <br><br>
                    <small><span data-i18n="auth.are_you_provider">Are you a service provider?</span> <a href="register_provider.php" data-i18n="auth.register_provider">Register as Provider</a></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/toast.js"></script>
    <script src="../js/accessibility.js"></script>
    <script>
        $(document).ready(function(){
            // Check for session timeout
            <?php if(isset($_SESSION['timeout_message'])): ?>
                Toast.warning('<?php echo $_SESSION['timeout_message']; ?>', 'Session Expired');
                <?php unset($_SESSION['timeout_message']); ?>
            <?php endif; ?>

            // Check for timeout URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('timeout') === '1') {
                Toast.warning('Your session has expired due to inactivity. Please sign in again.', 'Session Expired');
                // Remove timeout parameter from URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            $('#login-form').submit(function(e){
                e.preventDefault();

                var email = $('#email').val().trim();
                var password = $('#password').val();

                // Validation
                if (email === '' || password === '') {
                    Toast.error('Please enter both email and password!', 'Missing Fields');
                    return;
                }

                var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(email)) {
                    Toast.error('Please enter a valid email address', 'Invalid Email');
                    return;
                }

                // Submit via AJAX
                $.ajax({
                    url: '../actions/login_action.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        email: email,
                        password: password
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Toast.success(response.message, 'Welcome Back!');
                            setTimeout(function() {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            }, 1000);
                        } else {
                            Toast.error(response.message, 'Login Failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Login error:', error);
                        Toast.error('Unable to connect to server. Please try again.', 'Connection Error');
                    }
                });
            });
        });
    </script>
</body>
</html>
