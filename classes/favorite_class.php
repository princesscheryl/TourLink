<?php
/**
 * Favorite Class
 * Handles all database operations for the tl_favorites table
 */

require_once __DIR__ . '/../settings/db_class.php';

class Favorite extends db_connection {

    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Add a service to user's favorites
     * @param int $user_id
     * @param int $service_id
     * @return bool
     */
    public function add_favorite_cls($user_id, $service_id) {
        // Check if already favorited to avoid duplicate entry error
        if ($this->check_is_favorited_cls($user_id, $service_id)) {
            return false; // Already favorited
        }

        $sql = "INSERT INTO tl_favorites (user_id, service_id, added_date)
                VALUES (?, ?, NOW())";

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $service_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }

    /**
     * Remove a service from user's favorites
     * @param int $user_id
     * @param int $service_id
     * @return bool
     */
    public function remove_favorite_cls($user_id, $service_id) {
        $sql = "DELETE FROM tl_favorites
                WHERE user_id = ? AND service_id = ?";

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $service_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }

    /**
     * Get all favorited services for a user
     * @param int $user_id
     * @return array|false
     */
    public function get_user_favorites_cls($user_id) {
        $sql = "SELECT
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
                WHERE f.user_id = ?
                  AND s.service_status = 'active'
                  AND s.availability_status = 'available'
                ORDER BY f.added_date DESC";

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $favorites = [];
                while ($row = $result->fetch_assoc()) {
                    $favorites[] = $row;
                }
                $stmt->close();
                return $favorites;
            }

            $stmt->close();
        }

        return false;
    }

    /**
     * Check if a service is favorited by a user
     * @param int $user_id
     * @param int $service_id
     * @return bool
     */
    public function check_is_favorited_cls($user_id, $service_id) {
        $sql = "SELECT favorite_id
                FROM tl_favorites
                WHERE user_id = ? AND service_id = ?";

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $is_favorited = ($result->num_rows > 0);
            $stmt->close();
            return $is_favorited;
        }

        return false;
    }

    /**
     * Get count of user's favorites
     * @param int $user_id
     * @return int
     */
    public function get_favorite_count_cls($user_id) {
        $sql = "SELECT COUNT(*) as count
                FROM tl_favorites f
                INNER JOIN tl_services s ON f.service_id = s.service_id
                WHERE f.user_id = ?
                  AND s.service_status = 'active'
                  AND s.availability_status = 'available'";

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $count = $row['count'];
                $stmt->close();
                return $count;
            }

            $stmt->close();
        }

        return 0;
    }

    /**
     * Get all users who favorited a specific service
     * @param int $service_id
     * @return array|false
     */
    public function get_service_favoriters_cls($service_id) {
        $sql = "SELECT
                    f.user_id,
                    f.added_date,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM tl_favorites f
                INNER JOIN tl_users u ON f.user_id = u.user_id
                WHERE f.service_id = ?
                ORDER BY f.added_date DESC";

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $service_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $favoriters = [];
                while ($row = $result->fetch_assoc()) {
                    $favoriters[] = $row;
                }
                $stmt->close();
                return $favoriters;
            }

            $stmt->close();
        }

        return false;
    }

    /**
     * Get count of favorites for a specific service
     * @param int $service_id
     * @return int
     */
    public function get_service_favorite_count_cls($service_id) {
        $sql = "SELECT COUNT(*) as count
                FROM tl_favorites
                WHERE service_id = ?";

        $stmt = $this->db->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $service_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $count = $row['count'];
                $stmt->close();
                return $count;
            }

            $stmt->close();
        }

        return 0;
    }
}
?>
