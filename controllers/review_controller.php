<?php
/**
 * Review Controller
 * Controller layer for review operations
 */

require_once __DIR__.'/../classes/review_class.php';

/**
 * Submit a new review
 * @param int $service_id
 * @param int $tourist_id
 * @param int $booking_id
 * @param int $rating
 * @param string $review_title
 * @param string $review_text
 * @return int|bool Review ID on success, false on failure
 */
function submit_review_ctr($service_id, $tourist_id, $booking_id, $rating, $review_title, $review_text)
{
    $review = new Review();
    return $review->submit_review($service_id, $tourist_id, $booking_id, $rating, $review_title, $review_text);
}

/**
 * Check if user has already reviewed a booking
 * @param int $booking_id
 * @param int $tourist_id
 * @return bool
 */
function has_reviewed_booking_ctr($booking_id, $tourist_id)
{
    $review = new Review();
    return $review->has_reviewed_booking($booking_id, $tourist_id);
}

/**
 * Get all reviews for a service
 * @param int $service_id
 * @param int $limit Optional limit
 * @param int $offset Optional offset
 * @return array
 */
function get_service_reviews_ctr($service_id, $limit = null, $offset = 0)
{
    $review = new Review();
    return $review->get_service_reviews($service_id, $limit, $offset);
}

/**
 * Get review statistics for a service
 * @param int $service_id
 * @return array
 */
function get_service_review_stats_ctr($service_id)
{
    $review = new Review();
    return $review->get_service_review_stats($service_id);
}

/**
 * Get user's completed bookings for a service
 * @param int $tourist_id
 * @param int $service_id
 * @return array
 */
function get_user_completed_bookings_ctr($tourist_id, $service_id)
{
    $review = new Review();
    return $review->get_user_completed_bookings($tourist_id, $service_id);
}

/**
 * Add provider response to a review
 * @param int $review_id
 * @param string $provider_response
 * @return bool
 */
function add_provider_response_ctr($review_id, $provider_response)
{
    $review = new Review();
    return $review->add_provider_response($review_id, $provider_response);
}

/**
 * Get review by ID
 * @param int $review_id
 * @return array|bool
 */
function get_review_by_id_ctr($review_id)
{
    $review = new Review();
    return $review->get_review_by_id($review_id);
}

/**
 * Delete a review
 * @param int $review_id
 * @return bool
 */
function delete_review_ctr($review_id)
{
    $review = new Review();
    return $review->delete_review($review_id);
}

/**
 * Get total review count for a service
 * @param int $service_id
 * @return int
 */
function get_review_count_ctr($service_id)
{
    $review = new Review();
    return $review->get_review_count($service_id);
}
?>
