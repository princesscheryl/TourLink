<?php
/**
 * Favorite Controller
 * Controller functions for favorites functionality
 */

require_once __DIR__ . '/../classes/favorite_class.php';

/**
 * Add a service to user's favorites
 * @param int $user_id
 * @param int $service_id
 * @return bool
 */
function add_favorite_ctr($user_id, $service_id) {
    $favorite = new Favorite();
    return $favorite->add_favorite_cls($user_id, $service_id);
}

/**
 * Remove a service from user's favorites
 * @param int $user_id
 * @param int $service_id
 * @return bool
 */
function remove_favorite_ctr($user_id, $service_id) {
    $favorite = new Favorite();
    return $favorite->remove_favorite_cls($user_id, $service_id);
}

/**
 * Get all favorited services for a user
 * @param int $user_id
 * @return array|false
 */
function get_user_favorites_ctr($user_id) {
    $favorite = new Favorite();
    return $favorite->get_user_favorites_cls($user_id);
}

/**
 * Check if a service is favorited by a user
 * @param int $user_id
 * @param int $service_id
 * @return bool
 */
function is_favorited_ctr($user_id, $service_id) {
    $favorite = new Favorite();
    return $favorite->check_is_favorited_cls($user_id, $service_id);
}

/**
 * Get count of user's favorites
 * @param int $user_id
 * @return int
 */
function get_favorite_count_ctr($user_id) {
    $favorite = new Favorite();
    return $favorite->get_favorite_count_cls($user_id);
}

/**
 * Get all users who favorited a specific service
 * @param int $service_id
 * @return array|false
 */
function get_service_favoriters_ctr($service_id) {
    $favorite = new Favorite();
    return $favorite->get_service_favoriters_cls($service_id);
}

/**
 * Get count of favorites for a specific service
 * @param int $service_id
 * @return int
 */
function get_service_favorite_count_ctr($service_id) {
    $favorite = new Favorite();
    return $favorite->get_service_favorite_count_cls($service_id);
}
?>
