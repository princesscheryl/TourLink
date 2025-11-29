<?php
/**
 * Invoice Class
 * Handles invoice generation, management, and lifecycle for completed bookings
 */

require_once __DIR__ . '/../settings/db_class.php';

class Invoice extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Generate invoice for a completed booking
     * 
     * @param int $booking_id
     * @return int|false Invoice ID on success, false on failure
     */
    public function generate_invoice($booking_id)
    {
        // Check if invoice already exists
        $existing = $this->get_invoice_by_booking($booking_id);
        if ($existing) {
            return $existing['invoice_id'];
        }

        // Get booking details
        $booking = $this->get_booking_details($booking_id);
        if (!$booking) {
            return false;
        }

        // Generate invoice number
        $invoice_number = $this->generate_invoice_number();

        // Calculate invoice amounts
        $subtotal = $booking['original_amount'];
        $discount_amount = $booking['discount_amount'] ?? 0;
        $tax_amount = 0; // Can be configured later
        $total_amount = $subtotal - $discount_amount + $tax_amount;

        // Calculate due date (30 days from invoice date)
        $due_date = date('Y-m-d', strtotime('+30 days'));

        $sql = "INSERT INTO tl_invoices 
                (booking_id, invoice_number, subtotal, discount_amount, tax_amount, total_amount, due_date, invoice_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Invoice generation failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param(
            "isddddss",
            $booking_id,
            $invoice_number,
            $subtotal,
            $discount_amount,
            $tax_amount,
            $total_amount,
            $due_date
        );

        if ($stmt->execute()) {
            $invoice_id = $stmt->insert_id;
            $stmt->close();
            
            // Log audit action
            require_once __DIR__ . '/audit_log_class.php';
            log_audit_action(
                'invoice_generated',
                'invoice',
                $invoice_id,
                "Invoice generated for booking #{$booking_id}. Invoice number: $invoice_number",
                null
            );
            
            return $invoice_id;
        }

        $stmt->close();
        return false;
    }

    /**
     * Get invoice by booking ID
     * 
     * @param int $booking_id
     * @return array|false
     */
    public function get_invoice_by_booking($booking_id)
    {
        $sql = "SELECT * FROM tl_invoices WHERE booking_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $invoice = $result->fetch_assoc();
        $stmt->close();
        
        return $invoice ? $invoice : false;
    }

    /**
     * Get invoice with full details
     * 
     * @param int $invoice_id
     * @return array|false
     */
    public function get_invoice_details($invoice_id)
    {
        $sql = "SELECT i.*, 
                       b.booking_reference, b.service_date, b.service_time,
                       b.number_of_people, b.booking_status,
                       s.service_title, s.base_price,
                       sp.business_name as provider_name,
                       tu.first_name as tourist_first_name,
                       tu.last_name as tourist_last_name,
                       tu.email as tourist_email,
                       tu.phone as tourist_phone
                FROM tl_invoices i
                INNER JOIN tl_bookings b ON i.booking_id = b.booking_id
                INNER JOIN tl_services s ON b.service_id = s.service_id
                INNER JOIN tl_service_providers sp ON b.provider_id = sp.provider_id
                INNER JOIN tl_users tu ON b.tourist_id = tu.user_id
                WHERE i.invoice_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $invoice = $result->fetch_assoc();
        $stmt->close();
        
        return $invoice ? $invoice : false;
    }

    /**
     * Update invoice status
     * 
     * @param int $invoice_id
     * @param string $status (draft, sent, paid, overdue, cancelled)
     * @return bool
     */
    public function update_invoice_status($invoice_id, $status)
    {
        $valid_statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $sql = "UPDATE tl_invoices SET invoice_status = ?";
        $params = [$status];
        $types = "s";

        if ($status === 'sent') {
            $sql .= ", sent_date = NOW()";
        } elseif ($status === 'paid') {
            $sql .= ", paid_date = NOW()";
        }

        $sql .= " WHERE invoice_id = ?";
        $params[] = $invoice_id;
        $types .= "i";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            require_once __DIR__ . '/audit_log_class.php';
            log_audit_action(
                'invoice_status_updated',
                'invoice',
                $invoice_id,
                "Invoice status updated to: $status",
                $_SESSION['user_id'] ?? null
            );
        }

        return $result;
    }

    /**
     * Mark invoice as sent
     * 
     * @param int $invoice_id
     * @param bool $send_email Whether to send email to customer (default: false, not implemented yet)
     * @return bool
     */
    public function mark_invoice_sent($invoice_id, $send_email = false)
    {
        $result = $this->update_invoice_status($invoice_id, 'sent');
        
        // TODO: Implement email sending functionality
        // if ($result && $send_email) {
        //     $this->send_invoice_email($invoice_id);
        // }
        
        return $result;
    }

    /**
     * Mark invoice as paid
     * 
     * @param int $invoice_id
     * @return bool
     */
    public function mark_invoice_paid($invoice_id)
    {
        return $this->update_invoice_status($invoice_id, 'paid');
    }

    /**
     * Get invoices with filters
     * 
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array|false
     */
    public function get_invoices($filters = [], $limit = 50, $offset = 0)
    {
        $sql = "SELECT i.*, 
                       b.booking_reference,
                       s.service_title,
                       sp.business_name as provider_name,
                       tu.first_name as tourist_first_name,
                       tu.last_name as tourist_last_name
                FROM tl_invoices i
                INNER JOIN tl_bookings b ON i.booking_id = b.booking_id
                INNER JOIN tl_services s ON b.service_id = s.service_id
                INNER JOIN tl_service_providers sp ON b.provider_id = sp.provider_id
                INNER JOIN tl_users tu ON b.tourist_id = tu.user_id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (isset($filters['status'])) {
            $sql .= " AND i.invoice_status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        if (isset($filters['provider_id'])) {
            $sql .= " AND b.provider_id = ?";
            $params[] = $filters['provider_id'];
            $types .= "i";
        }

        if (isset($filters['tourist_id'])) {
            $sql .= " AND b.tourist_id = ?";
            $params[] = $filters['tourist_id'];
            $types .= "i";
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND DATE(i.invoice_date) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND DATE(i.invoice_date) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        $sql .= " ORDER BY i.invoice_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $invoices = [];

        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }

        $stmt->close();
        return $invoices;
    }

    /**
     * Generate unique invoice number
     * Format: INV-YYYYMMDD-XXXXX
     * 
     * @return string
     */
    private function generate_invoice_number()
    {
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return "INV-{$date}-{$random}";
    }

    /**
     * Get booking details for invoice
     * 
     * @param int $booking_id
     * @return array|false
     */
    private function get_booking_details($booking_id)
    {
        $sql = "SELECT * FROM tl_bookings WHERE booking_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();
        
        return $booking ? $booking : false;
    }

    /**
     * Check and update overdue invoices
     * 
     * @return int Number of invoices updated
     */
    public function update_overdue_invoices()
    {
        $sql = "UPDATE tl_invoices 
                SET invoice_status = 'overdue'
                WHERE invoice_status IN ('sent', 'draft')
                AND due_date < CURDATE()";
        
        $this->db->query($sql);
        return $this->db->affected_rows;
    }
}

