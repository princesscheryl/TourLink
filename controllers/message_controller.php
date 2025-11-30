<?php
require_once __DIR__ . '/../classes/message_class.php';
require_once __DIR__ . '/../classes/tourlink_user_class.php';
require_once __DIR__ . '/../classes/service_class.php';

/**
 * Send a message
 */
function send_message_ctr($sender_id, $receiver_id, $message_text, $service_id = null, $booking_id = null) {
    $message_class = new Message();
    return $message_class->send_message($sender_id, $receiver_id, $message_text, $service_id, $booking_id);
}

/**
 * Get all conversations for a user
 */
function get_user_conversations_ctr($user_id) {
    $message_class = new Message();
    $conversations = $message_class->get_conversations($user_id);
    
    // Enrich with user information
    $user_class = new TourlinkUser();
    $enriched = [];
    
    foreach ($conversations as $conv) {
        $other_user = $user_class->get_user_by_id($conv['other_user_id']);
        if ($other_user) {
            $conv['other_user'] = $other_user;
            $enriched[] = $conv;
        }
    }
    
    return $enriched;
}

/**
 * Get messages in a conversation
 */
function get_conversation_messages_ctr($conversation_id, $user_id) {
    $message_class = new Message();
    return $message_class->get_conversation_messages($conversation_id, $user_id);
}

/**
 * Get conversation by two user IDs
 */
function get_conversation_by_users_ctr($user1_id, $user2_id) {
    $message_class = new Message();
    return $message_class->get_conversation_by_users($user1_id, $user2_id);
}

/**
 * Get unread message count
 */
function get_unread_message_count_ctr($user_id) {
    $message_class = new Message();
    return $message_class->get_unread_count($user_id);
}

/**
 * Get conversation details with other user info
 */
function get_conversation_details_ctr($conversation_id, $current_user_id) {
    $message_class = new Message();
    $details = $message_class->get_conversation_details($conversation_id, $current_user_id);
    
    if (!$details) {
        return null;
    }
    
    // Get other user info
    $user_class = new TourlinkUser();
    $other_user = $user_class->get_user_by_id($details['other_user_id']);
    $details['other_user'] = $other_user;
    
    // Get service info if linked
    if ($details['service_id']) {
        $service_class = new Service();
        $service = $service_class->get_service_by_id($details['service_id']);
        $details['service'] = $service;
    }
    
    return $details;
}

