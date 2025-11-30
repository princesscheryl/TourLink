-- Support Tickets table for customer support system
CREATE TABLE IF NOT EXISTS tl_support_tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    user_type ENUM('tourist', 'provider', 'admin') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    category ENUM('payment', 'booking', 'account', 'technical', 'service', 'provider', 'other') NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('new', 'open', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    assigned_to INT NULL,
    related_booking_id INT NULL,
    related_service_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_assigned (assigned_to),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES tl_users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (related_booking_id) REFERENCES tl_bookings(booking_id) ON DELETE SET NULL,
    FOREIGN KEY (related_service_id) REFERENCES tl_services(service_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support Ticket Replies table
CREATE TABLE IF NOT EXISTS tl_support_replies (
    reply_id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    user_type ENUM('tourist', 'provider', 'admin') NOT NULL,
    message TEXT NOT NULL,
    is_internal_note TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket (ticket_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (ticket_id) REFERENCES tl_support_tickets(ticket_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support Ticket Attachments table
CREATE TABLE IF NOT EXISTS tl_support_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NULL,
    reply_id INT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket (ticket_id),
    INDEX idx_reply (reply_id),
    FOREIGN KEY (ticket_id) REFERENCES tl_support_tickets(ticket_id) ON DELETE CASCADE,
    FOREIGN KEY (reply_id) REFERENCES tl_support_replies(reply_id) ON DELETE CASCADE,
    CHECK (ticket_id IS NOT NULL OR reply_id IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

