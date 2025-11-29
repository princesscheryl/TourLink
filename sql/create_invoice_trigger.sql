-- Trigger to automatically generate invoice when booking is completed
-- This ensures invoices are created automatically when bookings are marked as completed

DELIMITER //

DROP TRIGGER IF EXISTS tl_after_booking_completed//

CREATE TRIGGER tl_after_booking_completed
AFTER UPDATE ON tl_bookings
FOR EACH ROW
BEGIN
    -- Check if booking status changed to 'completed'
    IF NEW.booking_status = 'completed' AND (OLD.booking_status IS NULL OR OLD.booking_status != 'completed') THEN
        -- Check if invoice doesn't already exist
        IF NOT EXISTS (SELECT 1 FROM tl_invoices WHERE booking_id = NEW.booking_id) THEN
            -- Generate invoice number
            SET @invoice_number = CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 99999), 5, '0'));
            
            -- Calculate amounts
            SET @subtotal = NEW.original_amount;
            SET @discount_amount = COALESCE(NEW.discount_amount, 0);
            SET @tax_amount = 0;
            SET @total_amount = @subtotal - @discount_amount + @tax_amount;
            SET @due_date = DATE_ADD(CURDATE(), INTERVAL 30 DAY);
            
            -- Insert invoice
            INSERT INTO tl_invoices (
                booking_id,
                invoice_number,
                subtotal,
                discount_amount,
                tax_amount,
                total_amount,
                due_date,
                invoice_status,
                sent_date
            ) VALUES (
                NEW.booking_id,
                @invoice_number,
                @subtotal,
                @discount_amount,
                @tax_amount,
                @total_amount,
                @due_date,
                'sent',
                NOW()
            );
        END IF;
    END IF;
END//

DELIMITER ;

-- Confirmation
SELECT 'Invoice generation trigger created successfully!' as Status;

