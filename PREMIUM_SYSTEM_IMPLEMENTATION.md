# Premium Subscription System - Implementation Guide

## ✅ Completed
1. SQL cleanup script updated (keeps tl_premium_listings & tl_subscription_payments)
2. Database migrations created and made idempotent:
   - `sql/drop_unused_tables.sql`
   - `sql/setup_premium_subscriptions.sql` ✅ IMPORTED
   - `sql/add_guest_details_to_bookings.sql` ✅ IMPORTED
3. Premium subscription dashboard created (`admin/premium_subscription.php`)
4. Payment system implemented:
   - `actions/initiate_premium_subscription.php`
   - `actions/premium_payment.php`
   - `actions/process_premium_payment.php`
   - `actions/cancel_premium_subscription.php`
5. Markdown files removed (DATABASE_CLEANUP.md, DARK_MODE_README.md)
6. ✅ Premium subscription link added to provider dashboard with "Active" badge
7. ✅ Featured services carousel implemented on index page with auto-rotation

## 🔧 Implementation Steps

### Step 1: Run SQL Migrations (REQUIRED)

**1.1 Clean up unused tables:**
```sql
-- In phpMyAdmin, import: sql/drop_unused_tables.sql
-- This removes 8 unused tables
```

**1.2 Setup premium subscription system:**
```sql
-- In phpMyAdmin, import: sql/setup_premium_subscriptions.sql
-- This adds required columns and indexes
```

**1.3 Add guest_details to bookings:**
```sql
-- In phpMyAdmin, import: sql/add_guest_details_to_bookings.sql
-- This adds booking guest details column
```

---

### Step 2: Add Provider Premium Subscription Link

**File: `admin/provider_dashboard.php`**

Add this button to the provider dashboard sidebar or main menu:

```php
<a href="premium_subscription.php" class="dashboard-link">
    <i class="fas fa-crown"></i>
    <span>Premium Subscription</span>
    <?php if ($has_premium): ?>
        <span class="badge-active">Active</span>
    <?php endif; ?>
</a>
```

---

### Step 3: Implement Featured Services Carousel on Index

**File: `index_tourlink.php`**

**3.1 Update the PHP at the top to get premium services:**
```php
// Around line 7, update to:
// Get premium services for carousel
$db_temp = new db_connection();
$db_temp->db_connect();
$premium_query = $db_temp->db->query("
    SELECT s.*, sp.business_name, sp.verification_status, sc.category_name,
           AVG(r.rating) as average_rating,
           COUNT(DISTINCT b.booking_id) as total_bookings
    FROM tl_services s
    JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
    JOIN tl_service_categories sc ON s.category_id = sc.category_id
    LEFT JOIN tl_reviews r ON s.service_id = r.service_id
    LEFT JOIN tl_bookings b ON s.service_id = b.service_id
    WHERE s.is_premium = 1
    AND s.is_active = 1
    AND sp.account_status = 'active'
    ORDER BY s.date_created DESC
    LIMIT 8
");
$premium_services = $premium_query->fetch_all(MYSQLI_ASSOC);
```

