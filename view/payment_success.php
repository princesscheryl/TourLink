<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

$reference = isset($_GET['reference']) ? trim($_GET['reference']) : null;
$payment_type = isset($_GET['type']) ? trim($_GET['type']) : 'booking';

if (!$reference) {
    if ($_SESSION['user_type'] === 'provider') {
        header('Location: ../admin/premium_subscription.php');
    } else {
        header('Location: my_bookings.php');
    }
    exit();
}

// Handle based on payment type
if ($payment_type === 'premium') {
    // Premium subscription success
    if ($_SESSION['user_type'] !== 'provider') {
        header('Location: ../index_tourlink.php');
        exit();
    }

    $premium_listing_id = isset($_GET['listing_id']) ? (int)$_GET['listing_id'] : 0;
    $is_premium = true;
} else {
    // Booking payment success
    if ($_SESSION['user_type'] !== 'tourist') {
        header('Location: ../index_tourlink.php');
        exit();
    }

    $booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

    if (!$booking_id) {
        header('Location: my_bookings.php');
        exit();
    }

    // Get booking details
    $booking = get_booking_by_id_ctr($booking_id);

    if (!$booking || $booking['tourist_id'] != $_SESSION['user_id']) {
        header('Location: my_bookings.php');
        exit();
    }

    $is_premium = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - TourLink</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/payment_success.css" rel="stylesheet">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .success-container {
            max-width: 700px;
            margin: 120px auto 60px;
            padding: 0 20px;
        }

        .success-card {
            background: white;
            border-radius: 16px;
            padding: 50px 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .success-icon i {
            font-size: 40px;
            color: #28a745;
        }

        h1 {
            font-size: 2rem;
            color: #1a1a1a;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            font-size: 15px;
            margin-bottom: 40px;
        }

        .booking-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-size: 14px;
        }

        .detail-value {
            color: #1a1a1a;
            font-weight: 600;
            font-size: 14px;
        }

        .detail-value.large {
            font-size: 18px;
            color: #2d6a4f;
        }

        .reference-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 30px;
        }

        .reference-box label {
            display: block;
            font-size: 12px;
            color: #856404;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .reference-box .ref-text {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #1a1a1a;
            word-break: break-all;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #2d6a4f;
            color: white;
        }

        .btn-primary:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: #2d6a4f;
            border: 2px solid #2d6a4f;
        }

        .btn-secondary:hover {
            background: #f0f7f4;
        }

        @media (max-width: 768px) {
            .success-card {
                padding: 40px 24px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <?php 
            // Include Adinkra symbols and cultural messaging
            if (file_exists(__DIR__ . '/../includes/adinkra_symbols.php')) {
                include __DIR__ . '/../includes/adinkra_symbols.php';
            }
            ?>
            
            <?php if ($is_premium): ?>
                <h1>
                    <?php if (function_exists('get_adinkra_symbol')): ?>
                        <span style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                            <?php echo get_adinkra_symbol('nsoromma', '32px', '#28a745'); ?>
                        </span>
                    <?php endif; ?>
                    Premium Subscription Activated!
                </h1>
                <p class="subtitle">
                    Your services are now featured. Enjoy increased visibility and more bookings!
                </p>
            <?php else: ?>
                <h1>
                    <?php if (function_exists('get_adinkra_symbol')): ?>
                        <span style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                            <?php echo get_adinkra_symbol('akoma', '32px', '#28a745'); ?>
                        </span>
                    <?php endif; ?>
                    Payment Successful!
                </h1>
                <p class="subtitle">
                    <strong style="color: #d4a017;">Akwaaba!</strong> Your booking is awaiting provider confirmation. 
                    <br>You will receive an email shortly. <em style="color: #666;">Yɛbɛhyia bio</em> (We will meet again)!
                </p>
            <?php endif; ?>

            <div class="reference-box">
                <label>Payment Reference</label>
                <div class="ref-text"><?php echo htmlspecialchars($reference); ?></div>
            </div>

            <div class="booking-details">
                <?php if ($is_premium): ?>
                    <!-- Premium Subscription Details -->
                    <div class="detail-row">
                        <span class="detail-label">Subscription Plan</span>
                        <span class="detail-value">Premium Listing</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Billing Cycle</span>
                        <span class="detail-value">Monthly</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Amount Paid</span>
                        <span class="detail-value large">GH₵ 150.00</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value" style="color: #28a745;">
                            <i class="fas fa-crown"></i> Active
                        </span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Next Billing</span>
                        <span class="detail-value"><?php echo date('F j, Y', strtotime('+30 days')); ?></span>
                    </div>
                <?php else: ?>
                    <!-- Booking Details -->
                    <div class="detail-row">
                        <span class="detail-label">Booking Reference</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Service</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['service_title']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Booking Date</span>
                        <span class="detail-value"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></span>
                    </div>

                    <?php if (!empty($booking['discount_amount']) && $booking['discount_amount'] > 0): ?>
                    <div class="detail-row">
                        <span class="detail-label">Original Amount</span>
                        <span class="detail-value">GH₵ <?php echo number_format($booking['total_amount'] + $booking['discount_amount'], 2); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Discount Applied</span>
                        <span class="detail-value" style="color: #28a745;">- GH₵ <?php echo number_format($booking['discount_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="detail-row">
                        <span class="detail-label">Amount Paid</span>
                        <span class="detail-value large">GH₵ <?php echo number_format($booking['total_amount'], 2); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Booking Status</span>
                        <span class="detail-value" style="color: #ffc107;">
                            <i class="fas fa-clock"></i> Pending Confirmation
                        </span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Payment Status</span>
                        <span class="detail-value" style="color: #28a745;">
                            <i class="fas fa-check-circle"></i> Paid
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cultural Message Box -->
            <div style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); border-radius: 12px; padding: 20px; margin-bottom: 30px; color: white; text-align: center;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px;">
                    <?php if (function_exists('get_adinkra_symbol')): ?>
                        <?php echo get_adinkra_symbol('sankofa', '24px', '#d4a017'); ?>
                    <?php endif; ?>
                    <h3 style="margin: 0; font-size: 18px; color: #d4a017;">Experience Ghana's Rich Culture</h3>
                    <?php if (function_exists('get_adinkra_symbol')): ?>
                        <?php echo get_adinkra_symbol('sankofa', '24px', '#d4a017'); ?>
                    <?php endif; ?>
                </div>
                <p style="margin: 0; font-size: 14px; opacity: 0.95; line-height: 1.6;">
                    Your booking supports local communities and preserves Ghana's cultural heritage. 
                    <br><strong>Medaase</strong> (Thank you) for choosing TourLink!
                </p>
            </div>

            <div class="action-buttons">
                <?php if ($is_premium): ?>
                    <a href="../admin/provider_dashboard.php" class="btn btn-primary">
                        <i class="fas fa-th-large"></i>
                        Go to Dashboard
                    </a>
                    <a href="../admin/premium_subscription.php" class="btn btn-secondary">
                        <i class="fas fa-crown"></i>
                        View Subscription
                    </a>
                <?php else: ?>
                    <a href="my_bookings.php" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i>
                        View My Bookings
                    </a>
                    <a href="all_services.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Browse More Services
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
