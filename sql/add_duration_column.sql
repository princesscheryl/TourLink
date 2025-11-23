-- Add service_duration column to tl_bookings table
-- This stores the duration/quantity for the booking:
-- - For per_hour: number of hours
-- - For per_day: number of days
-- - For per_person/flat_rate: defaults to 1

ALTER TABLE tl_bookings
ADD COLUMN service_duration INT DEFAULT 1 AFTER number_of_people;

-- Add comment to clarify the column purpose
ALTER TABLE tl_bookings
MODIFY COLUMN service_duration INT DEFAULT 1 COMMENT 'Duration in hours or days based on pricing unit';
