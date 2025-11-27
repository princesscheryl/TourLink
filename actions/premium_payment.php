<?php
session_start();
require_once '../settings/core.php';

// Check if provider is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header('Location: ../login/login.php');
    exit();
}

$subscription_id = isset($_GET['subscription_id']) ? (int)$_GET['subscription_id'] : 0;

if (!$subscription_id) {
    header('Location: ../admin/premium_subscription.php');
    exit();
}

// Get subscription details
$db = new db_connection();
$db->db_connect();

$stmt = $db->db->prepare("
    SELECT sp.*, pl.provider_id, pl.start_date, pl.end_date
    FROM tl_subscription_payments sp
    JOIN tl_premium_listings pl ON sp.premium_listing_id = pl.premium_listing_id
    WHERE sp.subscription_payment_id = ?
    AND sp.provider_id = ?
    AND sp.payment_status = 'pending'
");

$provider_id = $_SESSION['provider_id'];
$stmt->bind_param("ii", $subscription_id, $provider_id);
$stmt->execute();
$subscription = $stmt->get_result()->fetch_assoc();

if (!$subscription) {
    header('Location: ../admin/premium_subscription.php?error=not_found');
    exit();
}

$amount = floatval($subscription['amount']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Subscription Payment - TourLink</title>
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
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .premium-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
        }

        .payment-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .payment-header p {
            color: #6c757d;
            font-size: 14px;
        }

        .payment-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6c757d;
            font-size: 14px;
        }

        .detail-value {
            font-weight: 600;
            font-size: 14px;
        }

        .amount-row {
            background: white;
            padding: 16px;
            border-radius: 8px;
            margin-top: 16px;
        }

        .amount-row .detail-label {
            font-size: 16px;
            color: #1a1a1a;
        }

        .amount-row .detail-value {
            font-size: 32px;
            color: #1b4332;
        }

        .payment-methods {
            margin-bottom: 24px;
        }

        .method-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1a1a1a;
        }

        .method-btn {
            width: 100%;
            padding: 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .method-btn:hover {
            border-color: #1b4332;
            background: #f8f9fa;
        }

        .method-btn i {
            font-size: 24px;
            color: #1b4332;
        }

        .method-info {
            text-align: left;
            flex: 1;
        }

        .method-name {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 2px;
        }

        .method-desc {
            font-size: 12px;
            color: #6c757d;
        }

        .btn-cancel {
            width: 100%;
            padding: 14px;
            background: #e9ecef;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #6c757d;
            cursor: pointer;
            margin-top: 16px;
        }

        .btn-cancel:hover {
            background: #dee2e6;
        }

        .security-note {
            text-align: center;
            margin-top: 20px;
            padding: 16px;
            background: #e7f3ef;
            border-radius: 8px;
            font-size: 13px;
            color: #1b4332;
        }

        .security-note i {
            margin-right: 6px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <div class="premium-icon">
                <i class="fas fa-crown"></i>
            </div>
            <h1>Premium Subscription</h1>
            <p>Complete payment to activate your premium benefits</p>
        </div>

        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Subscription:</span>
                <span class="detail-value">Premium Listing</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Billing Cycle:</span>
                <span class="detail-value">Monthly</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Start Date:</span>
                <span class="detail-value"><?php echo date('M j, Y', strtotime($subscription['start_date'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Next Billing:</span>
                <span class="detail-value"><?php echo date('M j, Y', strtotime($subscription['end_date'])); ?></span>
            </div>

            <div class="amount-row">
                <div class="detail-row" style="border: none;">
                    <span class="detail-label">Amount Due:</span>
                    <span class="detail-value">GH₵ <?php echo number_format($amount, 2); ?></span>
                </div>
            </div>
        </div>

        <div class="payment-methods">
            <h3 class="method-title">Select Payment Method:</h3>

            <button class="method-btn" onclick="payWithMobileMoney('mtn')">
                <i class="fas fa-mobile-alt"></i>
                <div class="method-info">
                    <div class="method-name">MTN Mobile Money</div>
                    <div class="method-desc">Pay with your MTN MoMo account</div>
                </div>
                <i class="fas fa-chevron-right" style="font-size: 16px;"></i>
            </button>

            <button class="method-btn" onclick="payWithMobileMoney('telecel')">
                <i class="fas fa-mobile-alt"></i>
                <div class="method-info">
                    <div class="method-name">Telecel Cash</div>
                    <div class="method-desc">Pay with your Telecel Cash account</div>
                </div>
                <i class="fas fa-chevron-right" style="font-size: 16px;"></i>
            </button>
        </div>

        <button class="btn-cancel" onclick="window.location.href='../admin/premium_subscription.php'">
            Cancel
        </button>

        <div class="security-note">
            <i class="fas fa-lock"></i>
            Your payment is secure and encrypted. Auto-renewal can be cancelled anytime.
        </div>
    </div>

    <script>
        function payWithMobileMoney(provider) {
            // For now, simulate payment completion
            // In production, integrate with actual payment gateway
            if (confirm(`Process payment of GH₵<?php echo $amount; ?> via ${provider.toUpperCase()} Mobile Money?`)) {
                // Submit payment
                fetch('process_premium_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        subscription_id: <?php echo $subscription_id; ?>,
                        payment_method: provider,
                        amount: <?php echo $amount; ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Payment successful! Your premium subscription is now active.');
                        window.location.href = '../admin/premium_subscription.php?success=1';
                    } else {
                        alert('Payment failed: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Payment error. Please try again.');
                });
            }
        }
    </script>
</body>
</html>
