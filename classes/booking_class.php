<?php
require_once __DIR__.'/../settings/db_class.php';

/**
 * Booking Class for TourLink Platform
 * Handles all booking/order operations (tl_bookings table)
 */
class Booking extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Create a new booking
     * @param array $booking_data Associative array with booking details
     * @return int|bool Booking ID on success, false on failure
     */
    public function create_booking($booking_data)
    {
        $booking_reference = $this->generate_booking_reference();

        // Handle service_duration (default to 1 if not provided)
        $service_duration = isset($booking_data['service_duration']) ? (int)$booking_data['service_duration'] : 1;

        // Handle guest_details (optional)
        $guest_details = isset($booking_data['guest_details']) ? $booking_data['guest_details'] : null;

        $stmt = $this->db->prepare(
            "INSERT INTO tl_bookings (booking_reference, service_id, tourist_id, provider_id,
            service_date, service_time, number_of_people, service_duration, original_amount, discount_amount,
            total_amount, commission_amount, provider_earnings, special_requests, guest_details)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "siiissiidddddss",
            $booking_reference,
            $booking_data['service_id'],
            $booking_data['tourist_id'],
            $booking_data['provider_id'],
            $booking_data['service_date'],
            $booking_data['service_time'],
            $booking_data['number_of_people'],
            $service_duration,
            $booking_data['original_amount'],
            $booking_data['discount_amount'],
            $booking_data['total_amount'],
            $booking_data['commission_amount'],
            $booking_data['provider_earnings'],
            $booking_data['special_requests'],
            $guest_details
        );

        if ($stmt->execute()) {
            return $this->last_insert_id();
        }
        return false;
    }

    /**
     * Generate unique booking reference
     * Format: BK-YYYYMMDD-XXXXX
     * @return string Booking reference
     */
    private function generate_booking_reference()
    {
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return "BK-{$date}-{$random}";
    }

    /**
     * Get all bookings for a tourist
     * @param int $tourist_id
     * @return array|bool Array of bookings or false
     */
    public function get_tourist_bookings($tourist_id)
    {
        $sql = "SELECT b.*,
                       s.service_title,
                       s.service_images,
                       sc.category_name,
                       sp.business_name as provider_name,
                       u.phone as provider_phone
                FROM tl_bookings b
                INNER JOIN tl_services s ON b.service_id = s.service_id
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON b.provider_id = sp.provider_id
                INNER JOIN tl_users u ON sp.user_id = u.user_id
                WHERE b.tourist_id = ?
                ORDER BY b.booking_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tourist_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all bookings for a provider
     * @param int $provider_id
     * @return array|bool Array of bookings or false
     */
    public function get_provider_bookings($provider_id)
    {
        $sql = "SELECT b.*,
                       s.service_title,
                       u.first_name as tourist_first_name,
                       u.last_name as tourist_last_name,
                       u.phone as tourist_phone,
                       u.email as tourist_email
                FROM tl_bookings b
                INNER JOIN tl_services s ON b.service_id = s.service_id
                INNER JOIN tl_users u ON b.tourist_id = u.user_id
                WHERE b.provider_id = ?
                ORDER BY b.service_date DESC, b.booking_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get booking by ID with full details
     * @param int $booking_id
     * @return array|bool Booking details or false
     */
    public function get_booking_by_id($booking_id)
    {
        $sql = "SELECT b.*,
                       s.service_title,
                       s.service_description,
                       s.service_images,
                       sc.category_name,
                       sp.business_name as provider_name,
                       sp.provider_id,
                       pu.phone as provider_phone,
                       pu.email as provider_email,
                       tu.first_name as tourist_first_name,
                       tu.last_name as tourist_last_name,
                       tu.phone as tourist_phone,
                       tu.email as tourist_email
                FROM tl_bookings b
                INNER JOIN tl_services s ON b.service_id = s.service_id
                INNER JOIN tl_service_categories sc ON s.category_id = sc.category_id
                INNER JOIN tl_service_providers sp ON b.provider_id = sp.provider_id
                INNER JOIN tl_users pu ON sp.user_id = pu.user_id
                INNER JOIN tl_users tu ON b.tourist_id = tu.user_id
                WHERE b.booking_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get booking by reference
     * @param string $booking_reference
     * @return array|bool Booking details or false
     */
    public function get_booking_by_reference($booking_reference)
    {
        $sql = "SELECT b.*, s.service_title, sp.business_name as provider_name
                FROM tl_bookings b
                INNER JOIN tl_services s ON b.service_id = s.service_id
                INNER JOIN tl_service_providers sp ON b.provider_id = sp.provider_id
                WHERE b.booking_reference = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $booking_reference);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Update booking status
     * @param int $booking_id
     * @param string $status (pending, confirmed, in_progress, completed, cancelled, refunded)
     * @return bool True on success, false on failure
     */
    public function update_booking_status($booking_id, $status)
    {
        $valid_statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'refunded'];

        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE tl_bookings SET booking_status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $status, $booking_id);

        return $stmt->execute();
    }

    /**
     * Update payment status
     * @param int $booking_id
     * @param string $payment_status (pending, escrow, released, refunded)
     * @return bool True on success, false on failure
     */
    public function update_payment_status($booking_id, $payment_status)
    {
        $valid_statuses = ['pending', 'escrow', 'released', 'refunded'];

        if (!in_array($payment_status, $valid_statuses)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE tl_bookings SET payment_status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $payment_status, $booking_id);

        return $stmt->execute();
    }

    /**
     * Cancel booking
     * @param int $booking_id
     * @param string $cancelled_by (tourist, provider, admin)
     * @param string $cancellation_reason
     * @return bool True on success, false on failure
     */
    public function cancel_booking($booking_id, $cancelled_by, $cancellation_reason)
    {
        $stmt = $this->db->prepare(
            "UPDATE tl_bookings
            SET booking_status = 'cancelled',
                cancelled_by = ?,
                cancellation_reason = ?,
                cancellation_date = NOW()
            WHERE booking_id = ?"
        );
        $stmt->bind_param("ssi", $cancelled_by, $cancellation_reason, $booking_id);

        return $stmt->execute();
    }

    /**
     * Complete booking
     * @param int $booking_id
     * @return bool True on success, false on failure
     */
    public function complete_booking($booking_id)
    {
        $stmt = $this->db->prepare(
            "UPDATE tl_bookings
            SET booking_status = 'completed',
                completion_date = NOW()
            WHERE booking_id = ?"
        );
        $stmt->bind_param("i", $booking_id);

        $result = $stmt->execute();
        $stmt->close();

        // Generate invoice when booking is completed
        if ($result) {
            try {
                // Check if invoices table exists
                $check_table = $this->db->query("SHOW TABLES LIKE 'tl_invoices'");
                if ($check_table && $check_table->num_rows > 0) {
                    require_once __DIR__ . '/../controllers/invoice_controller.php';
                    $invoice_id = generate_invoice_ctr($booking_id);
                    
                    if ($invoice_id) {
                        // Automatically mark invoice as sent
                        require_once __DIR__ . '/invoice_class.php';
                        $invoice = new Invoice();
                        $invoice->mark_invoice_sent($invoice_id);
                        
                        error_log("Invoice generated for booking $booking_id - Invoice ID: $invoice_id");
                    } else {
                        error_log("Warning: Failed to generate invoice for booking $booking_id");
                    }
                } else {
                    error_log("Info: tl_invoices table does not exist. Invoice generation skipped for booking $booking_id");
                }
                
                // Log audit action
                require_once __DIR__ . '/audit_log_class.php';
                log_audit_action(
                    'booking_completed',
                    'booking',
                    $booking_id,
                    "Booking completed by provider.",
                    $_SESSION['user_id'] ?? null
                );
            } catch (Exception $e) {
                error_log("Error generating invoice for booking $booking_id: " . $e->getMessage());
                // Don't fail the booking completion if invoice generation fails
            } catch (Error $e) {
                error_log("Fatal error generating invoice for booking $booking_id: " . $e->getMessage());
                // Don't fail the booking completion if invoice generation fails
            }
        }

        return $result;
    }

    /**
     * Get booking statistics for a provider
     * @param int $provider_id
     * @return array Statistics array
     */
    public function get_provider_statistics($provider_id)
    {
        $sql = "SELECT
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN booking_status = 'completed' THEN provider_earnings ELSE 0 END) as total_earnings
                FROM tl_bookings
                WHERE provider_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }
}
?>
