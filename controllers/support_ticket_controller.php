<?php
require_once __DIR__ . '/../classes/support_ticket_class.php';
require_once __DIR__ . '/../classes/tourlink_user_class.php';

/**
 * Create a new support ticket
 */
function create_ticket_ctr($user_id, $user_type, $subject, $category, $description, $priority = 'medium', $related_booking_id = null, $related_service_id = null) {
    $ticket_class = new SupportTicket();
    return $ticket_class->create_ticket($user_id, $user_type, $subject, $category, $description, $priority, $related_booking_id, $related_service_id);
}

/**
 * Get ticket by ID
 */
function get_ticket_by_id_ctr($ticket_id) {
    $ticket_class = new SupportTicket();
    return $ticket_class->get_ticket_by_id($ticket_id);
}

/**
 * Get ticket by ticket number
 */
function get_ticket_by_number_ctr($ticket_number) {
    $ticket_class = new SupportTicket();
    return $ticket_class->get_ticket_by_number($ticket_number);
}

/**
 * Get all tickets for a user
 */
function get_user_tickets_ctr($user_id) {
    $ticket_class = new SupportTicket();
    return $ticket_class->get_user_tickets($user_id);
}

/**
 * Get all tickets for admin
 */
function get_all_tickets_ctr($filters = []) {
    $ticket_class = new SupportTicket();
    return $ticket_class->get_all_tickets($filters);
}

/**
 * Update ticket status
 */
function update_ticket_status_ctr($ticket_id, $status, $assigned_to = null) {
    $ticket_class = new SupportTicket();
    return $ticket_class->update_ticket_status($ticket_id, $status, $assigned_to);
}

/**
 * Assign ticket to admin
 */
function assign_ticket_ctr($ticket_id, $admin_id) {
    $ticket_class = new SupportTicket();
    return $ticket_class->assign_ticket($ticket_id, $admin_id);
}

/**
 * Get ticket statistics
 */
function get_ticket_stats_ctr() {
    $ticket_class = new SupportTicket();
    return $ticket_class->get_ticket_stats();
}

/**
 * Add reply to ticket
 */
function add_ticket_reply_ctr($ticket_id, $user_id, $user_type, $message, $is_internal_note = false) {
    $ticket_class = new SupportTicket();
    return $ticket_class->add_reply($ticket_id, $user_id, $user_type, $message, $is_internal_note);
}

/**
 * Get ticket replies
 */
function get_ticket_replies_ctr($ticket_id, $include_internal = false) {
    $ticket_class = new SupportTicket();
    return $ticket_class->get_ticket_replies($ticket_id, $include_internal);
}

