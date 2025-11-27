-- Add guest_details column to tl_bookings table
-- This stores additional guest information as JSON

ALTER TABLE tl_bookings
ADD COLUMN guest_details TEXT NULL
AFTER special_requests;

-- Add comment to column
ALTER TABLE tl_bookings
MODIFY COLUMN guest_details TEXT NULL
COMMENT 'Guest details stored as JSON (first_name, last_name, email, phone, booking_for, travelling_for_work, arrival_time)';
