<?php
session_start();
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tourist') {
    header('Location: ../login/login.php');
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

// Check if already paid
if ($booking['payment_status'] === 'escrow' || $booking['payment_status'] === 'released') {
    header('Location: my_bookings.php?already_paid=1');
    exit();
}

$total_amount = floatval($booking['total_amount']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - TourLink</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/navigation.css" rel="stylesheet">
    <link href="../css/footer.css" rel="stylesheet">
    <link href="../css/booking_payment.css" rel="stylesheet">
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

        .payment-container {
            max-width: 900px;
            margin: 120px auto 60px;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #666;
            font-size: 15px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .booking-details {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid #f5f5f5;
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

        .service-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .service-card h3 {
            font-size: 1rem;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .service-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .payment-sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .price-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .discount-section {
            margin-bottom: 25px;
        }

        .discount-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            display: block;
        }

        .discount-input-group {
            display: flex;
            gap: 8px;
        }

        .discount-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            text-transform: uppercase;
        }

        .discount-input:focus {
            outline: none;
            border-color: #2d6a4f;
        }

        .btn-apply {
            padding: 12px 20px;
            background: #2d6a4f;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-apply:hover {
            background: #1b4332;
        }

        .btn-apply:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .discount-message {
            margin-top: 8px;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            display: none;
        }

        .discount-message.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }

        .discount-message.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }

        .price-breakdown {
            margin-bottom: 25px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }

        .price-row.total {
            border-top: 2px solid #e0e0e0;
            margin-top: 12px;
            padding-top: 16px;
            font-size: 18px;
            font-weight: 700;
        }

        .price-row.discount {
            color: #28a745;
        }

        .btn-pay {
            width: 100%;
            padding: 16px;
            background: #2d6a4f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-pay:hover {
            background: #1b4332;
            transform: translateY(-2px);
        }

        .btn-pay:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .security-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }

        .security-note i {
            color: #28a745;
            margin-right: 4px;
        }

        @media (max-width: 968px) {
            .payment-grid {
                grid-template-columns: 1fr;
            }

            .payment-sidebar {
                position: static;
            }
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <div class="payment-container">
        <div class="page-header">
            <h1>Complete Your Payment</h1>
            <p>Review your booking details and proceed to payment</p>
        </div>

        <div class="payment-grid">
            <div class="booking-details">
                <h2 class="section-title">Booking Details</h2>

                <div class="service-card">
                    <h3><?php echo htmlspecialchars($booking['service_title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($booking['service_description'], 0, 150)); ?>...</p>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Booking Reference</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Provider</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['provider_name']); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Booking Date</span>
                    <span class="detail-value"><?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Number of People</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['number_of_people']); ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" style="color: #ffa500;">Pending Payment</span>
                </div>
            </div>

            <div class="payment-sidebar">
                <div class="price-card">
                    <h2 class="section-title">Payment Summary</h2>

                    <div class="discount-section">
                        <label class="discount-label">Have a discount code?</label>
                        <div class="discount-input-group">
                            <input type="text"
                                   id="discountCode"
                                   class="discount-input"
                                   placeholder="Enter code"
                                   maxlength="50">
                            <button type="button" class="btn-apply" id="applyDiscountBtn">Apply</button>
                        </div>
                        <div class="discount-message" id="discountMessage"></div>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span id="subtotalAmount">GH₵ <?php echo number_format($total_amount, 2); ?></span>
                        </div>
                        <div class="price-row discount" id="discountRow" style="display: none;">
                            <span>Discount</span>
                            <span id="discountAmount">- GH₵ 0.00</span>
                        </div>
                        <div class="price-row total">
                            <span>Total</span>
                            <span id="totalAmount">GH₵ <?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>

                    <button type="button" class="btn-pay" id="payButton">
                        <i class="fas fa-lock"></i>
                        Proceed to Payment
                    </button>

                    <div class="security-note">
                        <i class="fas fa-shield-alt"></i>
                        Secure payment powered by Paystack
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Pass PHP variables to JavaScript
        window.bookingId = <?php echo $booking_id; ?>;
        window.originalAmount = <?php echo $total_amount; ?>;
        window.userEmail = '<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>';
    </script>
    <script src="../js/booking_payment.js"></script>
</body>
</html>
