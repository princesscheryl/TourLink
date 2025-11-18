<?php
/**
 * Toggle Favorite Action
 * AJAX handler to add/remove services from favorites
 */

session_start();
header('Content-Type: application/json');

require_once '../controllers/favorite_controller.php';

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'action' => '',
    'count' => 0
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to add favorites';
    $response['require_login'] = true;
    echo json_encode($response);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get service ID from POST
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;

if ($service_id <= 0) {
    $response['message'] = 'Invalid service ID';
    echo json_encode($response);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Check if service is already favorited
$is_favorited = is_favorited_ctr($user_id, $service_id);

if ($is_favorited) {
    // Remove from favorites
    $result = remove_favorite_ctr($user_id, $service_id);

    if ($result) {
        $response['success'] = true;
        $response['action'] = 'removed';
        $response['message'] = 'Removed from favorites';
        $response['count'] = get_favorite_count_ctr($user_id);
    } else {
        $response['message'] = 'Failed to remove from favorites';
    }
} else {
    // Add to favorites
    $result = add_favorite_ctr($user_id, $service_id);

    if ($result) {
        $response['success'] = true;
        $response['action'] = 'added';
        $response['message'] = 'Added to favorites!';
        $response['count'] = get_favorite_count_ctr($user_id);
    } else {
        $response['message'] = 'Failed to add to favorites';
    }
}

echo json_encode($response);
exit;
?>
