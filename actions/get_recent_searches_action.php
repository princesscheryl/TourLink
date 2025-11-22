<?php
/**
 * Get Recent Searches Action
 * AJAX handler to retrieve user's recent searches
 */

session_start();
header('Content-Type: application/json');

require_once '../controllers/search_history_controller.php';

$response = [
    'success' => false,
    'searches' => [],
    'message' => ''
];

// Only return for logged-in users
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

// Get recent searches
$searches = get_recent_searches_ctr($user_id, $limit);

$response['success'] = true;
$response['searches'] = $searches;

echo json_encode($response);
exit;
?>
