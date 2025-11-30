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
     * @param int $festival_id (optional - link service to a festival)
     * @return int|bool Service ID on success, false on failure
     */
    public function add_service($provider_id, $category_id, $title, $description, $base_price, $pricing_unit, $service_location, $available_regions = null, $max_capacity = null, $service_images = null, $festival_id = null)
    {
        // Check if festival_id column exists (backward compatibility)
        $check_column = $this->db->query("SHOW COLUMNS FROM tl_services LIKE 'festival_id'");
        $has_festival_column = ($check_column && $check_column->num_rows > 0);

        if ($has_festival_column && $festival_id !== null) {
            // Include festival_id in the query
            $stmt = $this->db->prepare(
                "INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, festival_id, service_status, availability_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 'available')"
            );
            $stmt->bind_param("iissdssisis", $provider_id, $category_id, $title, $description, $base_price, $pricing_unit, $service_location, $available_regions, $max_capacity, $service_images, $festival_id);
        } else {
            // Exclude festival_id from the query
            $stmt = $this->db->prepare(
                "INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, service_status, availability_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 'available')"
            );
            $stmt->bind_param("iissdssisi", $provider_id, $category_id, $title, $description, $base_price, $pricing_unit, $service_location, $available_regions, $max_capacity, $service_images);
        }

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
                       COALESCE(s.average_rating, 0) as average_rating,
                       COALESCE(s.total_reviews, 0) as total_reviews,
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
                       u.user_id as provider_user_id,
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
                       sp.average_rating as provider_rating,
                       COALESCE(s.average_rating, 0) as average_rating,
                       COALESCE(s.total_reviews, 0) as total_reviews
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
                       sp.average_rating as provider_rating,
                       COALESCE(s.average_rating, 0) as average_rating,
                       COALESCE(s.total_reviews, 0) as total_reviews
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
        $allowed_fields = ['service_title', 'category_id', 'service_description', 'base_price', 'pricing_unit',
                          'service_location', 'available_regions', 'max_capacity', 'service_images',
                          'availability_status', 'service_status', 'festival_id'];

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
     * Get services by festival
     * @param int $festival_id
     * @return array|bool Array of services or false
     */
    public function get_services_by_festival($festival_id)
    {
        $sql = "SELECT s.*,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.region as provider_region,
                       sp.average_rating as provider_rating,
                       COALESCE(s.average_rating, 0) as average_rating,
                       COALESCE(s.total_reviews, 0) as total_reviews,
                       u.first_name as provider_first_name,
                       u.last_name as provider_last_name,
                       f.festival_name,
                       f.start_date as festival_start_date,
                       f.end_date as festival_end_date
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                LEFT JOIN tl_festivals f ON s.festival_id = f.festival_id
                WHERE s.festival_id = ?
                AND s.service_status = 'active'
                AND s.availability_status = 'available'
                ORDER BY s.is_premium_listing DESC, s.date_created DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $festival_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get premium (featured) services
     * @param int $limit Number of services to retrieve
     * @return array|bool Array of premium services or false
     */
    public function get_premium_services($limit = 6)
    {
        // Check which premium column exists
        $check_premium_col = $this->db->query("SHOW COLUMNS FROM tl_services LIKE 'is_premium'");
        $has_is_premium = $check_premium_col && $check_premium_col->num_rows > 0;
        
        $check_premium_listing_col = $this->db->query("SHOW COLUMNS FROM tl_services LIKE 'is_premium_listing'");
        $has_is_premium_listing = $check_premium_listing_col && $check_premium_listing_col->num_rows > 0;
        
        // Build WHERE clause based on available columns
        $premium_condition = "";
        if ($has_is_premium) {
            $premium_condition = "s.is_premium = 1";
        } elseif ($has_is_premium_listing) {
            $premium_condition = "s.is_premium_listing = 1";
        } else {
            // No premium column exists, return empty array
            return [];
        }
        
        $sql = "SELECT s.*,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.average_rating as provider_rating,
                       sp.verification_status,
                       AVG(r.rating) as average_rating,
                       COUNT(DISTINCT b.booking_id) as total_bookings
                FROM tl_services s
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                LEFT JOIN tl_reviews r ON s.service_id = r.service_id
                LEFT JOIN tl_bookings b ON s.service_id = b.service_id
                WHERE $premium_condition
                AND s.service_status = 'active'
                AND sp.verification_status = 'verified'
                AND u.account_status = 'active'
                GROUP BY s.service_id
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
