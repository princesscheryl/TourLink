<?php
/**
 * Audit Log Class
 * Tracks system actions, user activities, and important events for security and compliance
 * 
 * Purpose:
 * - Security monitoring: Track login attempts, access to sensitive data
 * - Compliance: Maintain records of data changes, user actions
 * - Debugging: Help troubleshoot issues by tracking system events
 * - Analytics: Understand user behavior and system usage patterns
 */

require_once __DIR__ . '/../settings/db_class.php';

class AuditLog extends db_connection
{
    /**
     * Log an action to the audit log
     * 
     * @param string $action_type Type of action (e.g., 'login', 'booking_created', 'payment_processed')
     * @param string $entity_type Type of entity affected (e.g., 'booking', 'user', 'service')
     * @param int|null $entity_id ID of the affected entity
     * @param string|null $description Detailed description of the action
     * @param int|null $user_id User who performed the action (null for system actions)
     * @return bool Success status
     */
    public function log_action($action_type, $entity_type = null, $entity_id = null, $description = null, $user_id = null)
    {
        if (!$this->db_connect()) {
            return false;
        }

        // Get IP address and user agent
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // If user_id not provided, try to get from session
        if ($user_id === null && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }

        $sql = "INSERT INTO tl_audit_log 
                (user_id, action_type, entity_type, entity_id, description, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Audit log prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param(
            "isssiss",
            $user_id,
            $action_type,
            $entity_type,
            $entity_id,
            $description,
            $ip_address,
            $user_agent
        );

        $result = $stmt->execute();
        if (!$result) {
            error_log("Audit log insert failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }

    /**
     * Get audit logs with filters
     * 
     * @param array $filters Array of filter conditions
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array|false Array of audit log entries or false on failure
     */
    public function get_logs($filters = [], $limit = 100, $offset = 0)
    {
        if (!$this->db_connect()) {
            return false;
        }

        $sql = "SELECT al.*, u.first_name, u.last_name, u.email
                FROM tl_audit_log al
                LEFT JOIN tl_users u ON al.user_id = u.user_id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (isset($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (isset($filters['action_type'])) {
            $sql .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
            $types .= "s";
        }

        if (isset($filters['entity_type'])) {
            $sql .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
            $types .= "s";
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND DATE(al.log_timestamp) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND DATE(al.log_timestamp) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        $sql .= " ORDER BY al.log_timestamp DESC LIMIT ? OFFSET ?";
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
        $logs = [];

        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }

        $stmt->close();
        return $logs;
    }
}

/**
 * Helper function to log actions easily
 * 
 * @param string $action_type
 * @param string|null $entity_type
 * @param int|null $entity_id
 * @param string|null $description
 * @param int|null $user_id
 * @return bool
 */
function log_audit_action($action_type, $entity_type = null, $entity_id = null, $description = null, $user_id = null)
{
    $audit = new AuditLog();
    return $audit->log_action($action_type, $entity_type, $entity_id, $description, $user_id);
}