**3.2 Add carousel HTML after the hero section:**
```html
<!-- Premium Services Carousel -->
<?php if (!empty($premium_services)): ?>
<section class="premium-carousel-section" style="padding: 80px 0; background: #f8f9fa;">
    <div class="container">
        <div class="section-header" style="text-align: center; margin-bottom: 50px;">
            <span style="color: #d4a017; font-weight: 600; font-size: 14px; letter-spacing: 1px; text-transform: uppercase;">
                <i class="fas fa-crown"></i> Featured Services
            </span>
            <h2 style="font-size: 36px; font-weight: 700; margin-top: 10px;">Premium Experiences</h2>
            <p style="color: #666; font-size: 16px; max-width: 600px; margin: 10px auto 0;">
                Discover our top-rated and verified service providers
            </p>
        </div>

        <div class="premium-carousel-container" style="position: relative;">
            <div class="premium-carousel" id="premiumCarousel" style="display: flex; gap: 24px; overflow: hidden;">
                <?php foreach($premium_services as $index => $service):
                    $images = json_decode($service['service_images'], true);
                    $first_image = $images[0] ?? 'default.jpg';
                    $rating = round($service['average_rating'] ?? 0, 1);
                    $is_verified = $service['verification_status'] === 'verified';
                ?>
                <div class="premium-card" style="min-width: 350px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: transform 0.3s;">
                    <div style="position: relative;">
                        <img src="uploads/services/<?php echo htmlspecialchars($first_image); ?>"
                             alt="<?php echo htmlspecialchars($service['service_title']); ?>"
                             style="width: 100%; height: 250px; object-fit: cover;">
                        <div style="position: absolute; top: 12px; right: 12px; background: #d4a017; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <i class="fas fa-crown"></i> Premium
                        </div>
                        <?php if ($is_verified): ?>
                        <div style="position: absolute; top: 12px; left: 12px; background: #0f5132; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Verified
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">
                                <?php echo htmlspecialchars($service['category_name']); ?>
                            </span>
                        </div>
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px; line-height: 1.3;">
                            <?php echo htmlspecialchars($service['service_title']); ?>
                        </h3>
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                            <span style="color: #ffc107; font-size: 14px;">
                                <?php for($i = 0; $i < 5; $i++): ?>
                                    <?php if ($i < floor($rating)): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i < ceil($rating)): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </span>
                            <span style="font-size: 14px; color: #666;"><?php echo $rating; ?></span>
                            <span style="font-size: 14px; color: #999;">(<?php echo $service['total_bookings']; ?> bookings)</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e9ecef;">
                            <div>
                                <div style="font-size: 24px; font-weight: 700; color: #1b4332;">
                                    GH₵ <?php echo number_format($service['base_price'], 0); ?>
                                </div>
                                <div style="font-size: 12px; color: #999;">
                                    per <?php echo $service['pricing_unit'] === 'per_person' ? 'person' : ($service['pricing_unit'] === 'per_hour' ? 'hour' : 'day'); ?>
                                </div>
                            </div>
                            <a href="view/single_service.php?service_id=<?php echo $service['service_id']; ?>"
                               style="padding: 10px 20px; background: #1b4332; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Carousel Controls -->
            <button onclick="scrollCarousel(-1)" style="position: absolute; left: -20px; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; background: white; border: 2px solid #1b4332; border-radius: 50%; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                <i class="fas fa-chevron-left" style="color: #1b4332;"></i>
            </button>
            <button onclick="scrollCarousel(1)" style="position: absolute; right: -20px; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; background: white; border: 2px solid #1b4332; border-radius: 50%; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                <i class="fas fa-chevron-right" style="color: #1b4332;"></i>
            </button>
        </div>
    </div>
</section>

<script>
let currentScroll = 0;
const carousel = document.getElementById('premiumCarousel');
const cardWidth = 374; // 350px + 24px gap

function scrollCarousel(direction) {
    const maxScroll = carousel.scrollWidth - carousel.clientWidth;
    currentScroll += direction * cardWidth;

    if (currentScroll < 0) currentScroll = 0;
    if (currentScroll > maxScroll) currentScroll = maxScroll;

    carousel.style.transform = `translateX(-${currentScroll}px)`;
    carousel.style.transition = 'transform 0.5s ease';
}

// Auto-scroll every 5 seconds
setInterval(() => {
    scrollCarousel(1);
    // Reset to start when reaching the end
    if (currentScroll >= carousel.scrollWidth - carousel.clientWidth) {
        setTimeout(() => {
            carousel.style.transition = 'none';
            currentScroll = 0;
            carousel.style.transform = `translateX(0)`;
            setTimeout(() => {
                carousel.style.transition = 'transform 0.5s ease';
            }, 50);
        }, 500);
    }
}, 5000);
</script>
<?php endif; ?>
```

---

### Step 4: Add Verified Badge Throughout Platform

**4.1 In Service Cards (all_services.php, search results, etc.):**

