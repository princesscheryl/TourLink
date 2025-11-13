<?php
require_once __DIR__.'/../settings/db_class.php';

/**
 * Service Provider Class for TourLink Platform
 * Handles service provider profile management (tl_service_providers table)
 */
class ServiceProvider extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Create service provider profile
     * @param int $user_id
     * @param string $business_name
     * @param string $business_description (not used - for backward compatibility)
     * @param string $business_location
     * @param string $primary_region
     * @return int|bool Provider ID on success, false on failure
     */
    public function create_provider($user_id, $business_name, $business_description = null, $business_location = null, $primary_region = null)
    {
        // Validate region matches ENUM values
        $valid_regions = ['Greater Accra', 'Central', 'Ashanti', 'Northern'];
        if (!in_array($primary_region, $valid_regions)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO tl_service_providers (user_id, business_name, location_details, region, verification_status)
            VALUES (?, ?, ?, ?, 'pending')"
        );

        $stmt->bind_param(
            "isss",
            $user_id,
            $business_name,
            $business_location,
            $primary_region
        );

        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Get provider by user ID
     * @param int $user_id
     * @return array|bool Provider data or false
     */
    public function get_provider_by_user_id($user_id)
    {
        $sql = "SELECT sp.*, u.email, u.first_name, u.last_name, u.phone
                FROM tl_service_providers sp
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                WHERE sp.user_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get provider by provider ID
     * @param int $provider_id
     * @return array|bool Provider data or false
     */
    public function get_provider_by_id($provider_id)
    {
        $sql = "SELECT sp.*, u.email, u.first_name, u.last_name, u.phone
                FROM tl_service_providers sp
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                WHERE sp.provider_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update provider profile
     * @param int $provider_id
     * @param array $data Associative array of fields to update
     * @return bool
     */
    public function update_provider($provider_id, $data)
    {
        $allowed_fields = ['business_name', 'business_registration_number', 'region',
                          'location_details', 'languages_spoken', 'years_of_experience'];

        $updates = [];
        $params = [];
        $types = '';

        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
                $types .= is_numeric($value) ? 'i' : 's';
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $provider_id;
        $types .= 'i';

        $sql = "UPDATE tl_service_providers SET " . implode(', ', $updates) . " WHERE provider_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);

        return $stmt->execute();
    }

    /**
     * Get all providers with filters
     * @param array $filters Optional filters (region, verification_status, etc.)
     * @return array|bool Array of providers or false
     */
    public function get_all_providers($filters = [])
    {
        $sql = "SELECT sp.*, u.email, u.first_name, u.last_name
                FROM tl_service_providers sp
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                WHERE 1=1";

        $params = [];
        $types = '';

        if (isset($filters['region'])) {
            $sql .= " AND sp.region = ?";
            $params[] = $filters['region'];
            $types .= 's';
        }

        if (isset($filters['verification_status'])) {
            $sql .= " AND sp.verification_status = ?";
            $params[] = $filters['verification_status'];
            $types .= 's';
        }

        $sql .= " ORDER BY sp.provider_id DESC";

        $stmt = $this->db->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update verification status
     * @param int $provider_id
     * @param string $status (pending, verified, rejected)
     * @return bool
     */
    public function update_verification_status($provider_id, $status)
    {
        $valid_statuses = ['pending', 'verified', 'rejected'];

        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE tl_service_providers SET verification_status = ? WHERE provider_id = ?");
        $stmt->bind_param("si", $status, $provider_id);

        return $stmt->execute();
    }

    /**
     * Update provider statistics (bookings, ratings, earnings)
     * @param int $provider_id
     * @return bool
     */
    public function update_statistics($provider_id)
    {
        $sql = "UPDATE tl_service_providers sp
                SET total_bookings = (
                    SELECT COUNT(*) FROM tl_bookings WHERE provider_id = ?
                ),
                average_rating = (
                    SELECT AVG(rating) FROM tl_reviews WHERE provider_id = ?
                ),
                total_earnings = (
                    SELECT SUM(provider_earnings)
                    FROM tl_bookings
                    WHERE provider_id = ? AND booking_status = 'completed'
                )
                WHERE provider_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iiii", $provider_id, $provider_id, $provider_id, $provider_id);

        return $stmt->execute();
    }

    /**
     * Check if user is already a provider
     * @param int $user_id
     * @return bool
     */
    public function is_provider($user_id)
    {
        $stmt = $this->db->prepare("SELECT provider_id FROM tl_service_providers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Pay onboarding fee
     * @param int $provider_id
     * @return bool
     */
    public function mark_onboarding_paid($provider_id)
    {
        $stmt = $this->db->prepare(
            "UPDATE tl_service_providers
            SET onboarding_fee_paid = TRUE, onboarding_payment_date = NOW()
            WHERE provider_id = ?"
        );
        $stmt->bind_param("i", $provider_id);

        return $stmt->execute();
    }
}
?>
