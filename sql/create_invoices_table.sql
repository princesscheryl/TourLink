-- Create tl_invoices table if it doesn't exist
-- This table stores invoices for completed bookings

CREATE TABLE IF NOT EXISTS tl_invoices (
    invoice_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    invoice_status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    pdf_url VARCHAR(255),
    sent_date TIMESTAMP NULL,
    paid_date TIMESTAMP NULL,
    FOREIGN KEY (booking_id) REFERENCES tl_bookings(booking_id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_status (invoice_status),
    INDEX idx_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Confirmation
SELECT 'tl_invoices table created successfully!' as Status;

