-- ============================================
-- Sample Data for TourLink Platform
-- Ghana Tourism Services and Providers
-- ============================================

-- Insert sample users (service providers)
INSERT INTO tl_users (email, password_hash, user_type, first_name, last_name, phone, account_status, email_verified) VALUES
('kwame.tourist@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'tourist', 'Kwame', 'Mensah', '+233244123456', 'active', TRUE),
('ama.provider@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'provider', 'Ama', 'Asante', '+233201234567', 'active', TRUE),
('kofi.tours@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'provider', 'Kofi', 'Boateng', '+233208765432', 'active', TRUE),
('akua.driver@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'provider', 'Akua', 'Osei', '+233244567890', 'active', TRUE),
('yaw.catering@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'provider', 'Yaw', 'Mensah', '+233207654321', 'active', TRUE),
('abena.translate@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'provider', 'Abena', 'Oppong', '+233209876543', 'active', TRUE),
('kwesi.culture@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'provider', 'Kwesi', 'Amponsah', '+233246789012', 'active', TRUE),
('efua.artisan@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'provider', 'Efua', 'Agyeman', '+233203456789', 'active', TRUE);

-- Insert service providers (extended profiles)
INSERT INTO tl_service_providers (user_id, business_name, region, location_details, languages_spoken, years_of_experience, verification_status, subscription_tier, onboarding_fee_paid, average_rating, total_bookings) VALUES
(2, 'Ama Tours Ghana', 'Greater Accra', 'Osu, Accra', '["English", "Twi", "Ga"]', 5, 'verified', 'Growth', TRUE, 4.8, 127),
(3, 'Kofi Heritage Tours', 'Ashanti', 'Kumasi', '["English", "Twi", "Asante"]', 8, 'verified', 'Pro', TRUE, 4.9, 203),
(4, 'Akua Safe Rides', 'Greater Accra', 'Tema', '["English", "Twi", "Ga"]', 6, 'verified', 'Starter', TRUE, 4.7, 89),
(5, 'Yaw Traditional Catering', 'Central', 'Cape Coast', '["English", "Fante"]', 10, 'verified', 'Growth', TRUE, 4.9, 156),
(6, 'Abena Language Services', 'Greater Accra', 'Airport Residential', '["English", "French", "Twi", "Ewe"]', 4, 'verified', 'Starter', TRUE, 4.6, 67),
(7, 'Kwesi Cultural Troupe', 'Ashanti', 'Kumasi', '["English", "Twi"]', 12, 'verified', 'Growth', TRUE, 4.8, 95),
(8, 'Efua Kente Creations', 'Ashanti', 'Bonwire, Kumasi', '["English", "Twi"]', 15, 'verified', 'Starter', TRUE, 4.9, 142);

-- Insert sample services for each category
-- Category 1: Tour Guides
INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, availability_status, service_status, is_premium_listing) VALUES
(1, 1, 'Accra City Heritage Tour', 'Explore the rich history of Accra including Independence Square, Kwame Nkrumah Memorial Park, Jamestown, and Makola Market. Perfect for first-time visitors.', 250.00, 'per_person', 'Accra', '["Greater Accra"]', 10, '["https://images.unsplash.com/photo-1523805009345-7448845a9e53?w=800", "https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800"]', 'available', 'active', TRUE),
(2, 1, 'Cape Coast Castle & Kakum Tour', 'Full day tour to Cape Coast Castle (UNESCO World Heritage Site) and Kakum National Park canopy walkway. Includes transportation and guide.', 450.00, 'per_person', 'Cape Coast', '["Central", "Greater Accra"]', 15, '["https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=800"]', 'available', 'active', TRUE),
(2, 1, 'Kumasi Cultural Tour', 'Visit the Manhyia Palace Museum, Kejetia Market, and traditional kente weaving villages. Experience the heart of Ashanti culture.', 350.00, 'per_person', 'Kumasi', '["Ashanti"]', 12, '["https://images.unsplash.com/photo-1609137144813-7d9921338f24?w=800"]', 'available', 'active', FALSE);

-- Category 2: Drivers & Transportation
INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, availability_status, service_status, is_premium_listing) VALUES
(3, 2, 'Airport Pickup & Drop-off Service', 'Reliable transportation between Kotoka International Airport and any location in Accra. Professional driver with clean, air-conditioned vehicle.', 150.00, 'flat_rate', 'Accra', '["Greater Accra"]', 4, '["https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=800"]', 'available', 'active', TRUE),
(3, 2, 'Full Day Car Hire with Driver', 'Hire a comfortable sedan with experienced driver for a full day. Perfect for sightseeing, business meetings, or shopping tours.', 800.00, 'per_day', 'Accra & Tema', '["Greater Accra", "Central"]', 4, '["https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800"]', 'available', 'active', FALSE),
(3, 2, 'Accra to Kumasi Private Transfer', 'Comfortable private transfer from Accra to Kumasi with rest stops. Experienced driver who knows the route well.', 600.00, 'flat_rate', 'Accra to Kumasi', '["Greater Accra", "Ashanti"]', 4, NULL, 'available', 'active', FALSE);

-- Category 3: Catering Services
INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, availability_status, service_status, is_premium_listing) VALUES
(4, 3, 'Traditional Ghanaian Buffet', 'Authentic Ghanaian dishes including jollof rice, fufu, banku, grilled tilapia, and more. Perfect for groups and events.', 80.00, 'per_person', 'Cape Coast', '["Central", "Greater Accra"]', 100, '["https://images.unsplash.com/photo-1604329760661-e71dc83f8f26?w=800"]', 'available', 'active', TRUE),
(4, 3, 'Street Food Tasting Experience', 'Guided street food experience featuring local favorites like waakye, kelewele, fried yam, and bofrot. Includes drinks.', 50.00, 'per_person', 'Accra', '["Greater Accra"]', 20, '["https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800"]', 'available', 'active', FALSE),
(4, 3, 'Private Chef for Your Stay', 'Personal chef prepares authentic Ghanaian meals in your accommodation. Includes shopping for ingredients and cooking.', 500.00, 'per_day', 'Greater Accra', '["Greater Accra"]', 10, NULL, 'available', 'active', FALSE);

-- Category 4: Translation & Interpretation
INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, availability_status, service_status, is_premium_listing) VALUES
(5, 4, 'English-Twi Translation Services', 'Professional translation between English and Twi for documents, conversations, or business meetings.', 100.00, 'per_hour', 'Accra', '["Greater Accra", "Ashanti"]', 1, NULL, 'available', 'active', FALSE),
(5, 4, 'French-English Interpretation', 'Fluent French-English interpretation for tours, business meetings, or personal needs.', 120.00, 'per_hour', 'Accra', '["Greater Accra"]', 1, NULL, 'available', 'active', FALSE),
(5, 4, 'Multi-language Tour Support', 'Accompany tourists as a language interpreter for the day. Fluent in English, French, Twi, and Ewe.', 400.00, 'per_day', 'Accra & surrounding areas', '["Greater Accra", "Central"]', 1, NULL, 'available', 'active', FALSE);

-- Category 5: Cultural Performances
INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, availability_status, service_status, is_premium_listing) VALUES
(6, 5, 'Traditional Adowa Dance Performance', 'Authentic Ashanti Adowa dance performance by trained dancers in traditional costumes. Perfect for events and cultural experiences.', 1200.00, 'flat_rate', 'Kumasi', '["Ashanti", "Greater Accra"]', 200, '["https://images.unsplash.com/photo-1508700929628-666bc8bd84ea?w=800"]', 'available', 'active', TRUE),
(6, 5, 'Kpanlogo Drumming & Dance Workshop', 'Interactive workshop teaching Ga traditional Kpanlogo drumming and dancing. Includes instruments and refreshments.', 80.00, 'per_person', 'Accra', '["Greater Accra"]', 25, '["https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800"]', 'available', 'active', FALSE),
(6, 5, 'Cultural Evening Show', '2-hour cultural performance featuring various Ghanaian dances, drumming, and storytelling. Includes light refreshments.', 150.00, 'per_person', 'Kumasi', '["Ashanti"]', 50, NULL, 'available', 'active', FALSE);

-- Category 6: Artisans & Crafts
INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, availability_status, service_status, is_premium_listing) VALUES
(7, 6, 'Kente Weaving Workshop', 'Learn traditional kente weaving from master weavers in Bonwire. Includes materials and a small finished piece to take home.', 200.00, 'per_person', 'Bonwire, Kumasi', '["Ashanti"]', 8, '["https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=800"]', 'available', 'active', TRUE),
(7, 6, 'Adinkra Symbol Printing Experience', 'Hands-on workshop learning traditional Adinkra symbol stamping on fabric. Create your own custom piece.', 150.00, 'per_person', 'Kumasi', '["Ashanti"]', 10, '["https://images.unsplash.com/photo-1515405295579-ba7b45403062?w=800"]', 'available', 'active', FALSE),
(7, 6, 'Beads Making & Crafts Tour', 'Visit Krobo bead makers and learn the traditional craft. Create your own bracelet or necklace.', 120.00, 'per_person', 'Krobo, Eastern Region', '["Greater Accra", "Central"]', 15, NULL, 'available', 'active', FALSE);

-- Category 7: Accommodation
INSERT INTO tl_services (provider_id, category_id, service_title, service_description, base_price, pricing_unit, service_location, available_regions, max_capacity, service_images, availability_status, service_status, is_premium_listing) VALUES
(1, 7, 'Local Homestay in Accra', 'Stay with a local family in a safe neighborhood in Accra. Includes breakfast and insights into daily Ghanaian life.', 150.00, 'per_day', 'Osu, Accra', '["Greater Accra"]', 2, '["https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800"]', 'available', 'active', FALSE),
(4, 7, 'Coastal Guesthouse Stay', 'Comfortable guesthouse accommodation near the beach in Cape Coast. Includes breakfast and WiFi.', 200.00, 'per_day', 'Cape Coast', '["Central"]', 4, '["https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800"]', 'available', 'active', FALSE);

-- ============================================
-- Update provider statistics
-- ============================================
UPDATE tl_service_providers sp
SET total_bookings = (
    SELECT COUNT(*) FROM tl_services s WHERE s.provider_id = sp.provider_id
);

-- ============================================
-- END OF SAMPLE DATA
-- ============================================
