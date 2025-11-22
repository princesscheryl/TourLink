<?php
/**
 * Save Search Action
 * AJAX handler to save search queries to history
 */

session_start();
header('Content-Type: application/json');

require_once '../controllers/search_history_controller.php';

$response = [
    'success' => false,
    'message' => ''
];

// Only save for logged-in users
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get search data
$search_query = isset($_POST['query']) ? trim($_POST['query']) : '';

if (empty($search_query)) {
    $response['message'] = 'Empty search query';
    echo json_encode($response);
    exit;
}

// Get optional filters
$filters = null;
if (isset($_POST['filters'])) {
    $filters = is_array($_POST['filters']) ? $_POST['filters'] : json_decode($_POST['filters'], true);
}

$user_id = (int)$_SESSION['user_id'];

// Save the search
if (save_search_ctr($user_id, $search_query, $filters)) {
    $response['success'] = true;
    $response['message'] = 'Search saved';
} else {
    $response['message'] = 'Failed to save search';
}

echo json_encode($response);
exit;
?>
