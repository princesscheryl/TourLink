<?php
header('Content-Type: application/json');
session_start();

$response = array();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/tourlink_user_controller.php';
require_once '../classes/service_provider_class.php';

// Get login credentials
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

$errors = array();

// Validate inputs
if (empty($email)) {
    $errors[] = 'Email is required';
}
if (empty($password)) {
    $errors[] = 'Password is required';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = $errors[0];
    echo json_encode($response);
    exit();
}

// Attempt to login
$user = login_tourlink_user_ctr($email, $password);

if ($user) {
    // Login successful - store user data in session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];

    $response['status'] = 'success';
    $response['message'] = 'Login successful!';

    // Role-based redirect logic
    if ($user['user_type'] === 'tourist') {
        // Tourist - redirect to homepage
        $response['redirect'] = '../index_tourlink.php';
        $response['message'] = 'Welcome back, ' . $user['first_name'] . '!';
    }
    else if ($user['user_type'] === 'provider') {
        // Provider - check verification status
        $provider_class = new ServiceProvider();
        $provider = $provider_class->get_provider_by_user_id($user['user_id']);

        if ($provider) {
            $_SESSION['provider_id'] = $provider['provider_id'];

            // Check verification status
            if ($provider['verification_status'] === 'pending') {
                // Provider is pending verification
                $response['redirect'] = '../admin/pending_verification.php';
                $response['message'] = 'Your account is pending verification. You will be notified once approved.';
            }
            else if ($provider['verification_status'] === 'verified' || $provider['verification_status'] === 'approved') {
                // Provider is verified - redirect to dashboard
                $response['redirect'] = '../admin/provider_dashboard.php';
                $response['message'] = 'Welcome back, ' . $user['first_name'] . '!';
            }
            else {
                // Suspended or other status
                $response['status'] = 'error';
                $response['message'] = 'Your provider account is currently ' . $provider['verification_status'] . '. Please contact support.';
                session_destroy();
            }
        } else {
            // Provider account exists but no provider profile (shouldn't happen)
            $response['status'] = 'error';
            $response['message'] = 'Provider profile not found. Please contact support.';
            session_destroy();
        }
    }
    else if ($user['user_type'] === 'admin') {
        // Admin - redirect to admin dashboard
        $response['redirect'] = '../admin/admin_dashboard.php';
        $response['message'] = 'Welcome back, Admin!';
    }
    else {
        // Unknown user type
        $response['status'] = 'error';
        $response['message'] = 'Invalid account type';
        session_destroy();
    }
} else {
    // Login failed
    $response['status'] = 'error';
    $response['message'] = 'Invalid email or password. Please try again.';
}

echo json_encode($response);
?>
