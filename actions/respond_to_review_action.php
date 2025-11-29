<?php
require_once '../settings/core.php';
require_once '../controllers/review_controller.php';
require_once '../classes/service_provider_class.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/all_services.php");
    exit();
}

// Get and sanitize form data
$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$provider_response = isset($_POST['provider_response']) ? trim($_POST['provider_response']) : '';
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;

// Validate required fields
if ($review_id <= 0 || empty($provider_response)) {
    $_SESSION['error'] = "Please provide a response";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Check if user is a provider
$provider_class = new ServiceProvider();
$provider = $provider_class->get_provider_by_user_id($_SESSION['user_id']);

if (!$provider) {
    $_SESSION['error'] = "Only providers can respond to reviews";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Get review details
$review = get_review_by_id_ctr($review_id);

if (!$review) {
    $_SESSION['error'] = "Review not found";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Check if this review is for the provider's service
if ($review['provider_id'] != $provider['provider_id']) {
    $_SESSION['error'] = "You can only respond to reviews for your own services";
    header("Location: ../view/single_service.php?id=$service_id");
    exit();
}

// Add provider response
if (add_provider_response_ctr($review_id, $provider_response)) {
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Your response has been posted successfully'
        ]);
        exit();
    }
    
    $_SESSION['success'] = "Your response has been posted successfully";
    header("Location: ../view/single_service.php?id=$service_id#reviews");
} else {
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to post response. Please try again.'
        ]);
        exit();
    }
    
    $_SESSION['error'] = "Failed to post response. Please try again.";
    header("Location: ../view/single_service.php?id=$service_id");
}

exit();
?>
