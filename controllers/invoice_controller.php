<?php
/**
 * Invoice Controller
 * Controller functions for invoice operations
 */

require_once __DIR__ . '/../classes/invoice_class.php';

/**
 * Generate invoice for a booking
 * 
 * @param int $booking_id
 * @return int|false Invoice ID on success, false on failure
 */
function generate_invoice_ctr($booking_id)
{
    $invoice = new Invoice();
    return $invoice->generate_invoice($booking_id);
}

/**
 * Get invoice by booking ID
 * 
 * @param int $booking_id
 * @return array|false
 */
function get_invoice_by_booking_ctr($booking_id)
{
    $invoice = new Invoice();
    return $invoice->get_invoice_by_booking($booking_id);
}

/**
 * Get invoice details
 * 
 * @param int $invoice_id
 * @return array|false
 */
function get_invoice_details_ctr($invoice_id)
{
    $invoice = new Invoice();
    return $invoice->get_invoice_details($invoice_id);
}

/**
 * Update invoice status
 * 
 * @param int $invoice_id
 * @param string $status
 * @return bool
 */
function update_invoice_status_ctr($invoice_id, $status)
{
    $invoice = new Invoice();
    return $invoice->update_invoice_status($invoice_id, $status);
}

/**
 * Mark invoice as sent
 * 
 * @param int $invoice_id
 * @return bool
 */
function mark_invoice_sent_ctr($invoice_id)
{
    $invoice = new Invoice();
    return $invoice->mark_invoice_sent($invoice_id);
}

/**
 * Mark invoice as paid
 * 
 * @param int $invoice_id
 * @return bool
 */
function mark_invoice_paid_ctr($invoice_id)
{
    $invoice = new Invoice();
    return $invoice->mark_invoice_paid($invoice_id);
}

/**
 * Get invoices with filters
 * 
 * @param array $filters
 * @param int $limit
 * @param int $offset
 * @return array|false
 */
function get_invoices_ctr($filters = [], $limit = 50, $offset = 0)
{
    $invoice = new Invoice();
    return $invoice->get_invoices($filters, $limit, $offset);
}

