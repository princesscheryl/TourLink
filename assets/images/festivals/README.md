# Ghana Festivals Images Directory

This directory contains images for featured Ghanaian festivals displayed on the festivals page.

**Location:** `/assets/images/festivals/`

## Current Setup

The festivals are currently using **Unsplash placeholder images** via URLs stored in the database. To use local images instead:

1. Download or capture festival images
2. Place them in this directory
3. Update the database `image_url` field to point to local paths

## Featured Festivals (Displayed on festivals.php)

### 1. **Akwasidae Festival** (Kumasi, Ashanti)
- **Description:** Celebrated every six weeks by the Ashanti, honors ancestors and the Golden Stool
- **Suggested image:** Traditional durbar at Manhyia Palace, Golden Stool ceremony, Ashanti chiefs in regalia
- **Local path:** `assets/images/festivals/akwasidae-festival.jpg`

### 2. **Aboakyir Festival** (Winneba, Central)
- **Description:** Deer hunting festival by the Effutu people
- **Suggested image:** Deer hunt procession, traditional warriors, colorful celebration
- **Local path:** `assets/images/festivals/aboakyir-festival.jpg`

### 3. **Panafest** (Cape Coast, Central)
- **Description:** Pan-African Historical Theatre Festival
- **Suggested image:** Cultural performances, theatre, pilgrimages to historic sites
- **Local path:** `assets/images/festivals/panafest.jpg`

### 4. **Homowo Festival** (Accra, Greater Accra)
- **Description:** Ga people's harvest festival with kpokpoi sprinkling
- **Suggested image:** Traditional Ga dancers, kpokpoi ceremony, colorful procession
- **Local path:** `assets/images/festivals/homowo-festival.jpg`

### 5. **Damba Festival** (Tamale, Northern)
- **Description:** Celebrates the birth of Prophet Mohammed (Dagomba, Mamprusi)
- **Suggested image:** Horsemen, drummers, traditional northern Ghana attire
- **Local path:** `assets/images/festivals/damba-festival.jpg`

### 6. **Chale Wote Street Art Festival** (Jamestown, Accra)
- **Description:** West Africa's largest street art festival
- **Suggested image:** Street murals, artists painting, colorful urban art
- **Local path:** `assets/images/festivals/chale-wote-festival.jpg`

## Image Specifications

- **Format:** JPG, PNG, or WebP
- **Recommended size:** 800x600px minimum (4:3 ratio)
- **Aspect ratio:** 4:3 or 16:9 works best for festival cards
- **File size:** Under 500KB (optimized for web)
- **Quality:** High resolution, vibrant colors, culturally authentic

## Updating the Database

### Option 1: Use Unsplash URLs (Current Setup)
Run the SQL migration:
```bash
mysql -u root -p ecommerce_2025A_princess_donkor < /path/to/tourlink/sql/update_festival_images.sql
```

### Option 2: Use Local Images
After placing images in this directory, update the database:
```sql
UPDATE tl_festivals SET image_url = 'assets/images/festivals/akwasidae-festival.jpg' WHERE festival_name = 'Akwasidae Festival';
UPDATE tl_festivals SET image_url = 'assets/images/festivals/aboakyir-festival.jpg' WHERE festival_name = 'Aboakyir Festival';
UPDATE tl_festivals SET image_url = 'assets/images/festivals/panafest.jpg' WHERE festival_name = 'Panafest';
-- ... and so on for other festivals
```

## Image Sources

Use royalty-free images from:
- Ghana Tourism Authority official website
- Unsplash (search "Ghana festival", "African festival")
- Pexels (search "Ghana culture")
- Your own festival photography
- Creative Commons licensed festival photos

**Important:** Ensure you have rights to use all images. Credit photographers where required.

## Fallback Behavior

If an image URL is not set or fails to load, the festival card displays a drum icon with a gradient background as a fallback.
