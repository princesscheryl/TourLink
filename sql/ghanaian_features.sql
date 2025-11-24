-- =====================================================
-- TourLink Ghanaian-Centric Features Database Schema
-- Run this SQL to add new tables and data
-- =====================================================

-- -----------------------------------------------------
-- Table: tl_admins - Platform administrators
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS tl_admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: Admin@123)
INSERT INTO tl_admins (email, password, first_name, last_name, role) VALUES
('admin@tourlink.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Admin', 'super_admin');

-- -----------------------------------------------------
-- Table: tl_festivals - Ghana Festival Calendar
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS tl_festivals (
    festival_id INT AUTO_INCREMENT PRIMARY KEY,
    festival_name VARCHAR(150) NOT NULL,
    description TEXT,
    region VARCHAR(50) NOT NULL,
    location VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE,
    month_typically TINYINT, -- 1-12 for recurring festivals
    festival_type ENUM('traditional', 'cultural', 'music', 'art', 'food', 'religious', 'national') DEFAULT 'cultural',
    image_url VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert major Ghanaian festivals
INSERT INTO tl_festivals (festival_name, description, region, location, start_date, end_date, month_typically, festival_type, is_featured) VALUES
('Homowo Festival', 'The Ga people celebrate this harvest festival with traditional rituals, drumming, and the sprinkling of kpokpoi. A time of joy and thanksgiving after the hunger season.', 'Greater Accra', 'Accra', '2025-08-01', '2025-08-31', 8, 'traditional', 1),
('Panafest', 'Pan-African Historical Theatre Festival celebrating African heritage, featuring performances, lectures, and pilgrimages to historic slave sites.', 'Central', 'Cape Coast', '2025-07-25', '2025-08-03', 7, 'cultural', 1),
('Chale Wote Street Art Festival', 'West Africa''s largest street art festival transforming Jamestown with murals, performances, music, and artistic expression.', 'Greater Accra', 'Jamestown, Accra', '2025-08-15', '2025-08-17', 8, 'art', 1),
('Aboakyir Festival', 'The Effutu people of Winneba celebrate with a deer hunt, drumming, and traditional ceremonies honoring their deity Penkye Otu.', 'Central', 'Winneba', '2025-05-03', '2025-05-03', 5, 'traditional', 1),
('Hogbetsotso Festival', 'The Anlo Ewe celebrate their migration from Notsie with colorful durbar, traditional dances, and cultural displays.', 'Volta', 'Anloga', '2025-11-01', '2025-11-01', 11, 'traditional', 1),
('Akwasidae Festival', 'Celebrated every six weeks by the Ashanti, this festival honors ancestors and the Golden Stool at the Manhyia Palace.', 'Ashanti', 'Kumasi', '2025-01-12', '2025-01-12', NULL, 'traditional', 1),
('Bakatue Festival', 'The people of Elmina celebrate the lifting of the ban on fishing with boat races, music, and traditional rituals.', 'Central', 'Elmina', '2025-07-01', '2025-07-01', 7, 'traditional', 0),
('Odwira Festival', 'A purification festival celebrated by the Akuapem people with processions, traditional rites, and cultural displays.', 'Eastern', 'Akropong', '2025-10-01', '2025-10-07', 10, 'traditional', 0),
('Kundum Festival', 'Harvest festival of the Nzema and Ahanta people featuring colorful masquerades and traditional dances.', 'Western', 'Axim', '2025-09-01', '2025-10-15', 9, 'traditional', 0),
('Damba Festival', 'Celebrated by the Dagomba, Mamprusi, and other northern ethnic groups to mark the birth of Prophet Mohammed.', 'Northern', 'Tamale', '2025-09-15', '2025-09-15', NULL, 'religious', 1);

-- -----------------------------------------------------
-- Table: tl_regions - Ghana Regions with Cultural Context
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS tl_regions (
    region_id INT AUTO_INCREMENT PRIMARY KEY,
    region_name VARCHAR(50) NOT NULL UNIQUE,
    region_capital VARCHAR(50),
    description TEXT,
    cultural_highlights TEXT,
    major_attractions TEXT,
    local_cuisine TEXT,
    best_time_to_visit VARCHAR(100),
    image_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1
);

-- Insert Ghana's regions with cultural context
INSERT INTO tl_regions (region_name, region_capital, description, cultural_highlights, major_attractions, local_cuisine, best_time_to_visit) VALUES
('Greater Accra', 'Accra', 'The vibrant capital region blending modern city life with rich Ga traditions. Home to Ghana''s seat of government and main commercial hub.', 'Ga culture, Homowo Festival, traditional boxing (Ga wrestling), Kpanlogo music and dance', 'Independence Square, Kwame Nkrumah Memorial, Jamestown Lighthouse, Makola Market, Labadi Beach', 'Kenkey with fish, Kpokpoi, Ga Jollof, Banku with okro soup', 'November to March (dry season)'),
('Ashanti', 'Kumasi', 'The heartland of the mighty Ashanti Kingdom, rich in gold, kente cloth, and powerful traditions centered around the Golden Stool.', 'Ashanti Kingdom heritage, Kente weaving, Adinkra symbols, Akwasidae Festival, traditional chieftaincy', 'Manhyia Palace, Kumasi Fort, Kejetia Market, Lake Bosomtwe, Bonwire Kente Village', 'Fufu with light soup, Kokonte, Garden egg stew, Ashanti Jollof', 'December to February'),
('Central', 'Cape Coast', 'A region steeped in history, home to UNESCO World Heritage slave castles and the birthplace of Ghana''s education system.', 'Fante culture, Panafest, Oguaa Fetu Afahye, traditional fishing communities', 'Cape Coast Castle, Elmina Castle, Kakum National Park, Hans Cottage Botel', 'Fante Fante (fish stew), Palm nut soup, Aprapransa, Fresh seafood', 'July to August (festival season)'),
('Northern', 'Tamale', 'Ghana''s largest region featuring savanna landscapes, unique mud-and-stick architecture, and vibrant Islamic and traditional cultures.', 'Dagomba chieftaincy, Damba Festival, traditional smock weaving, fire festivals', 'Mole National Park, Larabanga Mosque, Salaga Slave Market, Paga Crocodile Pond', 'Tuo Zaafi (TZ), Wasawasa, Guinea fowl dishes, Shea butter cuisine', 'October to March (dry season, best for wildlife)');

-- -----------------------------------------------------
-- Table: tl_provider_stories - Success Stories
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS tl_provider_stories (
    story_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT,
    provider_name VARCHAR(100) NOT NULL,
    business_name VARCHAR(150),
    region VARCHAR(50),
    story_title VARCHAR(200),
    story_content TEXT NOT NULL,
    quote TEXT,
    image_url VARCHAR(255),
    revenue_increase VARCHAR(50),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES tl_service_providers(provider_id) ON DELETE SET NULL
);

-- Insert sample success stories
INSERT INTO tl_provider_stories (provider_name, business_name, region, story_title, story_content, quote, revenue_increase, is_featured) VALUES
('Kwame Asante', 'Asante Heritage Tours', 'Ashanti', 'From Local Guide to Thriving Business', 'Kwame started as a part-time tour guide in Kumasi, showing visitors the Manhyia Palace and kente weaving villages. After joining TourLink, his bookings increased dramatically. He now employs three other guides and has expanded to offer multi-day Ashanti cultural immersion experiences.', 'TourLink connected me to tourists I never could have reached on my own. Now I''m preserving our culture while supporting my family.', '340%', 1),
('Ama Serwaa', 'Village Homestay Experience', 'Central', 'Bringing Tourists to Our Village', 'Ama runs a community homestay program in a small fishing village near Elmina. Through TourLink, international visitors now experience authentic Ghanaian village life - fishing with locals, cooking traditional meals, and participating in community activities. The income supports the entire village.', 'Our village was unknown to tourists. Now we share our way of life and earn income without leaving our home.', '280%', 1),
('Ibrahim Yakubu', 'Northern Safari Adventures', 'Northern', 'Wildlife Tours Supporting Conservation', 'Ibrahim grew up near Mole National Park and turned his knowledge of local wildlife into a safari tour business. TourLink helped him professionalize his operations and reach international tourists. He now contributes to local conservation efforts and employs guides from surrounding villages.', 'I went from an informal guide to running a real tourism business. TourLink made it possible.', '420%', 1);

-- -----------------------------------------------------
-- Add 'is_rural_provider' and 'is_community_tourism' flags
-- Note: If columns already exist, you can skip this section
-- -----------------------------------------------------

-- Check and add columns to tl_service_providers
SET @dbname = DATABASE();
SET @tablename = "tl_service_providers";
SET @columnname = "is_rural_provider";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) DEFAULT 0")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "community_name";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(100)")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add columns to tl_services
SET @tablename = "tl_services";
SET @columnname = "is_community_tourism";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) DEFAULT 0")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "festival_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add foreign key constraint if it doesn't exist
SET @constraint_name = 'fk_festival';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND CONSTRAINT_NAME = @constraint_name
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD CONSTRAINT ", @constraint_name, " FOREIGN KEY (festival_id) REFERENCES tl_festivals(festival_id) ON DELETE SET NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- -----------------------------------------------------
-- Add Community Tourism category if not exists
-- -----------------------------------------------------
INSERT INTO tl_service_categories (category_name, category_description, category_icon)
SELECT 'Community Tourism', 'Authentic village experiences, homestays, and community-based tourism supporting local communities', 'fa-home'
WHERE NOT EXISTS (SELECT 1 FROM tl_service_categories WHERE category_name = 'Community Tourism');

INSERT INTO tl_service_categories (category_name, category_description, category_icon)
SELECT 'Village Experiences', 'Immersive rural experiences including farming, fishing, crafts, and traditional village life', 'fa-leaf'
WHERE NOT EXISTS (SELECT 1 FROM tl_service_categories WHERE category_name = 'Village Experiences');

INSERT INTO tl_service_categories (category_name, category_description, category_icon)
SELECT 'Cultural Heritage', 'Historical sites, museums, traditional ceremonies, and cultural immersion experiences', 'fa-landmark'
WHERE NOT EXISTS (SELECT 1 FROM tl_service_categories WHERE category_name = 'Cultural Heritage');

-- -----------------------------------------------------
-- Add service_duration column to bookings if not exists
-- -----------------------------------------------------
SET @tablename = "tl_bookings";
SET @columnname = "service_duration";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT DEFAULT 1 AFTER number_of_people")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
