<?php
session_start();
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_once '../controllers/service_controller.php';

// Check if provider is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header('Location: ../login/login.php');
    exit();
}

$provider_id = $_SESSION['provider_id'];

// Check if provider already has active premium subscription
$db = new db_connection();
$db->db_connect();

$check_active = $db->db->prepare("
    SELECT * FROM tl_premium_listings
    WHERE provider_id = ?
    AND status = 'active'
    AND end_date >= CURDATE()
    ORDER BY end_date DESC
    LIMIT 1
");
$check_active->bind_param("i", $provider_id);
$check_active->execute();
$active_subscription = $check_active->get_result()->fetch_assoc();

// Determine if subscription is cancelled (auto_renew = 0)
$is_cancelled = false;
if ($active_subscription && isset($active_subscription['auto_renew'])) {
    $is_cancelled = ($active_subscription['auto_renew'] == 0 || $active_subscription['auto_renew'] === '0');
}

// Get provider's services
$services = get_services_by_provider_ctr($provider_id);
if (!$services) {
    $services = [];
}

// Get subscription history (backward compatible)
// Check if premium_listing_id column exists in tl_subscription_payments
$check_col = $db->db->query("SHOW COLUMNS FROM tl_subscription_payments LIKE 'premium_listing_id'");
if ($check_col->num_rows > 0) {
    // Column exists - use JOIN
    $history_query = $db->db->prepare("
        SELECT pl.*, sp.payment_date, sp.transaction_reference
        FROM tl_premium_listings pl
        LEFT JOIN tl_subscription_payments sp ON pl.premium_listing_id = sp.premium_listing_id
        WHERE pl.provider_id = ?
        ORDER BY pl.purchase_date DESC
        LIMIT 10
    ");
} else {
    // Column doesn't exist - query only premium_listings
    $history_query = $db->db->prepare("
        SELECT pl.*, pl.payment_reference as transaction_reference, pl.purchase_date as payment_date
        FROM tl_premium_listings pl
        WHERE pl.provider_id = ?
        ORDER BY pl.purchase_date DESC
        LIMIT 10
    ");
}
$history_query->bind_param("i", $provider_id);
$history_query->execute();
$history = $history_query->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Subscription - TourLink</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/premium_subscription.css" rel="stylesheet">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: #1a1a1a;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #1b4332;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 16px;
        }

        .premium-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 40px;
        }

        .premium-card {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .premium-hero {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            color: white;
        }

        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .premium-hero h2 {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .premium-price {
            font-size: 48px;
            font-weight: 700;
            margin: 20px 0;
        }

        .premium-price span {
            font-size: 18px;
            font-weight: 400;
            opacity: 0.9;
        }

        .benefits-list {
            list-style: none;
            margin: 24px 0;
        }

        .benefits-list li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
        }

        .benefits-list li i {
            color: #d4a017;
            font-size: 18px;
        }

        .btn-subscribe {
            width: 100%;
            padding: 16px;
            background: white;
            color: #1b4332;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-subscribe:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .btn-subscribe:disabled {
            background: #e9ecef;
            cursor: not-allowed;
            transform: none;
        }

        .status-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .status-active {
            background: #d1e7dd;
            border: 2px solid #0f5132;
            color: #0f5132;
            padding: 16px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .status-active h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .status-inactive {
            background: #f8d7da;
            border: 2px solid #842029;
            color: #842029;
            padding: 16px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }

        .status-detail {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .status-detail:last-child {
            border-bottom: none;
        }

        .status-label {
            color: #6c757d;
            font-size: 14px;
        }

        .status-value {
            font-weight: 600;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 20px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1b4332;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: #6c757d;
        }

        .history-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .error-message, .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 8px;
            padding: 16px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 350px;
            max-width: 500px;
            animation: slideIn 0.3s ease-out;
        }

        .error-message {
            border-left: 4px solid #dc3545;
        }

        .success-message {
            border-left: 4px solid #28a745;
        }

        .error-message i {
            color: #dc3545;
            font-size: 20px;
        }

        .success-message i {
            color: #28a745;
            font-size: 20px;
        }

        .error-message span, .success-message span {
            flex: 1;
            color: #1a1a1a;
            font-size: 14px;
            line-height: 1.5;
        }

        .error-close {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
            line-height: 1;
            transition: color 0.2s;
        }

        .error-close:hover {
            color: #1a1a1a;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .error-message, .success-message {
                right: 10px;
                left: 10px;
                min-width: auto;
                max-width: none;
            }
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            font-size: 13px;
            font-weight: 600;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
        }

        .history-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-active {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-expired {
            background: #f8d7da;
            color: #842029;
        }

        .badge-cancelled {
            background: #e9ecef;
            color: #6c757d;
        }

        .btn-cancel {
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #c82333;
        }

        @media (max-width: 968px) {
            .premium-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
</head>
<body>
    <div class="container">
        <a href="provider_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="page-header">
            <h1 class="page-title">Premium Subscription</h1>
            <p class="page-subtitle">Boost your visibility and get more bookings</p>
        </div>

        <!-- Error Message Display -->
        <div id="errorMessage" class="error-message" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorText"></span>
            <button type="button" onclick="closeErrorMessage()" class="error-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Success Message Display -->
        <div id="successMessage" class="success-message" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span id="successText"></span>
            <button type="button" onclick="closeSuccessMessage()" class="error-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <?php
        // Display error messages from URL parameters
        if (isset($_GET['error'])) {
            $error_msg = '';
            switch ($_GET['error']) {
                case 'already_subscribed':
                    $error_msg = 'You already have an active premium subscription with auto-renewal enabled.';
                    break;
                default:
                    $error_msg = 'An error occurred. Please try again.';
            }
            if ($error_msg) {
                echo '<script>document.addEventListener("DOMContentLoaded", function() { showErrorMessage("' . addslashes($error_msg) . '"); });</script>';
            }
        }
        ?>

        <?php if ($active_subscription && !$is_cancelled): ?>
            <!-- Active Subscription View -->
            <div class="premium-grid">
                <div class="premium-card">
                    <div class="status-active">
                        <h3><i class="fas fa-check-circle"></i> Your Premium Subscription is Active!</h3>
                        <p>Your services are featured on the homepage and top of search results</p>
                    </div>

                    <h3 class="section-title">Subscription Benefits</h3>
                    <ul class="benefits-list">
                        <li><i class="fas fa-star"></i> Featured on homepage carousel</li>
                        <li><i class="fas fa-search"></i> Priority in all search results</li>
                        <li><i class="fas fa-badge-check"></i> Premium badge on all your services</li>
                        <li><i class="fas fa-chart-line"></i> 3x more visibility</li>
                        <li><i class="fas fa-trophy"></i> Appear above regular listings</li>
                    </ul>

                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-value"><?php echo isset($active_subscription['views_count']) ? number_format($active_subscription['views_count']) : 'N/A'; ?></div>
                            <div class="stat-label">Premium Views</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?php echo isset($active_subscription['bookings_count']) ? number_format($active_subscription['bookings_count']) : 'N/A'; ?></div>
                            <div class="stat-label">Premium Bookings</div>
                        </div>
                    </div>
                </div>

                <div class="status-card">
                    <h3 class="section-title">Subscription Details</h3>
                    <div class="status-detail">
                        <span class="status-label">Status:</span>
                        <span class="status-value" style="color: #0f5132;">Active</span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Start Date:</span>
                        <span class="status-value"><?php echo date('M j, Y', strtotime($active_subscription['start_date'])); ?></span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Next Billing:</span>
                        <span class="status-value">
                            <?php
                            $billing_date = isset($active_subscription['next_billing_date'])
                                ? $active_subscription['next_billing_date']
                                : $active_subscription['end_date'];
                            echo date('M j, Y', strtotime($billing_date));
                            ?>
                        </span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Monthly Fee:</span>
                        <span class="status-value">GH₵ 150.00</span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Auto-Renew:</span>
                        <span class="status-value"><?php echo $active_subscription['auto_renew'] ? 'Yes' : 'No'; ?></span>
                    </div>

                    <?php if ($active_subscription['auto_renew']): ?>
                        <button class="btn-cancel" onclick="cancelSubscription(event)" style="width: 100%; margin-top: 20px;">
                            Cancel Subscription
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($active_subscription && $is_cancelled): ?>
            <!-- Cancelled Subscription View (Still Active Until End Date) -->
            <div class="premium-grid">
                <div class="premium-card">
                    <div class="status-inactive">
                        <h3><i class="fas fa-info-circle"></i> Subscription Cancelled</h3>
                        <p>Your premium benefits will continue until <?php echo date('M j, Y', strtotime($active_subscription['end_date'])); ?></p>
                    </div>

                    <h3 class="section-title">Subscription Benefits</h3>
                    <ul class="benefits-list">
                        <li><i class="fas fa-star"></i> Featured on homepage carousel</li>
                        <li><i class="fas fa-search"></i> Priority in all search results</li>
                        <li><i class="fas fa-badge-check"></i> Premium badge on all your services</li>
                        <li><i class="fas fa-chart-line"></i> 3x more visibility</li>
                        <li><i class="fas fa-trophy"></i> Appear above regular listings</li>
                    </ul>

                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-value"><?php echo isset($active_subscription['views_count']) ? number_format($active_subscription['views_count']) : 'N/A'; ?></div>
                            <div class="stat-label">Premium Views</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?php echo isset($active_subscription['bookings_count']) ? number_format($active_subscription['bookings_count']) : 'N/A'; ?></div>
                            <div class="stat-label">Premium Bookings</div>
                        </div>
                    </div>
                </div>

                <div class="status-card">
                    <h3 class="section-title">Subscription Details</h3>
                    <div class="status-detail">
                        <span class="status-label">Status:</span>
                        <span class="status-value" style="color: #842029;">Cancelled</span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Start Date:</span>
                        <span class="status-value"><?php echo date('M j, Y', strtotime($active_subscription['start_date'])); ?></span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Ends On:</span>
                        <span class="status-value"><?php echo date('M j, Y', strtotime($active_subscription['end_date'])); ?></span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Monthly Fee:</span>
                        <span class="status-value">GH₵ 150.00</span>
                    </div>
                    <div class="status-detail">
                        <span class="status-label">Auto-Renew:</span>
                        <span class="status-value" style="color: #842029;">No (Cancelled)</span>
                    </div>

                    <button class="btn-subscribe" onclick="subscribePremium(event)" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-sync-alt"></i> Re-subscribe Now
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Subscribe View -->
            <div class="premium-grid">
                <div class="premium-card premium-hero">
                    <div class="premium-badge">
                        <i class="fas fa-crown"></i>
                        Premium Listing
                    </div>
                    <h2>Boost Your Business</h2>
                    <p>Get featured placement and priority visibility across the platform</p>

                    <div class="premium-price">
                        GH₵ 150 <span>/ month</span>
                    </div>

                    <ul class="benefits-list">
                        <li><i class="fas fa-star"></i> Featured on homepage carousel</li>
                        <li><i class="fas fa-search"></i> Top of search results</li>
                        <li><i class="fas fa-badge-check"></i> Premium badge</li>
                        <li><i class="fas fa-chart-line"></i> 3x more visibility</li>
                        <li><i class="fas fa-repeat"></i> Auto-renews monthly</li>
                        <li><i class="fas fa-times-circle"></i> Cancel anytime</li>
                    </ul>

                    <button class="btn-subscribe" onclick="subscribePremium(event)">
                        Subscribe Now
                    </button>
                </div>

                <div class="status-card">
                    <h3 class="section-title">Why Go Premium?</h3>
                    <p style="color: #6c757d; margin-bottom: 20px; line-height: 1.6;">
                        Premium providers get an average of <strong>300% more bookings</strong> compared to regular listings. Stand out from the competition!
                    </p>

                    <div class="status-inactive" style="margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i>
                        <p style="margin-top: 8px;">You currently don't have an active premium subscription</p>
                    </div>

                    <h4 style="font-size: 16px; margin-bottom: 12px;">Your Services (<?php echo count($services); ?>)</h4>
                    <?php foreach(array_slice($services, 0, 3) as $service): ?>
                        <div class="status-detail">
                            <span class="status-label"><?php echo htmlspecialchars($service['service_title']); ?></span>
                            <span class="status-value" style="color: #6c757d;">Regular</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Subscription History -->
        <?php if (!empty($history)): ?>
        <div class="history-section">
            <h3 class="section-title">Subscription History</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($history as $record): 
                        // Determine status badge - check if cancelled (auto_renew = 0) but still active
                        $status = $record['status'];
                        $badge_class = 'badge-' . $status;
                        $status_text = ucfirst($status);
                        
                        // If active but auto_renew is 0, show as cancelled
                        if ($status === 'active' && isset($record['auto_renew']) && ($record['auto_renew'] == 0 || $record['auto_renew'] === '0')) {
                            $badge_class = 'badge-cancelled';
                            $status_text = 'Cancelled';
                        }
                    ?>
                    <tr>
                        <td><?php echo date('M j', strtotime($record['start_date'])) . ' - ' . date('M j, Y', strtotime($record['end_date'])); ?></td>
                        <td>GH₵ <?php echo number_format($record['amount_paid'], 2); ?></td>
                        <td>
                            <span class="status-badge <?php echo $badge_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td><?php echo $record['payment_date'] ? date('M j, Y', strtotime($record['payment_date'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($record['transaction_reference'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <script src="../js/premium_subscription.js"></script>
</body>
</html>
