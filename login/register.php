<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register - Shopify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/country-select-js/2.1.1/css/countrySelect.min.css">
    <link href="../css/index.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: var(--gray-50);
        }

        .auth-container {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* Left Side - Animated Illustrations */
        .auth-visual {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .auth-visual::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 20s infinite ease-in-out;
        }

        .auth-visual::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: float 15s infinite ease-in-out reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 50px) scale(1.1); }
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
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 40px;
            animation: slideInRight 1s ease-out;
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

        /* Animated Product Cards */
        .product-carousel {
            display: flex;
            gap: 20px;
            animation: slide 20s linear infinite;
        }

        .product-mini-card {
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

        .product-mini-card:nth-child(odd) {
            animation-delay: 0.5s;
        }

        .product-mini-card i {
            font-size: 3rem;
        }

        .product-mini-card span {
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
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .logo-dot {
            color: var(--primary);
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .form-subtitle {
            color: var(--gray-600);
            margin-bottom: 32px;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(103, 146, 103, 0.1);
            outline: none;
        }

        .role-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .role-option {
            padding: 16px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option:hover {
            border-color: var(--primary-light);
            background: var(--gray-50);
        }

        .role-option input[type="radio"]:checked + .role-content {
            color: var(--primary);
        }

        .role-option input[type="radio"]:checked ~ {
            border-color: var(--primary);
            background: rgba(103, 146, 103, 0.05);
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(103, 146, 103, 0.3);
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
            box-shadow: 0 8px 24px rgba(103, 146, 103, 0.4);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            color: var(--gray-600);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .back-link {
            color: var(--gray-600);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 32px;
            font-weight: 600;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--primary);
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
                <h1 class="visual-title">Join Our Community!</h1>
                <p class="visual-subtitle">Create an account and start your shopping journey</p>

                <div class="product-carousel">
                    <div class="product-mini-card">
                        <i class="fa fa-laptop"></i>
                        <span>Electronics</span>
                    </div>
                    <div class="product-mini-card">
                        <i class="fa fa-tshirt"></i>
                        <span>Fashion</span>
                    </div>
                    <div class="product-mini-card">
                        <i class="fa fa-home"></i>
                        <span>Home Decor</span>
                    </div>
                    <div class="product-mini-card">
                        <i class="fa fa-laptop"></i>
                        <span>Electronics</span>
                    </div>
                    <div class="product-mini-card">
                        <i class="fa fa-tshirt"></i>
                        <span>Fashion</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-side">
            <div class="form-container">
                <a href="../index.php" class="back-link">
                    <i class="fa fa-arrow-left"></i> Back to Home
                </a>

                <div class="logo-text">shopify<span class="logo-dot">.</span></div>
                <h2 class="form-title">Create Account</h2>
                <p class="form-subtitle">Join thousands of happy shoppers today</p>

                <form method="POST" action="" id="register-form">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">
                            <i class="fa fa-user"></i> Full Name
                        </label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                               placeholder="John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fa fa-envelope"></i> Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="you@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fa fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Create a strong password" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">
                                <i class="fa fa-flag"></i> Country
                            </label>
                            <input type="text" class="form-control" id="country" name="country"
                                   placeholder="Your country" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">
                                <i class="fa fa-city"></i> City
                            </label>
                            <input type="text" class="form-control" id="city" name="city"
                                   placeholder="Your city" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="contact" class="form-label">
                            <i class="fa fa-phone"></i> Phone Number
                        </label>
                        <input type="text" class="form-control" id="contact" name="contact"
                               placeholder="e.g., 0241234567" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fa fa-id-badge"></i> Register As
                        </label>
                        <div class="role-selector">
                            <label class="role-option">
                                <input type="radio" name="role" value="2" checked>
                                <div class="role-content">
                                    <i class="fa fa-shopping-cart"></i>
                                    <span>Customer</span>
                                </div>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="role" value="1">
                                <div class="role-content">
                                    <i class="fa fa-store"></i>
                                    <span>Business Owner</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-register w-100">
                        <span>Create Account</span>
                    </button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/country-select-js/2.1.1/js/countrySelect.min.js"></script>
    <script src="../js/register.js"></script>
    <script>
        // Make role selector more interactive
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => {
                    opt.style.borderColor = 'var(--gray-300)';
                    opt.style.background = 'white';
                });
                this.style.borderColor = 'var(--primary)';
                this.style.background = 'rgba(103, 146, 103, 0.05)';
            });
        });

        // Set initial active state
        document.querySelector('.role-option input[type="radio"]:checked').parentElement.click();
    </script>
</body>
</html>
