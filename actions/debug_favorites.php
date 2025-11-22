<?php
/**
 * Debug script to check favorites functionality
 * Access this file directly in browser to see diagnostic info
 * DELETE THIS FILE AFTER DEBUGGING
 */

require_once '../settings/core.php';
require_once '../settings/db_class.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Favorites Debug Info</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color:red'>Not logged in. Please log in first.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p><strong>User ID:</strong> $user_id</p>";

// Create database connection
$db = new db_connection();
$db->db_connect();

// 1. Check if favorites exist for this user
echo "<h2>1. Raw Favorites Data</h2>";
$sql = "SELECT * FROM tl_favorites WHERE user_id = $user_id";
$result = $db->db_fetch_all($sql);
echo "<pre>";
if ($result) {
    echo "Found " . count($result) . " favorite(s):\n";
    print_r($result);
} else {
    echo "No favorites found in tl_favorites table for this user.";
}
echo "</pre>";

// 2. Check if the joined query works
echo "<h2>2. Joined Query Results</h2>";
$sql2 = "SELECT
            f.favorite_id,
            f.user_id,
            f.service_id,
            f.added_date,
            s.service_title,
            s.service_status,
            s.availability_status,
            s.category_id,
            s.provider_id
        FROM tl_favorites f
        INNER JOIN tl_services s ON f.service_id = s.service_id
        WHERE f.user_id = $user_id";
$result2 = $db->db_fetch_all($sql2);
echo "<pre>";
if ($result2) {
    echo "Joined query returned " . count($result2) . " result(s):\n";
    print_r($result2);
} else {
    echo "Joined query returned no results. Possible causes:\n";
    echo "- Service may not exist in tl_services\n";
    echo "- Service may be inactive\n";
}
echo "</pre>";

// 3. Check service status values
echo "<h2>3. Service Status Check</h2>";
if ($result && count($result) > 0) {
    $service_id = $result[0]['service_id'];
    $sql3 = "SELECT service_id, service_title, service_status, availability_status FROM tl_services WHERE service_id = $service_id";
    $result3 = $db->db_fetch_one($sql3);
    echo "<pre>";
    if ($result3) {
        echo "Service data:\n";
        print_r($result3);
        echo "\n";
        if ($result3['service_status'] != 'active') {
            echo "WARNING: service_status is NOT 'active' (value: {$result3['service_status']})\n";
        }
        if ($result3['availability_status'] != 'available') {
            echo "WARNING: availability_status is NOT 'available' (value: {$result3['availability_status']})\n";
        }
    }
    echo "</pre>";
}

// 4. Check all service statuses
echo "<h2>4. All Distinct Service Statuses</h2>";
$sql4 = "SELECT DISTINCT service_status, availability_status FROM tl_services";
$result4 = $db->db_fetch_all($sql4);
echo "<pre>";
print_r($result4);
echo "</pre>";

// 5. Test the full query used by favorites
echo "<h2>5. Full Favorites Query Test</h2>";
$sql5 = "SELECT
            f.favorite_id,
            f.user_id,
            f.service_id,
            f.added_date,
            s.service_title as service_name,
            s.service_description,
            s.base_price,
            s.pricing_unit as pricing_type,
            s.service_images as service_image,
            s.service_status,
            s.service_location as location,
            sp.region as region,
            s.category_id,
            sc.category_name,
            s.provider_id,
            sp.business_name,
            sp.business_location
        FROM tl_favorites f
        INNER JOIN tl_services s ON f.service_id = s.service_id
        INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
        INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
        WHERE f.user_id = $user_id
          AND s.service_status = 'active'
          AND s.availability_status = 'available'
        ORDER BY f.added_date DESC";
$result5 = $db->db_fetch_all($sql5);
echo "<pre>";
if ($result5) {
    echo "Full query returned " . count($result5) . " result(s):\n";
    print_r($result5);
} else {
    echo "Full query returned NO results.\n";
    echo "This is the query used by my_favorites.php.\n";
    echo "Check the warnings above for possible causes.\n";
}
echo "</pre>";

echo "<hr><p><strong>Delete this file after debugging!</strong></p>";
?>
