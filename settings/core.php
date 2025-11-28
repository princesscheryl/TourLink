<?php
// Configure session settings BEFORE starting session
if (session_status() === PHP_SESSION_NONE) {
    // Set session lifetime to 30 minutes (1800 seconds)
    // This prevents sessions from being garbage collected during payment
    ini_set('session.gc_maxlifetime', 1800);

    // Set session cookie lifetime to 30 minutes
    ini_set('session.cookie_lifetime', 1800);

    // Reduce garbage collection frequency to preserve sessions longer
    // This means GC only runs 1% of the time instead of default
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);

    session_start();
}

//for header redirection
ob_start();

// Include session timeout management (15 minute auto-logout)
// Wrapped in try-catch to prevent crashes
try {
    require_once __DIR__ . '/session_timeout.php';
} catch (Throwable $e) {
    error_log("Session timeout error: " . $e->getMessage());
    // Don't crash, just log the error
}

//funtion to check for login
function isLoggedIn(){
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    else{
        return true;
    }
}

//function to get user ID
function getUserID(){
    if (isLoggedIn()){
        return $_SESSION['user_id'];
    }
    return false;
}

//function to check for role (admin, customer, etc)
function isAdmin(){
    if (isLoggedIn()){
        return $_SESSION['user_role'] == 1;
    }
}


?>