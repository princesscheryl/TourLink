-- Add average_rating and total_reviews columns to tl_services table
-- Run this in phpMyAdmin or MySQL console

ALTER TABLE tl_services
ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00 AFTER views_count,
ADD COLUMN total_reviews INT DEFAULT 0 AFTER average_rating;

-- Update existing services with their calculated ratings (if any reviews exist)
UPDATE tl_services s
SET average_rating = (
    SELECT COALESCE(AVG(r.rating), 0)
    FROM tl_reviews r
    WHERE r.service_id = s.service_id
),
total_reviews = (
    SELECT COUNT(*)
    FROM tl_reviews r
    WHERE r.service_id = s.service_id
);
