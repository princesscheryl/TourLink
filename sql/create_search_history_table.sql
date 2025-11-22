-- Search History Table for TourLink
-- Run this SQL in phpMyAdmin to create the table

CREATE TABLE IF NOT EXISTS `tl_search_history` (
    `search_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `search_query` VARCHAR(255) NOT NULL,
    `filters_used` JSON DEFAULT NULL COMMENT 'Stores category, region, price filters as JSON',
    `search_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_user_searches` (`user_id`, `search_date` DESC),
    INDEX `idx_search_date` (`search_date`),

    CONSTRAINT `fk_search_user`
        FOREIGN KEY (`user_id`)
        REFERENCES `tl_users`(`user_id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
