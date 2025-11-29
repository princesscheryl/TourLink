-- Remove escrow_release_date column from tl_payments table
-- This column is no longer used since escrow functionality has been removed

-- Check if column exists before attempting to drop it
SET @dbname = DATABASE();
SET @tablename = "tl_payments";
SET @columnname = "escrow_release_date";

-- Drop the column if it exists
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_name = @tablename 
   AND table_schema = @dbname 
   AND column_name = @columnname) > 0,
  CONCAT("ALTER TABLE ", @tablename, " DROP COLUMN ", @columnname),
  "SELECT 'Column does not exist, skipping...' as Status"
));

PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Confirmation message
SELECT 'Escrow release date column removed successfully!' as Status;

