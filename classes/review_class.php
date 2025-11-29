<?php
require_once __DIR__.'/../settings/db_class.php';

/**
 * Review Class for TourLink Platform
 * Handles all review operations (tl_reviews table)
 */
class Review extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Submit a new review
     * @param int $service_id
     * @param int $provider_id
     * @param int $tourist_id
     * @param int $booking_id
     * @param int $rating (1-5)
     * @param string $review_title
     * @param string $review_text
     * @return int|bool Review ID on success, false on failure
     */
    public function submit_review($service_id, $provider_id, $tourist_id, $booking_id, $rating, $review_title, $review_text)
    {
        // Check if user already reviewed this booking
        if ($this->has_reviewed_booking($booking_id, $tourist_id)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO tl_reviews (service_id, provider_id, tourist_id, booking_id, rating, review_title, review_text)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("iiiiiss", $service_id, $provider_id, $tourist_id, $booking_id, $rating, $review_title, $review_text);

        if ($stmt->execute()) {
            // Update service average rating
            $this->update_service_rating($service_id);
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Check if user has already reviewed a booking
     * @param int $booking_id
     * @param int $tourist_id
     * @return bool
     */
    public function has_reviewed_booking($booking_id, $tourist_id)
    {
        $stmt = $this->db->prepare(
            "SELECT review_id FROM tl_reviews WHERE booking_id = ? AND tourist_id = ?"
        );
        $stmt->bind_param("ii", $booking_id, $tourist_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Get all reviews for a service
     * @param int $service_id
     * @param int $limit Optional limit
     * @param int $offset Optional offset for pagination
     * @return array Array of reviews
     */
    public function get_service_reviews($service_id, $limit = null, $offset = 0)
    {
        $sql = "SELECT r.*,
                       u.first_name as tourist_first_name,
                       u.last_name as tourist_last_name,
                       b.booking_reference,
                       b.service_date
                FROM tl_reviews r
                INNER JOIN tl_users u ON r.tourist_id = u.user_id
                LEFT JOIN tl_bookings b ON r.booking_id = b.booking_id
                WHERE r.service_id = ?
                ORDER BY r.review_date DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit) {
            $stmt->bind_param("iii", $service_id, $limit, $offset);
        } else {
            $stmt->bind_param("i", $service_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get review statistics for a service
     * @param int $service_id
     * @return array Statistics including average rating and count by star
     */
    public function get_service_review_stats($service_id)
    {
        $sql = "SELECT
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM tl_reviews
                WHERE service_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get user's completed bookings for a service (to check if they can review)
     * @param int $tourist_id
     * @param int $service_id
     * @return array Array of completed bookings
     */
    public function get_user_completed_bookings($tourist_id, $service_id)
    {
        $sql = "SELECT b.booking_id, b.booking_reference, b.service_date, b.completion_date,
                       (SELECT COUNT(*) FROM tl_reviews r WHERE r.booking_id = b.booking_id) as has_review
                FROM tl_bookings b
                WHERE b.tourist_id = ?
                AND b.service_id = ?
                AND b.booking_status = 'completed'
                ORDER BY b.completion_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $tourist_id, $service_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Add provider response to a review
     * @param int $review_id
     * @param string $provider_response
     * @return bool True on success, false on failure
     */
    public function add_provider_response($review_id, $provider_response)
    {
        $stmt = $this->db->prepare(
            "UPDATE tl_reviews
            SET response_from_provider = ?,
                response_date = NOW()
            WHERE review_id = ?"
        );

        $stmt->bind_param("si", $provider_response, $review_id);

        return $stmt->execute();
    }

    /**
     * Update service average rating
     * @param int $service_id
     * @return bool
     */
    private function update_service_rating($service_id)
    {
        try {
            $stats = $this->get_service_review_stats($service_id);

            if ($stats && $stats['total_reviews'] > 0) {
                $stmt = $this->db->prepare(
                    "UPDATE tl_services
                    SET average_rating = ?,
                        total_reviews = ?
                    WHERE service_id = ?"
                );

                if ($stmt) {
                    $avg_rating = round($stats['average_rating'], 1);
                    $total = $stats['total_reviews'];

                    $stmt->bind_param("dii", $avg_rating, $total, $service_id);

                    return $stmt->execute();
                }
            }
        } catch (Exception $e) {
            // Silently fail if columns don't exist yet
            // Review is still saved, just rating won't be cached
        }

        return false;
    }

    /**
     * Delete a review (admin only)
     * @param int $review_id
     * @return bool
     */
    public function delete_review($review_id)
    {
        // Get service_id before deleting
        $stmt = $this->db->prepare("SELECT service_id FROM tl_reviews WHERE review_id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $review = $result->fetch_assoc();

        if (!$review) {
            return false;
        }

        // Delete the review
        $stmt = $this->db->prepare("DELETE FROM tl_reviews WHERE review_id = ?");
        $stmt->bind_param("i", $review_id);

        if ($stmt->execute()) {
            // Update service rating
            $this->update_service_rating($review['service_id']);
            return true;
        }

        return false;
    }

    /**
     * Get review by ID
     * @param int $review_id
     * @return array|bool Review data or false
     */
    public function get_review_by_id($review_id)
    {
        $sql = "SELECT r.*,
                       s.provider_id,
                       u.first_name as tourist_first_name,
                       u.last_name as tourist_last_name
                FROM tl_reviews r
                INNER JOIN tl_services s ON r.service_id = s.service_id
                INNER JOIN tl_users u ON r.tourist_id = u.user_id
                WHERE r.review_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get total review count for a service
     * @param int $service_id
     * @return int
     */
    public function get_review_count($service_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM tl_reviews WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['count'];
    }

    /**
     * Get all reviews for a provider (across all their services)
     * @param int $provider_id
     * @param int $limit Optional limit
     * @param int $offset Optional offset for pagination
     * @return array Array of reviews
     */
    public function get_provider_reviews($provider_id, $limit = null, $offset = 0)
    {
        $sql = "SELECT r.*,
                       u.first_name as tourist_first_name,
                       u.last_name as tourist_last_name,
                       s.service_title,
                       s.service_id,
                       b.booking_reference,
                       b.service_date
                FROM tl_reviews r
                INNER JOIN tl_users u ON r.tourist_id = u.user_id
                INNER JOIN tl_services s ON r.service_id = s.service_id
                LEFT JOIN tl_bookings b ON r.booking_id = b.booking_id
                WHERE r.provider_id = ?
                ORDER BY r.review_date DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit) {
            $stmt->bind_param("iii", $provider_id, $limit, $offset);
        } else {
            $stmt->bind_param("i", $provider_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get review statistics for a provider
     * @param int $provider_id
     * @return array Statistics including average rating and count
     */
    public function get_provider_review_stats($provider_id)
    {
        $sql = "SELECT
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN response_from_provider IS NULL OR response_from_provider = '' THEN 1 ELSE 0 END) as unanswered_reviews,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM tl_reviews
                WHERE provider_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }
}
?>
