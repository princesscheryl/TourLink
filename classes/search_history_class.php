<?php
require_once __DIR__ . '/../settings/db_class.php';

/**
 * Search History Class
 * Handles database operations for tl_search_history table
 */
class SearchHistory extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Save a search query to history
     * @param int $user_id
     * @param string $search_query
     * @param array $filters Optional filters used (category, region, price range)
     * @return bool
     */
    public function save_search($user_id, $search_query, $filters = null)
    {
        // Don't save empty searches
        if (empty(trim($search_query))) {
            return false;
        }

        // Check if this exact search already exists recently (within last hour)
        // If so, just update the timestamp instead of creating duplicate
        $existing = $this->get_recent_duplicate($user_id, $search_query);
        if ($existing) {
            return $this->update_search_date($existing['search_id']);
        }

        $filters_json = $filters ? json_encode($filters) : null;

        $stmt = $this->db->prepare(
            "INSERT INTO tl_search_history (user_id, search_query, filters_used, search_date)
            VALUES (?, ?, ?, NOW())"
        );

        $stmt->bind_param("iss", $user_id, $search_query, $filters_json);

        if ($stmt->execute()) {
            // Keep only the 10 most recent searches per user
            $this->cleanup_old_searches($user_id);
            return true;
        }

        return false;
    }

    /**
     * Check for recent duplicate search (within last hour)
     * @param int $user_id
     * @param string $search_query
     * @return array|null
     */
    private function get_recent_duplicate($user_id, $search_query)
    {
        $stmt = $this->db->prepare(
            "SELECT search_id FROM tl_search_history
            WHERE user_id = ? AND search_query = ?
            AND search_date > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            LIMIT 1"
        );

        $stmt->bind_param("is", $user_id, $search_query);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Update search date for existing search
     * @param int $search_id
     * @return bool
     */
    private function update_search_date($search_id)
    {
        $stmt = $this->db->prepare(
            "UPDATE tl_search_history SET search_date = NOW() WHERE search_id = ?"
        );

        $stmt->bind_param("i", $search_id);
        return $stmt->execute();
    }

    /**
     * Get recent searches for a user
     * @param int $user_id
     * @param int $limit Number of searches to return (default 10)
     * @return array
     */
    public function get_recent_searches($user_id, $limit = 10)
    {
        $stmt = $this->db->prepare(
            "SELECT search_id, search_query, filters_used, search_date
            FROM tl_search_history
            WHERE user_id = ?
            ORDER BY search_date DESC
            LIMIT ?"
        );

        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $searches = [];
        while ($row = $result->fetch_assoc()) {
            // Decode filters JSON
            if ($row['filters_used']) {
                $row['filters_used'] = json_decode($row['filters_used'], true);
            }
            $searches[] = $row;
        }

        return $searches;
    }

    /**
     * Delete a specific search from history
     * @param int $search_id
     * @param int $user_id For security - ensure user owns this search
     * @return bool
     */
    public function delete_search($search_id, $user_id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM tl_search_history WHERE search_id = ? AND user_id = ?"
        );

        $stmt->bind_param("ii", $search_id, $user_id);
        return $stmt->execute();
    }

    /**
     * Clear all search history for a user
     * @param int $user_id
     * @return bool
     */
    public function clear_all_searches($user_id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM tl_search_history WHERE user_id = ?"
        );

        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    /**
     * Keep only the most recent searches (cleanup old ones)
     * @param int $user_id
     * @param int $keep Number of searches to keep (default 10)
     * @return bool
     */
    private function cleanup_old_searches($user_id, $keep = 10)
    {
        // Get the search_id of the Nth most recent search
        $stmt = $this->db->prepare(
            "SELECT search_id FROM tl_search_history
            WHERE user_id = ?
            ORDER BY search_date DESC
            LIMIT 1 OFFSET ?"
        );

        $offset = $keep;
        $stmt->bind_param("ii", $user_id, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            // Delete all searches older than this one
            $stmt2 = $this->db->prepare(
                "DELETE FROM tl_search_history
                WHERE user_id = ? AND search_id <= ?"
            );

            $stmt2->bind_param("ii", $user_id, $row['search_id']);
            return $stmt2->execute();
        }

        return true;
    }
}
?>
