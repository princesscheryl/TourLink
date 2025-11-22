<?php
/**
 * Booking Controller
 * Controller functions for booking operations
 */

require_once __DIR__ . '/../classes/booking_class.php';

/**
 * Create a new booking
 * @param array $booking_data
 * @return int|bool Booking ID or false
 */
function create_booking_ctr($booking_data)
{
    $booking = new Booking();
    return $booking->create_booking($booking_data);
}

/**
 * Get bookings for a tourist
 * @param int $tourist_id
 * @return array
 */
function get_tourist_bookings_ctr($tourist_id)
{
    $booking = new Booking();
    return $booking->get_tourist_bookings($tourist_id);
}

/**
 * Get bookings for a provider
 * @param int $provider_id
 * @return array
 */
function get_provider_bookings_ctr($provider_id)
{
    $booking = new Booking();
    return $booking->get_provider_bookings($provider_id);
}

/**
 * Get booking by ID
 * @param int $booking_id
 * @return array|bool
 */
function get_booking_by_id_ctr($booking_id)
{
    $booking = new Booking();
    return $booking->get_booking_by_id($booking_id);
}

/**
 * Get booking by reference
 * @param string $booking_reference
 * @return array|bool
 */
function get_booking_by_reference_ctr($booking_reference)
{
    $booking = new Booking();
    return $booking->get_booking_by_reference($booking_reference);
}

/**
 * Update booking status
 * @param int $booking_id
 * @param string $status
 * @return bool
 */
function update_booking_status_ctr($booking_id, $status)
{
    $booking = new Booking();
    return $booking->update_booking_status($booking_id, $status);
}

/**
 * Cancel booking
 * @param int $booking_id
 * @param string $cancelled_by
 * @param string $reason
 * @return bool
 */
function cancel_booking_ctr($booking_id, $cancelled_by, $reason)
{
    $booking = new Booking();
    return $booking->cancel_booking($booking_id, $cancelled_by, $reason);
}

/**
 * Complete booking
 * @param int $booking_id
 * @return bool
 */
function complete_booking_ctr($booking_id)
{
    $booking = new Booking();
    return $booking->complete_booking($booking_id);
}

/**
 * Get provider statistics
 * @param int $provider_id
 * @return array
 */
function get_provider_statistics_ctr($provider_id)
{
    $booking = new Booking();
    return $booking->get_provider_statistics($provider_id);
}
?>
