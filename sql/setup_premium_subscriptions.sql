-- Setup Premium Subscription System for TourLink
-- Monthly subscription: GH₵150/month
-- Run this in phpMyAdmin after backing up your database
-- This script is safe to run multiple times (idempotent)

SET @dbname = DATABASE();

-- 1. Update tl_premium_listings table structure
-- Modify amount_paid column
ALTER TABLE tl_premium_listings
MODIFY COLUMN amount_paid DECIMAL(10,2) NOT NULL DEFAULT 150.00;

-- Add subscription_id if not exists
SET @tablename = "tl_premium_listings";
SET @columnname = "subscription_id";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD COLUMN subscription_id INT NULL AFTER premium_listing_id"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add is_subscription if not exists
SET @columnname = "is_subscription";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD COLUMN is_subscription BOOLEAN DEFAULT TRUE"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add next_billing_date if not exists
SET @columnname = "next_billing_date";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD COLUMN next_billing_date DATE NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add cancelled_at if not exists
SET @columnname = "cancelled_at";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD COLUMN cancelled_at TIMESTAMP NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add views_count if not exists
SET @columnname = "views_count";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD COLUMN views_count INT DEFAULT 0"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add bookings_count if not exists
SET @columnname = "bookings_count";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD COLUMN bookings_count INT DEFAULT 0"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Update tl_subscription_payments table
SET @tablename = "tl_subscription_payments";

-- Add premium_listing_id if not exists
SET @columnname = "premium_listing_id";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_subscription_payments ADD COLUMN premium_listing_id INT NULL AFTER subscription_payment_id"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Modify subscription_tier and amount columns (safe to run multiple times)
ALTER TABLE tl_subscription_payments
MODIFY COLUMN subscription_tier ENUM('Premium') NOT NULL DEFAULT 'Premium',
MODIFY COLUMN amount DECIMAL(10,2) NOT NULL DEFAULT 150.00;

-- 3. Add indexes for better performance
SET @tablename = "tl_premium_listings";

-- Add index on subscription_id
SET @indexname = "idx_subscription";
SET @preparedIndex = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE table_name = @tablename AND table_schema = @dbname AND index_name = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD INDEX idx_subscription (subscription_id)"
));
PREPARE addIndexIfNotExists FROM @preparedIndex;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Add index on next_billing_date
SET @indexname = "idx_next_billing";
SET @preparedIndex = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE table_name = @tablename AND table_schema = @dbname AND index_name = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD INDEX idx_next_billing (next_billing_date)"
));
PREPARE addIndexIfNotExists FROM @preparedIndex;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Add index on is_subscription
SET @indexname = "idx_is_subscription";
SET @preparedIndex = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE table_name = @tablename AND table_schema = @dbname AND index_name = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_premium_listings ADD INDEX idx_is_subscription (is_subscription)"
));
PREPARE addIndexIfNotExists FROM @preparedIndex;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Add index on premium_listing_id in tl_subscription_payments
SET @tablename = "tl_subscription_payments";
SET @indexname = "idx_premium_listing";
SET @preparedIndex = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE table_name = @tablename AND table_schema = @dbname AND index_name = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_subscription_payments ADD INDEX idx_premium_listing (premium_listing_id)"
));
PREPARE addIndexIfNotExists FROM @preparedIndex;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- 4. Update tl_services table to track premium status
SET @tablename = "tl_services";
SET @columnname = "is_premium";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_services ADD COLUMN is_premium BOOLEAN DEFAULT FALSE"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index on is_premium
SET @indexname = "idx_is_premium";
SET @preparedIndex = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE table_name = @tablename AND table_schema = @dbname AND index_name = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_services ADD INDEX idx_is_premium (is_premium)"
));
PREPARE addIndexIfNotExists FROM @preparedIndex;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- 5. Ensure verification_status column exists in tl_service_providers
SET @tablename = "tl_service_providers";
SET @columnname = "verification_status";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_service_providers ADD COLUMN verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index on verification_status
SET @indexname = "idx_verification_status";
SET @preparedIndex = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
   WHERE table_name = @tablename AND table_schema = @dbname AND index_name = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_service_providers ADD INDEX idx_verification_status (verification_status)"
));
PREPARE addIndexIfNotExists FROM @preparedIndex;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Confirmation message
SELECT 'Premium subscription system setup complete!' as Status;
SELECT '- Monthly subscription: GH₵150' as Details
UNION ALL
SELECT '- Auto-renewal supported'
UNION ALL
SELECT '- Verified badge system ready'
UNION ALL
SELECT '- Featured services carousel ready';
