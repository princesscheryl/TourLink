-- Add Paystack payment columns to tl_payments table
-- Note: If a column already exists, that specific ALTER will fail but others will continue

-- Add payment_method column
ALTER TABLE tl_payments
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'paystack' COMMENT 'Payment method: paystack, cash, bank_transfer, mobile_money';

-- Add transaction_ref column
ALTER TABLE tl_payments
ADD COLUMN transaction_ref VARCHAR(100) NULL COMMENT 'Paystack transaction reference';

-- Add authorization_code column
ALTER TABLE tl_payments
ADD COLUMN authorization_code VARCHAR(100) NULL COMMENT 'Authorization code from payment gateway';

-- Add payment_channel column
ALTER TABLE tl_payments
ADD COLUMN payment_channel VARCHAR(50) NULL COMMENT 'Payment channel: card, mobile_money, etc.';

-- Add discount_code column
ALTER TABLE tl_payments
ADD COLUMN discount_code VARCHAR(50) NULL COMMENT 'Discount code used (if any)';

-- Add discount_amount column
ALTER TABLE tl_payments
ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Discount amount applied';

-- Add indexes for faster queries
ALTER TABLE tl_payments ADD INDEX idx_transaction_ref (transaction_ref);
ALTER TABLE tl_payments ADD INDEX idx_payment_method (payment_method);
ALTER TABLE tl_payments ADD INDEX idx_discount_code (discount_code);

-- Update booking_status enum to include all needed statuses
ALTER TABLE tl_bookings MODIFY COLUMN booking_status
ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';

-- Ensure payment_status enum includes all needed statuses
ALTER TABLE tl_bookings MODIFY COLUMN payment_status
ENUM('pending', 'escrow', 'released', 'refunded', 'failed') DEFAULT 'pending';
