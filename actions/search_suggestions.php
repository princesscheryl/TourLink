<?php
/**
 * Search Suggestions API
 * Returns autocomplete suggestions for navigation search
 */

require_once '../settings/core.php';
require_once '../controllers/service_controller.php';
require_once '../controllers/service_category_controller.php';

header('Content-Type: application/json');

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

$suggestions = [];

try {
    // Get recent searches for logged-in users
    if (isset($_SESSION['user_id'])) {
        // TODO: Implement recent searches from database
        // For now, we'll skip this part
    }

    // Search for services
    $services = search_services_ctr($query);
    if ($services && is_array($services)) {
        foreach (array_slice($services, 0, 5) as $service) {
            $suggestions[] = [
                'type' => 'service',
                'id' => $service['service_id'],
                'text' => $service['service_title'],
                'category' => $service['category_name'] ?? ''
            ];
        }
    }

    // Search for categories
    $categories = get_all_service_categories_ctr();
    if ($categories && is_array($categories)) {
        foreach ($categories as $category) {
            if (stripos($category['category_name'], $query) !== false) {
                $suggestions[] = [
                    'type' => 'category',
                    'id' => $category['category_id'],
                    'text' => $category['category_name']
                ];
            }
        }
    }

} catch (Exception $e) {
    error_log("Search suggestions error: " . $e->getMessage());
}

echo json_encode($suggestions);
exit();