Add this after provider/business name:
```html
<?php if ($service['verification_status'] === 'verified'): ?>
    <span style="display: inline-flex; align-items: center; gap: 4px; background: #d1e7dd; color: #0f5132; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
        <i class="fas fa-check-circle"></i> Verified
    </span>
<?php endif; ?>
```

**4.2 In Provider Names:**

```html
<span class="provider-name">
    <?php echo htmlspecialchars($provider_name); ?>
    <?php if ($verification_status === 'verified'): ?>
        <i class="fas fa-badge-check" style="color: #0f5132; margin-left: 4px;" title="Verified Provider"></i>
    <?php endif; ?>
</span>
```

**4.3 CSS for Verified Badge:**

```css
.verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #d1e7dd;
    color: #0f5132;
    padding: 4px 10px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
}

.verified-badge i {
    font-size: 13px;
}
```

---

### Step 5: Update Service Queries to Include Premium Status

**In controllers/service_controller.php:**

Update queries to include `is_premium` field:

```php
function get_all_services_ctr() {
    $service = new Service();
    return $service->get_all_services();
}

// In service_class.php, update query:
$sql = "SELECT s.*, sp.business_name, sp.verification_status,
        sc.category_name, s.is_premium,
        AVG(r.rating) as average_rating,
        COUNT(DISTINCT b.booking_id) as total_bookings
        FROM tl_services s
        JOIN tl_service_providers sp ON s.provider_id = sp.provider_id
        JOIN tl_service_categories sc ON s.category_id = sc.category_id
        LEFT JOIN tl_reviews r ON s.service_id = r.service_id
        LEFT JOIN tl_bookings b ON s.service_id = b.service_id
        WHERE s.is_active = 1
        GROUP BY s.service_id
        ORDER BY s.is_premium DESC, average_rating DESC";
```

---

### Step 6: Add Premium Badge to Search Results

**In view/all_services.php and search results:**

```html
<?php if ($service['is_premium']): ?>
    <div class="premium-badge" style="position: absolute; top: 10px; right: 10px; background: #d4a017; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
        <i class="fas fa-crown"></i> Premium
    </div>
<?php endif; ?>
```

---

## 🎯 Testing Checklist

- [ ] Run all 3 SQL migration files
- [ ] Test premium subscription page loads
- [ ] Test payment flow (subscribe → pay → activate)
- [ ] Verify premium services appear in carousel
- [ ] Test carousel auto-rotation (every 5 seconds)
- [ ] Test manual carousel navigation
- [ ] Verify verified badge appears on verified providers
- [ ] Test subscription cancellation
- [ ] Verify premium services appear at top of search
- [ ] Test that non-premium services still work normally

---

## 📝 Monthly Subscription Details

- **Price:** GH₵150/month
- **Auto-renewal:** Yes (can be cancelled anytime)
- **Benefits:**
  - ✨ Featured on homepage carousel
  - 🔍 Priority in all search results
  - 👑 Premium badge on all services
  - 📈 Performance tracking (views, bookings)
- **All provider's services** become premium (not per-service)
- **Continues until end of billing period** after cancellation

---

## 🔄 Auto-Renewal System (Future Enhancement)

To implement automated monthly renewals:

1. Create a cron job that runs daily:
   ```php
   // Check for upcoming renewals (3 days before)
   // Send reminder emails
   // Process automatic payments
   // Update subscription status
   ```

2. Add email notifications:
   - Payment successful
   - Payment failed
   - Subscription expiring soon
   - Subscription cancelled

---

## 💡 Provider Verification Process

To mark a provider as verified:

```sql
UPDATE tl_service_providers
SET verification_status = 'verified'
WHERE provider_id = [PROVIDER_ID];
```

Or via admin dashboard with verification workflow.

---

## 🎨 Customization Options

- Change carousel auto-scroll interval (currently 5 seconds)
- Adjust number of premium services shown (currently 8)
- Modify premium badge colors
- Change subscription price
- Add different subscription tiers

---

## Support

All files are created and ready. Follow the steps above to complete the implementation!
