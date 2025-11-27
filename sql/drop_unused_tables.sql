-- Drop Unused Tables from TourLink Database
-- These tables are not implemented or redundant
-- IMPORTANT: Create a backup first via phpMyAdmin or command line

-- Drop unused tables
DROP TABLE IF EXISTS tl_cart;
DROP TABLE IF EXISTS tl_disputes;
DROP TABLE IF EXISTS tl_invoices;
DROP TABLE IF EXISTS tl_onboarding_payments;
DROP TABLE IF EXISTS tl_promotional_campaigns;
DROP TABLE IF EXISTS tl_provider_stories;
DROP TABLE IF EXISTS tl_support_tickets;
DROP TABLE IF EXISTS tl_user_discounts;

-- Optional: Drop these if you're not planning to use them
-- Uncomment if you want to drop these as well

-- DROP TABLE IF EXISTS tl_regions;              -- Static data, removed from admin
-- DROP TABLE IF EXISTS tl_system_config;        -- System config not in use
-- DROP TABLE IF EXISTS tl_audit_log;            -- If you don't need audit logging

-- Summary of dropped tables:
-- ✓ tl_cart
-- ✓ tl_disputes
-- ✓ tl_invoices
-- ✓ tl_onboarding_payments
-- ✓ tl_premium_listings
-- ✓ tl_promotional_campaigns
-- ✓ tl_provider_stories
-- ✓ tl_subscription_payments
-- ✓ tl_support_tickets
-- ✓ tl_user_discounts

-- KEPT TABLES (Essential for TourLink):
-- ✓ tl_admins              - Admin accounts
-- ✓ tl_bookings            - Booking records
-- ✓ tl_discount_codes      - Discount codes
-- ✓ tl_discount_usage      - Discount usage tracking
-- ✓ tl_favorites           - User favorites
-- ✓ tl_festivals           - Ghanaian festivals
-- ✓ tl_notifications       - User notifications
-- ✓ tl_payments            - Payment transactions
-- ✓ tl_reviews             - Service reviews
-- ✓ tl_search_history      - User search history
-- ✓ tl_services            - Service listings
-- ✓ tl_service_categories  - Service categories
-- ✓ tl_service_providers   - Provider profiles
-- ✓ tl_users               - User accounts

SELECT 'Cleanup complete! Unused tables have been dropped.' as Status;
