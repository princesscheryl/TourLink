-- Setup Premium Subscription System for TourLink
-- Monthly subscription: GH₵150/month
-- Run this in phpMyAdmin after backing up your database

-- 1. Update tl_premium_listings table structure
ALTER TABLE tl_premium_listings
MODIFY COLUMN amount_paid DECIMAL(10,2) NOT NULL DEFAULT 150.00,
ADD COLUMN subscription_id INT NULL AFTER premium_listing_id,
ADD COLUMN is_subscription BOOLEAN DEFAULT TRUE AFTER auto_renew,
ADD COLUMN next_billing_date DATE NULL AFTER end_date,
ADD COLUMN cancelled_at TIMESTAMP NULL,
ADD COLUMN views_count INT DEFAULT 0,
ADD COLUMN bookings_count INT DEFAULT 0;

-- 2. Update tl_subscription_payments table
ALTER TABLE tl_subscription_payments
ADD COLUMN premium_listing_id INT NULL AFTER subscription_payment_id,
MODIFY COLUMN subscription_tier ENUM('Premium') NOT NULL DEFAULT 'Premium',
MODIFY COLUMN amount DECIMAL(10,2) NOT NULL DEFAULT 150.00;

-- 3. Add indexes for better performance
ALTER TABLE tl_premium_listings
ADD INDEX idx_subscription (subscription_id),
ADD INDEX idx_next_billing (next_billing_date),
ADD INDEX idx_is_subscription (is_subscription);

ALTER TABLE tl_subscription_payments
ADD INDEX idx_premium_listing (premium_listing_id);

-- 4. Update tl_services table to track premium status (if column doesn't exist)
-- Check if column exists first, if not add it
SET @dbname = DATABASE();
SET @tablename = "tl_services";
SET @columnname = "is_premium";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " BOOLEAN DEFAULT FALSE AFTER is_active")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. Add index on is_premium for faster queries
ALTER TABLE tl_services
ADD INDEX IF NOT EXISTS idx_is_premium (is_premium);

-- 6. Ensure verification_status column exists in tl_service_providers
SET @columnname2 = "verification_status";
SET @tablename2 = "tl_service_providers";
SET @preparedStatement2 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename2)
      AND (table_schema = @dbname)
      AND (column_name = @columnname2)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename2, " ADD COLUMN ", @columnname2, " ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' AFTER account_status")
));
PREPARE alterIfNotExists2 FROM @preparedStatement2;
EXECUTE alterIfNotExists2;
DEALLOCATE PREPARE alterIfNotExists2;

-- 7. Add index on verification_status
ALTER TABLE tl_service_providers
ADD INDEX IF NOT EXISTS idx_verification_status (verification_status);

-- Confirmation message
SELECT 'Premium subscription system setup complete!' as Status;
SELECT '- Monthly subscription: GH₵150' as Details
UNION ALL
SELECT '- Auto-renewal supported'
UNION ALL
SELECT '- Verified badge system ready'
UNION ALL
SELECT '- Featured services carousel ready';
