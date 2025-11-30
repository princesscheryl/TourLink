<?php
require_once __DIR__ . '/../settings/db_class.php';

class Message {
    private $db;

    public function __construct() {
        $this->db = new db_connection();
        $this->db->db_connect();
    }

    /**
     * Generate conversation ID between two users
     * Always returns the same ID regardless of sender/receiver order
     */
    private function generate_conversation_id($user1_id, $user2_id) {
        // Sort IDs to ensure consistent conversation ID
        $ids = [$user1_id, $user2_id];
        sort($ids);
        return 'conv_' . $ids[0] . '_' . $ids[1];
    }

    /**
     * Send a message
     */
    public function send_message($sender_id, $receiver_id, $message_text, $service_id = null, $booking_id = null) {
        $conversation_id = $this->generate_conversation_id($sender_id, $receiver_id);
        
        $sql = "INSERT INTO tl_messages (conversation_id, sender_id, receiver_id, service_id, booking_id, message_text, is_read) 
                VALUES (?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("siiiss", $conversation_id, $sender_id, $receiver_id, $service_id, $booking_id, $message_text);
        
        if ($stmt->execute()) {
            return $this->db->db->insert_id;
        }
        
        return false;
    }

    /**
     * Get all conversations for a user
     */
    public function get_conversations($user_id) {
        $sql = "SELECT 
                    m.conversation_id,
                    CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id
                        ELSE m.sender_id
                    END as other_user_id,
                    MAX(m.created_at) as last_message_time,
                    (SELECT message_text FROM tl_messages 
                     WHERE conversation_id = m.conversation_id 
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM tl_messages 
                     WHERE conversation_id = m.conversation_id 
                     AND receiver_id = ? AND is_read = 0) as unread_count
                FROM tl_messages m
                WHERE m.sender_id = ? OR m.receiver_id = ?
                GROUP BY m.conversation_id, other_user_id
                ORDER BY last_message_time DESC";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        
        return $conversations;
    }

    /**
     * Get messages in a conversation
     */
    public function get_conversation_messages($conversation_id, $user_id, $limit = 50) {
        $sql = "SELECT m.*, 
                       u1.first_name as sender_first_name,
                       u1.last_name as sender_last_name,
                       u1.profile_image as sender_profile_image,
                       u1.user_type as sender_user_type
                FROM tl_messages m
                JOIN tl_users u1 ON m.sender_id = u1.user_id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
                LIMIT ?";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("si", $conversation_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        // Mark messages as read
        $this->mark_conversation_as_read($conversation_id, $user_id);
        
        return $messages;
    }

    /**
     * Get conversation by two user IDs
     */
    public function get_conversation_by_users($user1_id, $user2_id) {
        $conversation_id = $this->generate_conversation_id($user1_id, $user2_id);
        return $this->get_conversation_messages($conversation_id, $user1_id);
    }

    /**
     * Mark messages in a conversation as read
     */
    public function mark_conversation_as_read($conversation_id, $user_id) {
        $sql = "UPDATE tl_messages 
                SET is_read = 1 
                WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $conversation_id, $user_id);
        return $stmt->execute();
    }

    /**
     * Get unread message count for a user
     */
    public function get_unread_count($user_id) {
        $sql = "SELECT COUNT(*) as count 
                FROM tl_messages 
                WHERE receiver_id = ? AND is_read = 0";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }

    /**
     * Get conversation details (other user info, service info if linked)
     */
    public function get_conversation_details($conversation_id, $current_user_id) {
        // Get the other user in the conversation
        $sql = "SELECT DISTINCT
                    CASE 
                        WHEN m.sender_id = ? THEN m.receiver_id
                        ELSE m.sender_id
                    END as other_user_id,
                    m.service_id,
                    m.booking_id
                FROM tl_messages m
                WHERE m.conversation_id = ?
                LIMIT 1";
        
        $stmt = $this->db->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("is", $current_user_id, $conversation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
}

