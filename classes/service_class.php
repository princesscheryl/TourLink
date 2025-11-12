<?php
require_once __DIR__.'/../settings/db_class.php';

/**
 * Service Class for TourLink Platform
 * Handles all operations related to tourism services (tl_services table)
 */
class Service extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Add new service to database
     * @param int $provider_id
     * @param int $category_id
     * @param string $title
     * @param string $description
     * @param float $base_price
     * @param string $pricing_unit (per_hour, per_day, per_person, flat_rate)
     * @param string $service_location
     * @param string $available_regions (JSON string)
     * @param int $max_capacity
     * @param string $service_images (JSON string)
     * @return int|bool Service ID on success, false on failure
     */
    public function add_service($provider_id, $category_id, $title, $description, $base_price, $pricing_unit, $service_location, $available_regions = null, $max_capacity = null, $service_images = null)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, service_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_approval')"
        );
        $stmt->bind_param("iissdsssis", $provider_id, $category_id, $title, $description, $base_price, $pricing_unit, $service_location, $available_regions, $max_capacity, $service_images);

        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Get all active services with category and provider details
     * @return array|bool Array of services or false on failure
     */
    public function get_all_services()
    {
        $sql = "SELECT s.*,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.region as provider_region,
                       sp.average_rating as provider_rating,
                       u.first_name as provider_first_name,
                       u.last_name as provider_last_name
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                WHERE s.service_status = 'active'
                AND s.availability_status = 'available'
                ORDER BY s.is_premium_listing DESC, s.date_created DESC";

        return $this->db_fetch_all($sql);
    }

    /**
     * Get service by ID with full details
     * @param int $service_id
     * @return array|bool Service details or false
     */
    public function get_service_by_id($service_id)
    {
        $sql = "SELECT s.*,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.region as provider_region,
                       sp.average_rating as provider_rating,
                       sp.total_bookings as provider_bookings,
                       sp.years_of_experience,
                       sp.languages_spoken,
                       u.first_name as provider_first_name,
                       u.last_name as provider_last_name,
                       u.phone as provider_phone,
                       u.email as provider_email
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                WHERE s.service_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get services by category
     * @param int $category_id
     * @return array|bool Array of services or false
     */
    public function get_services_by_category($category_id)
    {
        $sql = "SELECT s.*,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.average_rating as provider_rating
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                WHERE s.category_id = ?
                AND s.service_status = 'active'
                AND s.availability_status = 'available'
                ORDER BY s.is_premium_listing DESC, s.date_created DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get services by provider
     * @param int $provider_id
     * @return array|bool Array of services or false
     */
    public function get_services_by_provider($provider_id)
    {
        $sql = "SELECT s.*, sc.category_name
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                WHERE s.provider_id = ?
                ORDER BY s.date_created DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Search services by keyword
     * @param string $keyword
     * @return array|bool Array of services or false
     */
    public function search_services($keyword)
    {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT s.*,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.average_rating as provider_rating
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                WHERE (s.service_title LIKE ? OR s.service_description LIKE ?)
                AND s.service_status = 'active'
                AND s.availability_status = 'available'
                ORDER BY s.is_premium_listing DESC, s.views_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update service details
     * @param int $service_id
     * @param array $data Associative array of fields to update
     * @return bool True on success, false on failure
     */
    public function update_service($service_id, $data)
    {
        $allowed_fields = ['service_title', 'service_description', 'base_price', 'pricing_unit',
                          'service_location', 'available_regions', 'max_capacity', 'service_images',
                          'availability_status', 'service_status'];

        $updates = [];
        $params = [];
        $types = '';

        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
                $types .= is_numeric($value) ? (is_float($value) ? 'd' : 'i') : 's';
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $service_id;
        $types .= 'i';

        $sql = "UPDATE tl_services SET " . implode(', ', $updates) . " WHERE service_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);

        return $stmt->execute();
    }

    /**
     * Delete service
     * @param int $service_id
     * @return bool True on success, false on failure
     */
    public function delete_service($service_id)
    {
        $stmt = $this->db->prepare("DELETE FROM tl_services WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);

        return $stmt->execute();
    }

    /**
     * Increment service views count
     * @param int $service_id
     * @return bool True on success, false on failure
     */
    public function increment_views($service_id)
    {
        $stmt = $this->db->prepare("UPDATE tl_services SET views_count = views_count + 1 WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);

        return $stmt->execute();
    }

    /**
     * Get premium (featured) services
     * @param int $limit Number of services to retrieve
     * @return array|bool Array of premium services or false
     */
    public function get_premium_services($limit = 6)
    {
        $sql = "SELECT s.*,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.average_rating as provider_rating
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                WHERE s.is_premium_listing = 1
                AND s.service_status = 'active'
                AND s.availability_status = 'available'
                ORDER BY s.date_created DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
