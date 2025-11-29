-- ============================================
-- TourLink Database Schema - Table Prefix Version
-- An Inclusive E-Commerce Platform for Ghana's Tourism Sector
-- ============================================
-- For use in existing database: ecommerce_2025A_princess_donkor
-- All tables prefixed with: tl_
-- Version: 1.0
-- Date: November 12, 2025
-- ============================================

-- NOTE: This script adds TourLink tables to your existing database
-- All table names use 'tl_' prefix to avoid conflicts

SET FOREIGN_KEY_CHECKS=0;

-- ============================================
-- CORE TABLES
-- ============================================

-- Users table (for both tourists and service providers)
CREATE TABLE IF NOT EXISTS tl_users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('tourist', 'provider', 'admin') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    profile_image VARCHAR(255),
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    account_status ENUM('active', 'suspended', 'pending_verification') DEFAULT 'pending_verification',
    email_verified BOOLEAN DEFAULT FALSE,
    signup_discount_applied BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_account_status (account_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Providers table (extended profile for providers)
CREATE TABLE IF NOT EXISTS tl_service_providers (
    provider_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    business_name VARCHAR(255),
    business_registration_number VARCHAR(100),
    region ENUM('Greater Accra', 'Central', 'Ashanti', 'Northern') NOT NULL,
    location_details TEXT,
    languages_spoken TEXT COMMENT 'JSON array of languages',
    years_of_experience INT,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_documents TEXT COMMENT 'JSON array of document URLs',
    subscription_tier ENUM('Starter', 'Growth', 'Pro') DEFAULT 'Starter',
    subscription_start_date DATE,
    subscription_end_date DATE,
    onboarding_fee_paid BOOLEAN DEFAULT FALSE,
    onboarding_payment_date TIMESTAMP NULL,
    onboarding_year INT COMMENT 'Year 1, 2, or 3',
    provider_number INT COMMENT 'Sequential number within year',
    total_bookings INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    total_earnings DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    INDEX idx_region (region),
    INDEX idx_verification (verification_status),
    INDEX idx_onboarding_year (onboarding_year),
    INDEX idx_subscription_tier (subscription_tier),
    UNIQUE KEY unique_provider_number (onboarding_year, provider_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Categories table
CREATE TABLE IF NOT EXISTS tl_service_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    icon_url VARCHAR(255),
    display_order INT DEFAULT 0,
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services table (catalog of services offered by providers)
CREATE TABLE IF NOT EXISTS tl_services (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    provider_id INT NOT NULL,
    category_id INT NOT NULL,
    service_title VARCHAR(255) NOT NULL,
    service_description TEXT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    pricing_unit ENUM('per_hour', 'per_day', 'per_person', 'flat_rate') NOT NULL,
    service_location VARCHAR(255),
    available_regions TEXT COMMENT 'JSON array of regions',
    max_capacity INT,
    service_images TEXT COMMENT 'JSON array of image URLs',
    availability_status ENUM('available', 'unavailable', 'seasonal') DEFAULT 'available',
    service_status ENUM('active', 'inactive', 'pending_approval') DEFAULT 'pending_approval',
    is_premium_listing BOOLEAN DEFAULT FALSE,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    views_count INT DEFAULT 0,
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES tl_service_categories(category_id),
    INDEX idx_provider (provider_id),
    INDEX idx_category (category_id),
    INDEX idx_status (service_status),
    INDEX idx_premium (is_premium_listing),
    INDEX idx_availability (availability_status),
    FULLTEXT idx_search (service_title, service_description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SHOPPING & BOOKING TABLES
-- ============================================

-- Shopping Cart table
CREATE TABLE IF NOT EXISTS tl_cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    selected_date DATE,
    selected_time TIME,
    number_of_people INT DEFAULT 1,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES tl_services(service_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_service (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favorites/Wishlist table
CREATE TABLE IF NOT EXISTS tl_favorites (
    favorite_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES tl_services(service_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, service_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings/Orders table
CREATE TABLE IF NOT EXISTS tl_bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_reference VARCHAR(50) UNIQUE NOT NULL,
    service_id INT NOT NULL,
    tourist_id INT NOT NULL,
    provider_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    service_date DATE NOT NULL,
    service_time TIME,
    number_of_people INT DEFAULT 1,
    original_amount DECIMAL(10,2) NOT NULL COMMENT 'Amount before discount',
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL COMMENT 'Amount after discount',
    commission_amount DECIMAL(10,2) NOT NULL,
    provider_earnings DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'escrow', 'released', 'refunded') DEFAULT 'pending',
    special_requests TEXT,
    cancellation_reason TEXT,
    cancelled_by ENUM('tourist', 'provider', 'admin') NULL,
    cancellation_date TIMESTAMP NULL,
    completion_date TIMESTAMP NULL,
    FOREIGN KEY (service_id) REFERENCES tl_services(service_id),
    FOREIGN KEY (tourist_id) REFERENCES tl_users(user_id),
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id),
    INDEX idx_reference (booking_reference),
    INDEX idx_tourist (tourist_id),
    INDEX idx_provider (provider_id),
    INDEX idx_booking_status (booking_status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_service_date (service_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PAYMENT & FINANCIAL TABLES
-- ============================================

-- Payments table
CREATE TABLE IF NOT EXISTS tl_payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    transaction_reference VARCHAR(100) UNIQUE NOT NULL,
    payment_method ENUM('mobile_money', 'card', 'bank_transfer') NOT NULL,
    payment_provider VARCHAR(50) COMMENT 'MTN MoMo, Telecel Cash, Hubtel, etc.',
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'successful', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    refund_date TIMESTAMP NULL COMMENT 'Set when a refund is processed',
    provider_account_details TEXT COMMENT 'Encrypted account info',
    transaction_metadata TEXT COMMENT 'JSON for provider response data',
    FOREIGN KEY (booking_id) REFERENCES tl_bookings(booking_id),
    INDEX idx_booking (booking_id),
    INDEX idx_transaction (transaction_reference),
    INDEX idx_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices table
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
    INDEX idx_status (invoice_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription Payments table
CREATE TABLE IF NOT EXISTS tl_subscription_payments (
    subscription_payment_id INT PRIMARY KEY AUTO_INCREMENT,
    provider_id INT NOT NULL,
    subscription_tier ENUM('Starter', 'Growth', 'Pro') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'overdue') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    transaction_reference VARCHAR(100),
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id),
    INDEX idx_provider (provider_id),
    INDEX idx_status (payment_status),
    INDEX idx_billing_period (billing_period_start, billing_period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Onboarding Payments table (20 GHS verification fee)
CREATE TABLE IF NOT EXISTS tl_onboarding_payments (
    onboarding_payment_id INT PRIMARY KEY AUTO_INCREMENT,
    provider_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 20.00,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    transaction_reference VARCHAR(100),
    payment_method ENUM('mobile_money', 'card', 'bank_transfer') NOT NULL,
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id),
    INDEX idx_provider (provider_id),
    INDEX idx_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DISCOUNT & PROMOTION TABLES
-- ============================================

-- User Discounts table (10% signup bonus)
CREATE TABLE IF NOT EXISTS tl_user_discounts (
    discount_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    discount_code VARCHAR(50),
    discount_type ENUM('signup_bonus', 'referral', 'promotional', 'loyalty') NOT NULL,
    discount_percentage DECIMAL(5,2) COMMENT 'e.g., 10.00 for 10%',
    discount_amount DECIMAL(10,2) COMMENT 'Fixed amount alternative',
    applicable_to ENUM('first_booking', 'all_bookings', 'specific_service', 'subscription') DEFAULT 'first_booking',
    max_discount_amount DECIMAL(10,2) COMMENT 'Cap on percentage discounts',
    usage_limit INT DEFAULT 1,
    times_used INT DEFAULT 0,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_code (discount_code),
    INDEX idx_type (discount_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Discount Usage Tracking
CREATE TABLE IF NOT EXISTS tl_discount_usage (
    usage_id INT PRIMARY KEY AUTO_INCREMENT,
    discount_id INT NOT NULL,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    discount_amount_applied DECIMAL(10,2) NOT NULL,
    original_amount DECIMAL(10,2) NOT NULL,
    final_amount DECIMAL(10,2) NOT NULL,
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discount_id) REFERENCES tl_user_discounts(discount_id),
    FOREIGN KEY (booking_id) REFERENCES tl_bookings(booking_id),
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id),
    INDEX idx_discount (discount_id),
    INDEX idx_booking (booking_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Premium Listings table (LIMITED TO 20 SLOTS)
CREATE TABLE IF NOT EXISTS tl_premium_listings (
    premium_listing_id INT PRIMARY KEY AUTO_INCREMENT,
    provider_id INT NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 400.00,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    auto_renew BOOLEAN DEFAULT FALSE,
    payment_reference VARCHAR(100),
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id) ON DELETE CASCADE,
    INDEX idx_provider (provider_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promotional Campaigns table
CREATE TABLE IF NOT EXISTS tl_promotional_campaigns (
    campaign_id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_name VARCHAR(255) NOT NULL,
    campaign_type ENUM('sponsored_listing', 'banner', 'featured_service') NOT NULL,
    service_id INT NULL,
    provider_id INT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    budget_amount DECIMAL(10,2),
    status ENUM('draft', 'active', 'paused', 'completed') DEFAULT 'draft',
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    FOREIGN KEY (service_id) REFERENCES tl_services(service_id),
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- REVIEW & FEEDBACK TABLES
-- ============================================

-- Reviews and Ratings table
CREATE TABLE IF NOT EXISTS tl_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    provider_id INT NOT NULL,
    tourist_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(255),
    review_text TEXT,
    response_from_provider TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_date TIMESTAMP NULL,
    review_status ENUM('pending', 'approved', 'flagged', 'removed') DEFAULT 'approved',
    helpful_count INT DEFAULT 0,
    FOREIGN KEY (booking_id) REFERENCES tl_bookings(booking_id),
    FOREIGN KEY (service_id) REFERENCES tl_services(service_id),
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id),
    FOREIGN KEY (tourist_id) REFERENCES tl_users(user_id),
    INDEX idx_service (service_id),
    INDEX idx_provider (provider_id),
    INDEX idx_rating (rating),
    INDEX idx_status (review_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SUPPORT & DISPUTE TABLES
-- ============================================

-- Dispute Resolution table
CREATE TABLE IF NOT EXISTS tl_disputes (
    dispute_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    raised_by INT NOT NULL,
    dispute_type ENUM('service_quality', 'payment', 'cancellation', 'other') NOT NULL,
    dispute_description TEXT NOT NULL,
    dispute_status ENUM('open', 'under_review', 'resolved', 'closed') DEFAULT 'open',
    resolution_notes TEXT,
    raised_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_date TIMESTAMP NULL,
    resolved_by INT NULL,
    FOREIGN KEY (booking_id) REFERENCES tl_bookings(booking_id),
    FOREIGN KEY (raised_by) REFERENCES tl_users(user_id),
    FOREIGN KEY (resolved_by) REFERENCES tl_users(user_id),
    INDEX idx_booking (booking_id),
    INDEX idx_status (dispute_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support Tickets table
CREATE TABLE IF NOT EXISTS tl_support_tickets (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    ticket_status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    category VARCHAR(100),
    assigned_to INT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id),
    FOREIGN KEY (assigned_to) REFERENCES tl_users(user_id),
    INDEX idx_user (user_id),
    INDEX idx_status (ticket_status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATION & COMMUNICATION TABLES
-- ============================================

-- Notifications table
CREATE TABLE IF NOT EXISTS tl_notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_entity_type VARCHAR(50) COMMENT 'booking, service, review, etc.',
    related_entity_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SYSTEM & CONFIGURATION TABLES
-- ============================================

-- System Configuration table
CREATE TABLE IF NOT EXISTS tl_system_config (
    config_id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT NOT NULL,
    config_description TEXT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Log table (for tracking important actions)
CREATE TABLE IF NOT EXISTS tl_audit_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    log_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tl_users(user_id),
    INDEX idx_user (user_id),
    INDEX idx_timestamp (log_timestamp),
    INDEX idx_action (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

-- ============================================
-- DEFAULT DATA INSERTS
-- ============================================

-- Insert default service categories
INSERT INTO tl_service_categories (category_name, category_description, display_order) VALUES
('Tour Guides', 'Professional tour guides for various locations in Ghana', 1),
('Drivers & Transportation', 'Private drivers and transportation services', 2),
('Catering Services', 'Local food and catering for events and tours', 3),
('Translation & Interpretation', 'Language translation and interpretation services', 4),
('Cultural Performances', 'Traditional dances, music, and cultural shows', 5),
('Artisans & Crafts', 'Local artisans and craft demonstrations', 6),
('Accommodation', 'Local homestays and accommodation services', 7);

-- Insert system configuration defaults
INSERT INTO tl_system_config (config_key, config_value, config_description) VALUES
-- Commission & Fees
('commission_rate', '0.05', 'Platform commission rate (5%)'),
('onboarding_fee', '20', 'One-time onboarding and verification fee (GHS)'),

-- Subscription Tiers
('starter_tier_fee', '250', 'Monthly fee for Starter tier (GHS)'),
('growth_tier_fee', '300', 'Monthly fee for Growth tier (GHS)'),
('pro_tier_fee', '400', 'Monthly fee for Pro tier (GHS)'),

-- Premium Listings
('premium_listing_price', '400', 'Monthly price for premium listing (GHS)'),
('max_premium_slots', '20', 'Maximum number of premium listing slots available'),

-- Provider Onboarding Limits
('max_providers_year_1', '48', 'Maximum providers to onboard in Year 1'),
('max_providers_year_2', '96', 'Maximum providers to onboard in Year 2'),
('max_providers_year_3', '160', 'Maximum providers to onboard in Year 3'),
('current_operational_year', '1', 'Current year of operation (1, 2, or 3)'),

-- Discount Settings
('signup_discount_percentage', '10', 'Percentage discount for new user signups (%)'),
('signup_discount_max_amount', '50', 'Maximum discount amount for signup bonus (GHS)'),
('signup_discount_validity_days', '90', 'Number of days signup discount remains valid'),

-- Rating System
('min_rating', '1', 'Minimum rating value'),
('max_rating', '5', 'Maximum rating value'),

-- Customer Support
('customer_support_response_time', '20', 'Target response time in minutes'),
('support_email', 'support@tourlink.com.gh', 'Customer support email'),
('support_phone', '+233 XX XXX XXXX', 'Customer support phone number'),

-- Platform Settings
('platform_name', 'TourLink', 'Platform name'),
('platform_tagline', 'Connecting Ghana, One Experience at a Time', 'Platform tagline'),
('currency', 'GHS', 'Platform currency'),
('timezone', 'Africa/Accra', 'Platform timezone');

-- ============================================
-- VIEWS FOR COMMON QUERIES
-- ============================================

-- View for active services with provider details
CREATE OR REPLACE VIEW vw_tl_active_services AS
SELECT 
    s.service_id,
    s.service_title,
    s.service_description,
    s.base_price,
    s.pricing_unit,
    s.service_location,
    s.is_premium_listing,
    s.views_count,
    sc.category_name,
    sp.provider_id,
    sp.business_name,
    sp.region,
    sp.average_rating,
    sp.total_bookings,
    u.first_name,
    u.last_name,
    u.email
FROM tl_services s
JOIN tl_service_categories sc ON s.category_id = sc.category_id
JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
JOIN tl_users u ON sp.user_id = u.user_id
WHERE s.service_status = 'active' 
AND s.availability_status = 'available'
AND sp.verification_status = 'verified';

-- View for provider dashboard statistics
CREATE OR REPLACE VIEW vw_tl_provider_stats AS
SELECT 
    sp.provider_id,
    sp.business_name,
    sp.subscription_tier,
    COUNT(DISTINCT s.service_id) as total_services,
    COUNT(DISTINCT b.booking_id) as total_bookings,
    SUM(CASE WHEN b.booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(CASE WHEN b.booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
    sp.average_rating,
    sp.total_earnings,
    COUNT(DISTINCT r.review_id) as total_reviews
FROM tl_service_providers sp
LEFT JOIN tl_services s ON sp.provider_id = s.provider_id
LEFT JOIN tl_bookings b ON sp.provider_id = b.provider_id
LEFT JOIN tl_reviews r ON sp.provider_id = r.provider_id
GROUP BY sp.provider_id;

-- ============================================
-- USEFUL STORED PROCEDURES
-- ============================================

DELIMITER //

-- Procedure to calculate booking amounts
DROP PROCEDURE IF EXISTS tl_calculate_booking_amounts//
CREATE PROCEDURE tl_calculate_booking_amounts(
    IN p_service_id INT,
    IN p_number_of_people INT,
    IN p_discount_id INT,
    OUT p_original_amount DECIMAL(10,2),
    OUT p_discount_amount DECIMAL(10,2),
    OUT p_total_amount DECIMAL(10,2),
    OUT p_commission_amount DECIMAL(10,2),
    OUT p_provider_earnings DECIMAL(10,2)
)
BEGIN
    DECLARE v_base_price DECIMAL(10,2);
    DECLARE v_commission_rate DECIMAL(5,2);
    DECLARE v_discount_percentage DECIMAL(5,2);
    DECLARE v_max_discount DECIMAL(10,2);
    
    -- Get service price
    SELECT base_price INTO v_base_price FROM tl_services WHERE service_id = p_service_id;
    
    -- Get commission rate
    SELECT config_value INTO v_commission_rate FROM tl_system_config WHERE config_key = 'commission_rate';
    
    -- Calculate original amount
    SET p_original_amount = v_base_price * p_number_of_people;
    
    -- Calculate discount if applicable
    SET p_discount_amount = 0;
    IF p_discount_id IS NOT NULL THEN
        SELECT discount_percentage, max_discount_amount 
        INTO v_discount_percentage, v_max_discount
        FROM tl_user_discounts 
        WHERE discount_id = p_discount_id AND is_active = TRUE;
        
        IF v_discount_percentage IS NOT NULL THEN
            SET p_discount_amount = p_original_amount * (v_discount_percentage / 100);
            IF v_max_discount IS NOT NULL AND p_discount_amount > v_max_discount THEN
                SET p_discount_amount = v_max_discount;
            END IF;
        END IF;
    END IF;
    
    -- Calculate final amounts
    SET p_total_amount = p_original_amount - p_discount_amount;
    SET p_commission_amount = p_total_amount * v_commission_rate;
    SET p_provider_earnings = p_total_amount - p_commission_amount;
END//

-- Procedure to update provider statistics
DROP PROCEDURE IF EXISTS tl_update_provider_stats//
CREATE PROCEDURE tl_update_provider_stats(IN p_provider_id INT)
BEGIN
    DECLARE v_avg_rating DECIMAL(3,2);
    DECLARE v_total_bookings INT;
    DECLARE v_total_earnings DECIMAL(10,2);
    
    -- Calculate average rating
    SELECT COALESCE(AVG(rating), 0) INTO v_avg_rating
    FROM tl_reviews
    WHERE provider_id = p_provider_id AND review_status = 'approved';
    
    -- Count total bookings
    SELECT COUNT(*) INTO v_total_bookings
    FROM tl_bookings
    WHERE provider_id = p_provider_id AND booking_status = 'completed';
    
    -- Calculate total earnings
    SELECT COALESCE(SUM(provider_earnings), 0) INTO v_total_earnings
    FROM tl_bookings
    WHERE provider_id = p_provider_id 
    AND booking_status = 'completed'
    AND payment_status = 'released';
    
    -- Update provider record
    UPDATE tl_service_providers
    SET average_rating = v_avg_rating,
        total_bookings = v_total_bookings,
        total_earnings = v_total_earnings
    WHERE provider_id = p_provider_id;
END//

DELIMITER ;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER //

-- Trigger to generate booking reference
DROP TRIGGER IF EXISTS tl_before_booking_insert//
CREATE TRIGGER tl_before_booking_insert
BEFORE INSERT ON tl_bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_reference IS NULL OR NEW.booking_reference = '' THEN
        SET NEW.booking_reference = CONCAT('TL', YEAR(NOW()), LPAD(FLOOR(RAND() * 999999), 6, '0'));
    END IF;
END//

-- Trigger to auto-generate invoice when booking is confirmed
DROP TRIGGER IF EXISTS tl_after_booking_confirmed//
CREATE TRIGGER tl_after_booking_confirmed
AFTER UPDATE ON tl_bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_status = 'confirmed' AND OLD.booking_status != 'confirmed' THEN
        INSERT INTO tl_invoices (
            booking_id, 
            invoice_number, 
            subtotal, 
            discount_amount, 
            total_amount,
            invoice_status
        ) VALUES (
            NEW.booking_id,
            CONCAT('INV-', NEW.booking_reference),
            NEW.original_amount,
            NEW.discount_amount,
            NEW.total_amount,
            'sent'
        );
    END IF;
END//

-- Trigger to update service view count
DROP TRIGGER IF EXISTS tl_after_service_view//
CREATE TRIGGER tl_after_service_view
AFTER INSERT ON tl_audit_log
FOR EACH ROW
BEGIN
    IF NEW.action_type = 'service_viewed' AND NEW.entity_type = 'service' THEN
        UPDATE tl_services 
        SET views_count = views_count + 1 
        WHERE service_id = NEW.entity_id;
    END IF;
END//

DELIMITER ;

-- ============================================
-- ADDITIONAL INDEXES FOR PERFORMANCE
-- ============================================

-- Composite indexes for common queries
CREATE INDEX idx_tl_booking_tourist_status ON tl_bookings(tourist_id, booking_status);
CREATE INDEX idx_tl_booking_provider_status ON tl_bookings(provider_id, booking_status);
CREATE INDEX idx_tl_service_category_status ON tl_services(category_id, service_status);
CREATE INDEX idx_tl_payment_booking_status ON tl_payments(booking_id, payment_status);

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Display success message and table count
SELECT 'TourLink tables created successfully!' as Message;
SELECT 'All tables use prefix: tl_' as Prefix_Info;
SELECT COUNT(*) as TourLink_Tables 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name LIKE 'tl_%';

-- List all TourLink tables created
SELECT table_name as TourLink_Tables_Created
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name LIKE 'tl_%'
ORDER BY table_name;

-- ============================================
-- END OF SCHEMA
-- ============================================
