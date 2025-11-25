<?php
require_once __DIR__ . '/../settings/db_class.php';

/**
 * Notification Class for TourLink Platform
 * Handles in-app notifications and email reminders
 */
class Notification extends db_connection
{
    public function __construct()
    {
        $this->db_connect();
    }

    /**
     * Create notification for user
     * @param int $user_id
     * @param string $type (festival_reminder, booking_update, etc.)
     * @param string $title
     * @param string $message
     * @param string|null $related_entity_type
     * @param int|null $related_entity_id
     * @return bool
     */
    public function create_notification($user_id, $type, $title, $message, $related_entity_type = null, $related_entity_id = null)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tl_notifications (user_id, notification_type, title, message, related_entity_type, related_entity_id)
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issssi", $user_id, $type, $title, $message, $related_entity_type, $related_entity_id);

        return $stmt->execute();
    }

    /**
     * Send festival reminders to all active tourists
     * @param int $festival_id
     * @param array $festival_data
     * @return int Number of notifications sent
     */
    public function send_festival_reminders($festival_id, $festival_data)
    {
        // Get all active tourists
        $tourists = $this->db_fetch_all("
            SELECT user_id, first_name, email
            FROM tl_users
            WHERE user_type = 'tourist'
            AND account_status = 'active'
        ");

        if (!$tourists) {
            return 0;
        }

        $count = 0;
        $festival_name = $festival_data['festival_name'];
        $start_date = date('F j, Y', strtotime($festival_data['start_date']));
        $region = $festival_data['region'];

        foreach ($tourists as $tourist) {
            $title = "🎉 {$festival_name} Festival Approaching!";
            $message = "The {$festival_name} festival in {$region} region starts on {$start_date}. Discover unique cultural experiences and book your tour guide or local services now!";

            if ($this->create_notification(
                $tourist['user_id'],
                'festival_reminder',
                $title,
                $message,
                'festival',
                $festival_id
            )) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Send festival reminders for upcoming festivals (within 2 weeks)
     * Called by cron job
     * @return array Results of reminder sending
     */
    public function send_upcoming_festival_reminders()
    {
        require_once __DIR__ . '/festival_class.php';

        $festival_class = new Festival();

        // Get festivals starting in 14-21 days (send reminder 2-3 weeks before)
        $sql = "SELECT * FROM tl_festivals
                WHERE is_active = 1
                AND start_date BETWEEN DATE_ADD(CURRENT_DATE(), INTERVAL 14 DAY)
                AND DATE_ADD(CURRENT_DATE(), INTERVAL 21 DAY)
                AND festival_id NOT IN (
                    SELECT DISTINCT related_entity_id
                    FROM tl_notifications
                    WHERE notification_type = 'festival_reminder'
                    AND related_entity_type = 'festival'
                    AND created_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                )";

        $upcoming_festivals = $this->db_fetch_all($sql);
        $results = [];

        if ($upcoming_festivals) {
            foreach ($upcoming_festivals as $festival) {
                $sent = $this->send_festival_reminders($festival['festival_id'], $festival);
                $results[] = [
                    'festival' => $festival['festival_name'],
                    'notifications_sent' => $sent
                ];
            }
        }

        return $results;
    }

    /**
     * Get user's unread notifications
     * @param int $user_id
     * @param int $limit
     * @return array|bool
     */
    public function get_user_notifications($user_id, $limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM tl_notifications
            WHERE user_id = ?
            ORDER BY created_date DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Mark notification as read
     * @param int $notification_id
     * @return bool
     */
    public function mark_as_read($notification_id)
    {
        $stmt = $this->db->prepare("
            UPDATE tl_notifications
            SET is_read = 1, read_date = NOW()
            WHERE notification_id = ?
        ");
        $stmt->bind_param("i", $notification_id);

        return $stmt->execute();
    }

    /**
     * Get unread count for user
     * @param int $user_id
     * @return int
     */
    public function get_unread_count($user_id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM tl_notifications
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }
}
?>
