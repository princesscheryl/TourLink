<?php
/**
 * Session Timeout Management
 * Auto-logout users after 15 minutes of inactivity
 */

// Skip timeout check for payment callback pages to prevent interrupting payment verification
// Check for any paystack callback file (including test/debug versions)
$is_payment_callback = (
    strpos($_SERVER['REQUEST_URI'], 'paystack_callback') !== false ||
    strpos($_SERVER['REQUEST_URI'], 'paystack_verify_payment') !== false ||
    strpos($_SERVER['REQUEST_URI'], 'test_callback') !== false
);

// Only check timeout if user is logged in AND not in callback
if (isset($_SESSION['user_id']) && !$is_payment_callback) {
    // Session timeout duration in seconds (30 minutes = 1800 seconds)
    // Increased to give users enough time during payment process
    $timeout_duration = 1800;

    // Check if last activity timestamp exists
    if (isset($_SESSION['last_activity'])) {
        // Calculate how long since last activity
        $elapsed_time = time() - $_SESSION['last_activity'];

        // If more than 15 minutes have passed, logout user
        if ($elapsed_time > $timeout_duration) {
            // Store timeout message for login page
            $_SESSION['timeout_message'] = 'Your session has expired due to inactivity. Please sign in again.';

            // Store the original user type for redirect
            $was_provider = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'provider');

            // Destroy the session
            session_unset();
            session_destroy();

            // Start a new session just to preserve the timeout message
            session_start();
            $_SESSION['timeout_message'] = 'Your session has expired due to inactivity. Please sign in again.';

            // Redirect to appropriate login page
            $login_url = $was_provider ? '/tourlink/login/login.php' : '/tourlink/login/login.php';

            // Determine correct path based on current location
            $current_path = $_SERVER['REQUEST_URI'];
            if (strpos($current_path, '/admin/') !== false) {
                $redirect = '../login/login.php?timeout=1';
            } elseif (strpos($current_path, '/view/') !== false) {
                $redirect = '../login/login.php?timeout=1';
            } elseif (strpos($current_path, '/actions/') !== false) {
                $redirect = '../login/login.php?timeout=1';
            } else {
                $redirect = 'login/login.php?timeout=1';
            }

            // Redirect to login
            header("Location: $redirect");
            exit();
        }
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}
?>
