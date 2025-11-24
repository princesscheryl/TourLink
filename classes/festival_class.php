<?php
require_once __DIR__ . '/../settings/db_class.php';

/**
 * Festival Class for TourLink Platform
 * Handles Ghana Festival Calendar operations
 */
class Festival extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Get all active festivals
     */
    public function get_all_festivals()
    {
        $sql = "SELECT * FROM tl_festivals WHERE is_active = 1 ORDER BY start_date ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get upcoming festivals
     */
    public function get_upcoming_festivals($limit = 6)
    {
        $sql = "SELECT * FROM tl_festivals
                WHERE is_active = 1 AND start_date >= CURDATE()
                ORDER BY start_date ASC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get featured festivals
     */
    public function get_featured_festivals($limit = 4)
    {
        $sql = "SELECT * FROM tl_festivals
                WHERE is_active = 1 AND is_featured = 1
                ORDER BY start_date ASC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get festival by ID
     */
    public function get_festival_by_id($festival_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_festivals WHERE festival_id = ?");
        $stmt->bind_param("i", $festival_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get festivals by region
     */
    public function get_festivals_by_region($region)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_festivals WHERE region = ? AND is_active = 1 ORDER BY start_date ASC");
        $stmt->bind_param("s", $region);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get festivals by month
     */
    public function get_festivals_by_month($month)
    {
        $stmt = $this->db->prepare("SELECT * FROM tl_festivals WHERE (MONTH(start_date) = ? OR month_typically = ?) AND is_active = 1 ORDER BY start_date ASC");
        $stmt->bind_param("ii", $month, $month);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Add new festival
     */
    public function add_festival($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tl_festivals (festival_name, description, region, location, start_date, end_date, month_typically, festival_type, image_url, is_featured)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssssisii",
            $data['festival_name'],
            $data['description'],
            $data['region'],
            $data['location'],
            $data['start_date'],
            $data['end_date'],
            $data['month_typically'],
            $data['festival_type'],
            $data['image_url'],
            $data['is_featured']
        );
        return $stmt->execute();
    }

    /**
     * Update festival
     */
    public function update_festival($festival_id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE tl_festivals SET festival_name = ?, description = ?, region = ?, location = ?, start_date = ?, end_date = ?, festival_type = ?, is_featured = ? WHERE festival_id = ?"
        );
        $stmt->bind_param(
            "sssssssii",
            $data['festival_name'],
            $data['description'],
            $data['region'],
            $data['location'],
            $data['start_date'],
            $data['end_date'],
            $data['festival_type'],
            $data['is_featured'],
            $festival_id
        );
        return $stmt->execute();
    }

    /**
     * Delete festival
     */
    public function delete_festival($festival_id)
    {
        $stmt = $this->db->prepare("DELETE FROM tl_festivals WHERE festival_id = ?");
        $stmt->bind_param("i", $festival_id);
        return $stmt->execute();
    }
}
?>
