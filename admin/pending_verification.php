<?php
require_once '../settings/core.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Verification - TourLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .pending-container {
            max-width: 600px;
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .pending-icon {
            width: 120px;
            height: 120px;
            background: rgba(45, 106, 79, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }

        .pending-icon i {
            font-size: 50px;
            color: #2d6a4f;
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.05rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #2d6a4f;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            margin-bottom: 30px;
        }

        .info-box h6 {
            font-weight: 700;
            color: #2d6a4f;
            margin-bottom: 12px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .info-box li {
            margin-bottom: 8px;
            color: #333;
        }

        .btn-logout {
            background: #2d6a4f;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: #1b4332;
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <div class="pending-container">
        <div class="pending-icon">
            <i class="fa fa-clock"></i>
        </div>

        <h1>Account Pending Verification</h1>
        <p>Thank you for registering as a service provider! Your account is currently being reviewed by our team.</p>

        <div class="info-box">
            <h6><i class="fa fa-info-circle"></i> What Happens Next?</h6>
            <ul>
                <li>Our team will review your application within 24-48 hours</li>
                <li>You'll receive an email notification once verified</li>
                <li>After verification, you can start listing your services</li>
                <li>Don't forget to complete the GHS 20 onboarding payment</li>
            </ul>
        </div>

        <p style="font-size: 0.95rem; color: #999;">Need help? Contact us at <strong style="color: #2d6a4f;">support@tourlink.com.gh</strong></p>

        <a href="../login/logout.php" class="btn-logout">
            <i class="fa fa-sign-out-alt"></i> Logout
        </a>
    </div>
</body>
</html>
