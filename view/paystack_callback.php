<?php
// Enable error display for debugging - SHOW ERRORS ON SCREEN
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', 1);

// Catch FATAL errors that occur before try-catch
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        echo "<!DOCTYPE html><html><head><title>Fatal Error</title>";
        echo "<style>body{font-family:monospace;padding:40px;background:#1a1a1a;color:#ff6b6b;}</style></head><body>";
        echo "<h1>🔴 FATAL PHP ERROR</h1>";
        echo "<p><strong>Type:</strong> " . $error['type'] . "</p>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($error['file']) . "</p>";
        echo "<p><strong>Line:</strong> " . $error['line'] . "</p>";
        echo "<hr><p><strong>Reference:</strong> " . htmlspecialchars($_GET['reference'] ?? 'N/A') . "</p>";
        echo "<p><a href='../admin/premium_subscription.php'>← Back to Premium Subscription</a></p>";
        echo "</body></html>";
    }
});

// Prevent any output before headers
ob_start();

// Initialize variables
$reference = null;
$error_message = null;

// Wrap everything in try-catch to prevent crashes
try {
    // Get reference FIRST before any redirects
    $reference = isset($_GET['reference']) ? trim($_GET['reference']) : null;

    if (!$reference) {
        throw new Exception("No payment reference provided in URL");
    }

    // Don't start session - core.php will do it
    require_once '../settings/core.php';

    // Check authentication - Allow both tourists (bookings) and providers (subscriptions)
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User session not found. Session may have expired during payment. Reference: $reference");
    }

} catch (Throwable $e) {
    // Catch ALL errors including fatal errors
    $error_message = $e->getMessage();
    error_log("Callback page error: " . $error_message);

    // Display error on page instead of dying
    ob_end_clean();
    echo "<!DOCTYPE html><html><head><title>Payment Callback Error</title>";
    echo "<style>body{font-family:monospace;padding:40px;background:#1a1a1a;color:#0f0;}</style></head><body>";
    echo "<h1>⚠️ Payment Callback Error</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($error_message) . "</p>";
    echo "<p><strong>Reference:</strong> " . htmlspecialchars($reference ?? 'N/A') . "</p>";
    echo "<hr><h2>Debug Information:</h2>";
    echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . "</p>";
    echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>User ID in Session:</strong> " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "</p>";
    echo "<p><strong>Session Keys:</strong> " . implode(', ', array_keys($_SESSION ?? [])) . "</p>";
    echo "<p><strong>GET Parameters:</strong> " . print_r($_GET, true) . "</p>";
    echo "<hr><p><a href='../admin/premium_subscription.php'>← Back to Premium Subscription</a></p>";
    echo "</body></html>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - TourLink</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .spinner-wrapper {
            margin-bottom: 30px;
        }

        .spinner {
            display: inline-block;
            width: 60px;
            height: 60px;
            border: 5px solid #f1f1f1;
            border-top: 5px solid #2d6a4f;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        h1 {
            font-size: 1.8rem;
            color: #1a1a1a;
            margin-bottom: 12px;
            font-weight: 700;
        }

        p {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .reference-box {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin: 25px 0;
            border-left: 4px solid #2d6a4f;
        }

        .reference-box label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .reference-box strong {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #1a1a1a;
            word-break: break-all;
        }

        .message-box {
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            display: none;
            text-align: left;
        }

        .error-box {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .success-box {
            background: #efe;
            border: 1px solid #cfc;
            color: #363;
        }

        .message-box strong {
            display: block;
            margin-bottom: 8px;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .icon.processing {
            color: #2d6a4f;
        }

        .icon.success {
            color: #28a745;
        }

        .icon.error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner-wrapper" id="spinnerWrapper">
            <div class="spinner"></div>
        </div>

        <div class="icon processing" id="processingIcon">
            <i class="fas fa-credit-card"></i>
        </div>

        <h1 id="title">Verifying Payment</h1>
        <p id="description">Please wait while we confirm your payment...</p>

        <div class="reference-box">
            <label>Payment Reference</label>
            <strong><?php echo htmlspecialchars($reference); ?></strong>
        </div>

        <div class="message-box error-box" id="errorBox">
            <strong>Error</strong>
            <span id="errorMessage"></span>
        </div>

        <div class="message-box success-box" id="successBox">
            <strong>Success!</strong>
            <span>Your payment has been verified. Redirecting...</span>
        </div>
    </div>

    <script>
        async function verifyPayment() {
            const reference = '<?php echo htmlspecialchars($reference); ?>';

            // Log session info for debugging
            console.log('Payment Reference:', reference);
            console.log('Verifying payment...');

            try {
                const response = await fetch('../actions/paystack_verify_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ reference: reference })
                });

                console.log('Response status:', response.status);

                // Check if response is JSON
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error('Non-JSON response received:', text);

                    // Show more detailed error on page
                    showError('Server returned an error. Please check console for details. Error preview: ' + text.substring(0, 500));

                    // Also display the full error in the page for debugging
                    const errorDiv = document.createElement('div');
                    errorDiv.style.cssText = 'position: fixed; bottom: 0; left: 0; right: 0; background: #000; color: #0f0; padding: 20px; max-height: 200px; overflow: auto; font-family: monospace; font-size: 12px; z-index: 10000;';
                    errorDiv.textContent = 'FULL ERROR OUTPUT:\n' + text;
                    document.body.appendChild(errorDiv);
                    return;
                }

                const data = await response.json();
                console.log('Verification response:', data);
                console.log('Status:', data.status);
                console.log('Verified:', data.verified);
                console.log('Message:', data.message);

                document.getElementById('spinnerWrapper').style.display = 'none';

                if (data.status === 'success' && data.verified) {
                    document.getElementById('processingIcon').className = 'icon success';
                    document.getElementById('processingIcon').innerHTML = '<i class="fas fa-check-circle"></i>';
                    document.getElementById('successBox').style.display = 'block';

                    // Handle different payment types
                    if (data.payment_type === 'premium_subscription') {
                        document.getElementById('title').textContent = 'Premium Subscription Activated!';
                        document.getElementById('description').textContent = 'Your services are now featured.';

                        setTimeout(() => {
                            window.location.href = `payment_success.php?reference=${encodeURIComponent(reference)}&type=premium&listing_id=${data.premium_listing_id || 0}`;
                        }, 1500);
                    } else {
                        document.getElementById('title').textContent = 'Payment Successful!';
                        document.getElementById('description').textContent = 'Your booking is pending provider confirmation.';

                        setTimeout(() => {
                            window.location.href = `payment_success.php?reference=${encodeURIComponent(reference)}&booking_id=${data.booking_id}`;
                        }, 1500);
                    }

                } else {
                    showError(data.message || 'Payment verification failed');

                    setTimeout(() => {
                        // Redirect based on user type
                        const userType = '<?php echo $_SESSION['user_type'] ?? 'tourist'; ?>';
                        if (userType === 'provider') {
                            window.location.href = '../admin/premium_subscription.php?payment_failed=1';
                        } else {
                            window.location.href = 'my_bookings.php?payment_failed=1';
                        }
                    }, 5000);
                }

            } catch (error) {
                console.error('Verification error:', error);
                showError('Connection error. Please contact support if money was deducted.');

                setTimeout(() => {
                    window.location.href = 'my_bookings.php?payment_error=1';
                }, 5000);
            }
        }

        function showError(message) {
            document.getElementById('spinnerWrapper').style.display = 'none';
            document.getElementById('processingIcon').className = 'icon error';
            document.getElementById('processingIcon').innerHTML = '<i class="fas fa-times-circle"></i>';
            document.getElementById('title').textContent = 'Payment Failed';
            document.getElementById('description').textContent = 'There was an issue processing your payment.';
            document.getElementById('errorBox').style.display = 'block';
            document.getElementById('errorMessage').textContent = message;
        }

        window.addEventListener('load', verifyPayment);
    </script>
</body>
</html>
