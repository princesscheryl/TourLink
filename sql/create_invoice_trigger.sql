-- ============================================
-- INVOICE GENERATION TRIGGER (OPTIONAL)
-- ============================================
-- NOTE: This trigger requires SUPER privilege or log_bin_trust_function_creators=1
-- 
-- GOOD NEWS: Invoice generation is already implemented in PHP code!
-- The complete_booking() method in classes/booking_class.php automatically 
-- generates invoices when bookings are completed. This trigger is optional.
--
-- If you want to use the database trigger instead, you need:
-- 1. SUPER privilege, OR
-- 2. Set log_bin_trust_function_creators=1 in MySQL config
--
-- ============================================

-- Uncomment below if you have the required privileges:

/*
DELIMITER //

DROP TRIGGER IF EXISTS tl_after_booking_completed//

CREATE TRIGGER tl_after_booking_completed
AFTER UPDATE ON tl_bookings
FOR EACH ROW
BEGIN
    DECLARE invoice_exists INT DEFAULT 0;
    DECLARE invoice_num VARCHAR(50);
    DECLARE subtotal_val DECIMAL(10,2);
    DECLARE discount_val DECIMAL(10,2);
    DECLARE tax_val DECIMAL(10,2);
    DECLARE total_val DECIMAL(10,2);
    DECLARE due_date_val DATE;
    
    IF NEW.booking_status = 'completed' AND (OLD.booking_status IS NULL OR OLD.booking_status != 'completed') THEN
        SELECT COUNT(*) INTO invoice_exists FROM tl_invoices WHERE booking_id = NEW.booking_id;
        
        IF invoice_exists = 0 THEN
            SET invoice_num = CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 99999), 5, '0'));
            SET subtotal_val = NEW.original_amount;
            SET discount_val = COALESCE(NEW.discount_amount, 0);
            SET tax_val = 0;
            SET total_val = subtotal_val - discount_val + tax_val;
            SET due_date_val = DATE_ADD(CURDATE(), INTERVAL 30 DAY);
            
            INSERT INTO tl_invoices (
                booking_id, invoice_number, subtotal, discount_amount, 
                tax_amount, total_amount, due_date, invoice_status, sent_date
            ) VALUES (
                NEW.booking_id, invoice_num, subtotal_val, discount_val,
                tax_val, total_val, due_date_val, 'sent', NOW()
            );
        END IF;
    END IF;
END//

DELIMITER ;
*/

-- Status message
SELECT 'Invoice generation is already working via PHP code (booking_class.php). Database trigger is optional.' as Status;

