-- Drop existing tables first to remove old constraints
DROP TABLE IF EXISTS tl_discount_usage;
DROP TABLE IF EXISTS tl_discount_codes;

-- Discount Codes Table for TourLink
CREATE TABLE tl_discount_codes (
    discount_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    min_booking_amount DECIMAL(10,2) DEFAULT 0 COMMENT 'Minimum booking amount to use this code',
    max_discount_amount DECIMAL(10,2) NULL COMMENT 'Maximum discount amount (for percentage type)',
    usage_limit INT DEFAULT NULL COMMENT 'Total times this code can be used (NULL = unlimited)',
    usage_count INT DEFAULT 0 COMMENT 'Number of times used',
    per_user_limit INT DEFAULT 1 COMMENT 'How many times each user can use this code',
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    applicable_to ENUM('all', 'services', 'categories', 'providers') DEFAULT 'all',
    applicable_ids TEXT NULL COMMENT 'JSON array of IDs if applicable_to is categories or providers',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NULL COMMENT 'Admin user ID who created the code',
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT NULL COMMENT 'Internal notes about this discount code',
    INDEX idx_code (code),
    INDEX idx_active (is_active),
    INDEX idx_valid_dates (valid_from, valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Promotional discount codes for bookings';

-- Discount Usage Tracking Table
CREATE TABLE tl_discount_usage (
    usage_id INT AUTO_INCREMENT PRIMARY KEY,
    discount_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL COMMENT 'Actual discount amount applied',
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_discount (discount_id),
    INDEX idx_user (user_id),
    INDEX idx_booking (booking_id),
    UNIQUE KEY unique_user_discount (user_id, discount_id, booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tracks discount code usage by users';

-- Sample discount codes for testing
INSERT INTO tl_discount_codes (code, discount_type, discount_value, min_booking_amount, max_discount_amount, usage_limit, valid_from, valid_until, created_by, description) VALUES
('WELCOME10', 'percentage', 10.00, 50.00, 50.00, NULL, '2025-01-01', '2025-12-31', NULL, 'Welcome discount for new users - 10% off'),
('FESTIVAL20', 'percentage', 20.00, 100.00, 100.00, 500, '2025-01-01', '2025-12-31', NULL, 'Festival season special - 20% off bookings over GHS 100'),
('EARLYBIRD', 'fixed', 25.00, 150.00, NULL, 100, '2025-01-01', '2025-06-30', NULL, 'Early booking discount - GHS 25 off'),
('SUMMER2025', 'percentage', 15.00, 75.00, 75.00, 200, '2025-06-01', '2025-08-31', NULL, 'Summer promotion - 15% off');
