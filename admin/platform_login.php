<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: platform_dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../classes/admin_class.php';

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $admin = new Admin();
        $result = $admin->admin_login($email, $password);

        if ($result) {
            $_SESSION['admin_id'] = $result['admin_id'];
            $_SESSION['admin_name'] = $result['first_name'] . ' ' . $result['last_name'];
            $_SESSION['admin_role'] = $result['role'];
            $_SESSION['admin_email'] = $result['email'];
            header("Location: platform_dashboard.php");
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            font-size: 28px;
            font-weight: 700;
            color: #1b4332;
            text-decoration: none;
        }

        .brand-logo span {
            color: #d4a017;
        }

        .brand-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }

        .login-title {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1b4332;
            box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #1b4332;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: #143728;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 13px;
            text-decoration: none;
        }

        .back-link:hover {
            color: #1b4332;
        }

        .ghana-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }

        .ghana-badge img {
            width: 16px;
            height: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand">
                <a href="../index_tourlink.php" class="brand-logo">TourLink<span>.</span></a>
                <p class="brand-subtitle">Administration Portal</p>
            </div>

            <h1 class="login-title">Sign in to continue</h1>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@tourlink.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <a href="../index_tourlink.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to TourLink
            </a>

            <div class="ghana-badge">
                <span>Proudly Ghanaian</span>
            </div>
        </div>
    </div>
</body>
</html>
