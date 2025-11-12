<?php
require_once __DIR__.'/../settings/db_class.php';

/**
 * Service Category Class for TourLink Platform
 * Handles all operations related to service categories (tl_service_categories table)
 */
class ServiceCategory extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Add new service category
     * @param string $category_name
     * @param string $category_description
     * @param string $icon_url
     * @param int $display_order
     * @return int|bool Category ID on success, false on failure
     */
    public function add_category($category_name, $category_description = null, $icon_url = null, $display_order = 0)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tl_service_categories (category_name, category_description, icon_url, display_order)
            VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("sssi", $category_name, $category_description, $icon_url, $display_order);

        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Get all service categories
     * @return array|bool Array of categories or false
     */
    public function get_all_categories()
    {
        $sql = "SELECT * FROM tl_service_categories ORDER BY display_order ASC, category_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get category by ID
     * @param int $category_id
     * @return array|bool Category details or false
     */
    public function get_category_by_id($category_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_service_categories WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Update category
     * @param int $category_id
     * @param string $category_name
     * @param string $category_description
     * @param string $icon_url
     * @param int $display_order
     * @return bool True on success, false on failure
     */
    public function update_category($category_id, $category_name, $category_description = null, $icon_url = null, $display_order = 0)
    {
        $stmt = $this->db->prepare(
            "UPDATE tl_service_categories
            SET category_name = ?, category_description = ?, icon_url = ?, display_order = ?
            WHERE category_id = ?"
        );
        $stmt->bind_param("sssii", $category_name, $category_description, $icon_url, $display_order, $category_id);

        return $stmt->execute();
    }

    /**
     * Delete category
     * @param int $category_id
     * @return bool True on success, false on failure
     */
    public function delete_category($category_id)
    {
        $stmt = $this->db->prepare("DELETE FROM tl_service_categories WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);

        return $stmt->execute();
    }

    /**
     * Get category with service count
     * @return array|bool Array of categories with counts or false
     */
    public function get_categories_with_count()
    {
        $sql = "SELECT sc.*,
                       COUNT(s.service_id) as service_count
                FROM tl_service_categories sc
                LEFT JOIN tl_services s ON sc.category_id = s.category_id
                    AND s.service_status = 'active'
                    AND s.availability_status = 'available'
                GROUP BY sc.category_id
                ORDER BY sc.display_order ASC, sc.category_name ASC";

        return $this->db_fetch_all($sql);
    }
}
?>
