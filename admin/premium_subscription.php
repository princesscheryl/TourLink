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
