-- Add preference columns to tl_users table
-- Run these SQL statements to add language and country preferences

ALTER TABLE `tl_users`
ADD COLUMN `preferred_language` VARCHAR(5) DEFAULT 'en' AFTER `profile_image`;

ALTER TABLE `tl_users`
ADD COLUMN `country` VARCHAR(10) DEFAULT NULL AFTER `preferred_language`;
