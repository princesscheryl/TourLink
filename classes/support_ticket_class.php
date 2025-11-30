<?php
require_once __DIR__ . '/../settings/db_class.php';

class SupportTicket {
    private $db;

    public function __construct() {
        $this->db = new db_connection();
        $this->db->db_connect();
    }

    /**
     * Generate unique ticket number
     */
    private function generate_ticket_number() {
        do {
            $number = 'TL-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $check = $this->db->db->prepare("SELECT ticket_id FROM tl_support_tickets WHERE ticket_number = ?");
            $check->bind_param("s", $number);
            $check->execute();
            $result = $check->get_result();
        } while ($result->num_rows > 0);
        
        return $number;
    }

    /**
     * Create a new support ticket
     */
    public function create_ticket($user_id, $user_type, $subject, $category, $description, $priority = 'medium', $related_booking_id = null, $related_service_id = null) {
        $ticket_number = $this->generate_ticket_number();
        
        $sql = "INSERT INTO tl_support_tickets (ticket_number, user_id, user_type, subject, category, description, priority, related_booking_id, related_service_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sississsii", $ticket_number, $user_id, $user_type, $subject, $category, $description, $priority, $related_booking_id, $related_service_id);
        
        if ($stmt->execute()) {
            return $this->db->db->insert_id;
        }
        
        return false;
    }

    /**
     * Get ticket by ID
     */
    public function get_ticket_by_id($ticket_id) {
        $sql = "SELECT t.*, 
                       u1.first_name as user_first_name,
                       u1.last_name as user_last_name,
                       u1.email as user_email,
                       u2.first_name as assigned_first_name,
                       u2.last_name as assigned_last_name
                FROM tl_support_tickets t
                LEFT JOIN tl_users u1 ON t.user_id = u1.user_id
                LEFT JOIN tl_users u2 ON t.assigned_to = u2.user_id
                WHERE t.ticket_id = ?";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get ticket by ticket number
     */
    public function get_ticket_by_number($ticket_number) {
        $sql = "SELECT t.*, 
                       u1.first_name as user_first_name,
                       u1.last_name as user_last_name,
                       u1.email as user_email,
                       u2.first_name as assigned_first_name,
                       u2.last_name as assigned_last_name
                FROM tl_support_tickets t
                LEFT JOIN tl_users u1 ON t.user_id = u1.user_id
                LEFT JOIN tl_users u2 ON t.assigned_to = u2.user_id
                WHERE t.ticket_number = ?";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $ticket_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Get all tickets for a user
     */
    public function get_user_tickets($user_id) {
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM tl_support_replies r WHERE r.ticket_id = t.ticket_id AND r.is_internal_note = 0) as reply_count
                FROM tl_support_tickets t
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tickets = [];
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row;
        }
        
        return $tickets;
    }

    /**
     * Get all tickets for admin management
     */
    public function get_all_tickets($filters = []) {
        $where = [];
        $params = [];
        $types = '';

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "t.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['category']) && $filters['category'] !== '') {
            $where[] = "t.category = ?";
            $params[] = $filters['category'];
            $types .= 's';
        }

        if (isset($filters['priority']) && $filters['priority'] !== '') {
            $where[] = "t.priority = ?";
            $params[] = $filters['priority'];
            $types .= 's';
        }

        if (isset($filters['assigned_to']) && $filters['assigned_to'] !== '') {
            if ($filters['assigned_to'] === 'unassigned') {
                $where[] = "t.assigned_to IS NULL";
            } else {
                $where[] = "t.assigned_to = ?";
                $params[] = (int)$filters['assigned_to'];
                $types .= 'i';
            }
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT t.*, 
                       u1.first_name as user_first_name,
                       u1.last_name as user_last_name,
                       u1.email as user_email,
                       u2.first_name as assigned_first_name,
                       u2.last_name as assigned_last_name,
                       (SELECT COUNT(*) FROM tl_support_replies r WHERE r.ticket_id = t.ticket_id AND r.is_internal_note = 0) as reply_count
                FROM tl_support_tickets t
                LEFT JOIN tl_users u1 ON t.user_id = u1.user_id
                LEFT JOIN tl_users u2 ON t.assigned_to = u2.user_id
                $where_clause
                ORDER BY 
                    CASE t.priority
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    t.created_at DESC";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        $tickets = [];
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row;
        }
        
        return $tickets;
    }

    /**
     * Update ticket status
     */
    public function update_ticket_status($ticket_id, $status, $assigned_to = null) {
        $sql = "UPDATE tl_support_tickets SET status = ?, assigned_to = ?";
        
        if ($status === 'resolved' || $status === 'closed') {
            $sql .= ", resolved_at = NOW()";
        } else {
            $sql .= ", resolved_at = NULL";
        }
        
        $sql .= " WHERE ticket_id = ?";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        if ($assigned_to) {
            $stmt->bind_param("sii", $status, $assigned_to, $ticket_id);
        } else {
            $stmt->bind_param("sii", $status, $assigned_to, $ticket_id);
            $stmt->bind_param("sii", $status, $assigned_to, $ticket_id);
            $sql = "UPDATE tl_support_tickets SET status = ?";
            if ($status === 'resolved' || $status === 'closed') {
                $sql .= ", resolved_at = NOW()";
            } else {
                $sql .= ", resolved_at = NULL";
            }
            $sql .= " WHERE ticket_id = ?";
            $stmt = $this->db->db->prepare($sql);
            $stmt->bind_param("si", $status, $ticket_id);
        }
        
        return $stmt->execute();
    }

    /**
     * Assign ticket to admin
     */
    public function assign_ticket($ticket_id, $admin_id) {
        $sql = "UPDATE tl_support_tickets SET assigned_to = ?, status = CASE WHEN status = 'new' THEN 'open' ELSE status END WHERE ticket_id = ?";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $admin_id, $ticket_id);
        return $stmt->execute();
    }

    /**
     * Get ticket statistics
     */
    public function get_ticket_stats() {
        $sql = "SELECT 
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_tickets,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tickets,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets,
                    SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_tickets
                FROM tl_support_tickets";
        
        $result = $this->db->db->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Add reply to ticket
     */
    public function add_reply($ticket_id, $user_id, $user_type, $message, $is_internal_note = false) {
        $sql = "INSERT INTO tl_support_replies (ticket_id, user_id, user_type, message, is_internal_note) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $is_internal = $is_internal_note ? 1 : 0;
        $stmt->bind_param("iissi", $ticket_id, $user_id, $user_type, $message, $is_internal);
        
        if ($stmt->execute()) {
            $reply_id = $this->db->db->insert_id;
            
            // Update ticket status if admin replies
            if ($user_type === 'admin' && !$is_internal_note) {
                $this->update_ticket_status($ticket_id, 'in_progress');
            }
            
            return $reply_id;
        }
        
        return false;
    }

    /**
     * Get all replies for a ticket
     */
    public function get_ticket_replies($ticket_id, $include_internal = false) {
        $sql = "SELECT r.*, 
                       u.first_name, u.last_name, u.email
                FROM tl_support_replies r
                JOIN tl_users u ON r.user_id = u.user_id
                WHERE r.ticket_id = ?";
        
        if (!$include_internal) {
            $sql .= " AND r.is_internal_note = 0";
        }
        
        $sql .= " ORDER BY r.created_at ASC";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $replies = [];
        while ($row = $result->fetch_assoc()) {
            $replies[] = $row;
        }
        
        return $replies;
    }
}

