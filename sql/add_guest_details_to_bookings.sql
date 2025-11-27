-- Add guest_details column to tl_bookings table
-- This stores additional guest information as JSON
-- This script is safe to run multiple times (idempotent)

SET @dbname = DATABASE();
SET @tablename = "tl_bookings";
SET @columnname = "guest_details";

-- Check if column exists, if not add it
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE tl_bookings ADD COLUMN guest_details TEXT NULL COMMENT 'Guest details stored as JSON (first_name, last_name, email, phone, booking_for, travelling_for_work, arrival_time)'"
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Confirmation message
SELECT 'Guest details column added successfully!' as Status;
