<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Shopify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            left: -250px;
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
            right: -200px;
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
            animation: slideInLeft 0.8s ease-out;
        }

        .visual-subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            margin-bottom: 40px;
            animation: slideInLeft 1s ease-out;
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

        /* Animated Icons Grid */
        .floating-icons {
            display: grid;
            grid-template-columns: repeat(3, 100px);
            gap: 24px;
            margin-top: 40px;
        }

        .icon-box {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            animation: floatIcon 3s infinite ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .icon-box:nth-child(1) { animation-delay: 0s; }
        .icon-box:nth-child(2) { animation-delay: 0.3s; }
        .icon-box:nth-child(3) { animation-delay: 0.6s; }
        .icon-box:nth-child(4) { animation-delay: 0.9s; }
        .icon-box:nth-child(5) { animation-delay: 1.2s; }
        .icon-box:nth-child(6) { animation-delay: 1.5s; }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Right Side - Form */
        .auth-form-side {
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
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
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            padding: 14px 18px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(103, 146, 103, 0.1);
            outline: none;
        }

        .btn-login {
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

        /* Loading animation */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Visual -->
        <div class="auth-visual">
            <div class="visual-content">
                <h1 class="visual-title">Welcome Back!</h1>
                <p class="visual-subtitle">Sign in to continue shopping amazing products</p>

                <div class="floating-icons">
                    <div class="icon-box"><i class="fa fa-shopping-bag"></i></div>
                    <div class="icon-box"><i class="fa fa-heart"></i></div>
                    <div class="icon-box"><i class="fa fa-star"></i></div>
                    <div class="icon-box"><i class="fa fa-gift"></i></div>
                    <div class="icon-box"><i class="fa fa-tag"></i></div>
                    <div class="icon-box"><i class="fa fa-rocket"></i></div>
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
                <h2 class="form-title">Sign In</h2>
                <p class="form-subtitle">Enter your credentials to access your account</p>

                <form method="POST" action="" id="login-form">
                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="fa fa-envelope"></i> Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="you@example.com" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fa fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-login w-100">
                        <span>Sign In</span>
                    </button>
                </form>

                <div class="auth-footer">
                    Don't have an account? <a href="register.php">Create one now</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/login.js"></script>
</body>
</html>
