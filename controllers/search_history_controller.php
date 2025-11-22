<?php
/**
 * Search History Controller
 * Controller functions for search history operations
 */

require_once __DIR__ . '/../classes/search_history_class.php';

/**
 * Save a search to history
 * @param int $user_id
 * @param string $search_query
 * @param array $filters Optional filters
 * @return bool
 */
function save_search_ctr($user_id, $search_query, $filters = null)
{
    $search_history = new SearchHistory();
    return $search_history->save_search($user_id, $search_query, $filters);
}

/**
 * Get recent searches for a user
 * @param int $user_id
 * @param int $limit
 * @return array
 */
function get_recent_searches_ctr($user_id, $limit = 10)
{
    $search_history = new SearchHistory();
    return $search_history->get_recent_searches($user_id, $limit);
}

/**
 * Delete a specific search
 * @param int $search_id
 * @param int $user_id
 * @return bool
 */
function delete_search_ctr($search_id, $user_id)
{
    $search_history = new SearchHistory();
    return $search_history->delete_search($search_id, $user_id);
}

/**
 * Clear all search history
 * @param int $user_id
 * @return bool
 */
function clear_search_history_ctr($user_id)
{
    $search_history = new SearchHistory();
    return $search_history->clear_all_searches($user_id);
}
?>
