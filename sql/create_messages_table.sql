-- Messages table for tourist-provider communication
CREATE TABLE IF NOT EXISTS tl_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id VARCHAR(100) NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    service_id INT NULL,
    booking_id INT NULL,
    message_text TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_service (service_id),
    INDEX idx_booking (booking_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (sender_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES tl_services(service_id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES tl_bookings(booking_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

