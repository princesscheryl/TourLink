-- Add Paystack payment columns to tl_payments table

ALTER TABLE tl_payments
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'paystack' COMMENT 'Payment method: paystack, cash, bank_transfer, mobile_money',
ADD COLUMN IF NOT EXISTS transaction_ref VARCHAR(100) NULL COMMENT 'Paystack transaction reference',
ADD COLUMN IF NOT EXISTS authorization_code VARCHAR(100) NULL COMMENT 'Authorization code from payment gateway',
ADD COLUMN IF NOT EXISTS payment_channel VARCHAR(50) NULL COMMENT 'Payment channel: card, mobile_money, etc.',
ADD COLUMN IF NOT EXISTS discount_code VARCHAR(50) NULL COMMENT 'Discount code used (if any)',
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Discount amount applied';

-- Add indexes for faster queries
ALTER TABLE tl_payments ADD INDEX IF NOT EXISTS idx_transaction_ref (transaction_ref);
ALTER TABLE tl_payments ADD INDEX IF NOT EXISTS idx_payment_method (payment_method);
ALTER TABLE tl_payments ADD INDEX IF NOT EXISTS idx_discount_code (discount_code);

-- Update booking_status enum to include escrow status if not already present
ALTER TABLE tl_bookings MODIFY COLUMN booking_status
ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending';

-- Ensure payment_status enum includes all needed statuses
ALTER TABLE tl_bookings MODIFY COLUMN payment_status
ENUM('pending', 'escrow', 'released', 'refunded', 'failed') DEFAULT 'pending';
