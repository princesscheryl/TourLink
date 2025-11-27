# Booking System Update - TourLink

## Summary of Changes

### 1. Success Stories Removed from Admin ✅
- Removed "Success Stories" from admin sidebar across all pages
- Deleted `admin/stories.php` file
- Success stories remain on the public index page for tourists to view

### 2. Enhanced Booking Flow ✅

#### New Booking Process:
1. **Tourist selects date/time on service page** → Clicks "Book Now"
2. **Redirects to comprehensive booking details page** (`book_service.php`)
3. **Tourist fills in all required details**:
   - Personal info (first name, last name, email, phone)
   - Booking details (who they're booking for, travelling for work)
   - Special requests (dietary restrictions, accessibility needs, etc.)
   - Estimated arrival time
4. **Proceeds directly to payment** → No provider approval needed before payment
5. **Completes payment** → Booking confirmed

#### Key Changes:
- **No provider approval before payment** - Bookings go directly to payment after submission
- **Comprehensive guest information** - Collects all necessary details upfront
- **Booking.com-style form** - Professional, user-friendly interface

### 3. Files Created/Modified

#### New Files:
1. `view/book_service.php` - Comprehensive booking details page
2. `actions/create_booking_with_payment.php` - Handles booking creation with full guest details
3. `sql/add_guest_details_to_bookings.sql` - Database migration for guest_details column

#### Modified Files:
1. `view/single_service.php` - Updated to redirect to new booking page
2. `classes/booking_class.php` - Added support for guest_details field
3. All admin pages - Removed Success Stories and Regions from sidebar

### 4. Database Changes Required

**IMPORTANT**: Run this SQL migration on your database:

```sql
-- File: sql/add_guest_details_to_bookings.sql
ALTER TABLE tl_bookings
ADD COLUMN guest_details TEXT NULL
AFTER special_requests;
```

This adds a `guest_details` column to store JSON data with:
- first_name
- last_name
- email
- phone
- booking_for (main_guest/someone_else)
- travelling_for_work (yes/no)
- arrival_time

### 5. Booking Form Fields

The new booking page includes:

**Personal Details:**
- First name *
- Last name *
- Email address * (confirmation sent here)
- Phone number *

**Booking Details:**
- Who are you booking for? (optional)
  - I am the main guest
  - Booking is for someone else
- Are you travelling for work? (optional)
  - Yes / No

**Special Requests:**
- Text area for special requests (optional)
- Examples: dietary restrictions, accessibility needs, language preferences

**Arrival Time:**
- Information about scheduled service time
- Optional arrival time selection
- Time zone indicator (Ghana time)

### 6. Payment Flow

```
Service Page → Select Date/Time → Book Now Button
    ↓
Booking Details Page (book_service.php)
    ↓ (Fill personal info, booking details, special requests)
    ↓
Create Booking (status: pending, payment_status: pending)
    ↓
Payment Page (booking_payment.php?booking_id=X)
    ↓
Complete Payment
    ↓
Booking Confirmed (payment_status: escrow, booking_status: confirmed)
```

### 7. Provider Workflow Change

**OLD FLOW:**
1. Tourist creates booking → booking_status: pending
2. Provider reviews and approves
3. Tourist gets notification to pay
4. Tourist pays
5. Booking confirmed

**NEW FLOW:**
1. Tourist creates booking → booking_status: pending, payment_status: pending
2. Tourist immediately proceeds to payment
3. Tourist pays → payment_status: escrow, booking_status: confirmed
4. Provider receives confirmed booking notification
5. Service delivery

### 8. Testing Checklist

- [ ] Run SQL migration to add guest_details column
- [ ] Test booking flow from service page
- [ ] Verify all form fields are captured
- [ ] Test payment integration
- [ ] Verify booking appears in provider dashboard
- [ ] Test email notifications
- [ ] Check mobile responsiveness of new booking page

### 9. Next Steps

1. **Run the SQL migration** on your production database
2. **Clear PHP opcache** (if needed):
   ```bash
   # Visit: http://169.239.251.102:442/~princess.donkor/tourlink/clear_cache.php
   ```
3. **Push changes to repo** when ready
4. **Test the booking flow** end-to-end

## Questions About Payment On-Site

You asked: "should they be able to pay on-site?"

**Current Implementation**: All payments are processed online through the platform (MTN MoMo, Telecel Cash).

**If you want to add "Pay on-site" option:**
1. Add payment method selection on payment page
2. Add "pay_on_site" option
3. Skip online payment if selected
4. Booking status goes to "confirmed_pending_payment"
5. Provider collects cash on service date
6. Provider marks as paid in their dashboard

Would you like me to implement the "pay on-site" option as well?

## Files Summary

### View Files:
- `view/book_service.php` (NEW) - Comprehensive booking page
- `view/single_service.php` (MODIFIED) - Redirects to new booking page
- `view/booking_payment.php` (EXISTING) - Payment page

### Action Files:
- `actions/create_booking_with_payment.php` (NEW) - Creates booking with full details
- `actions/create_booking_action.php` (LEGACY) - Old booking creation (deprecated)

### Class Files:
- `classes/booking_class.php` (MODIFIED) - Added guest_details support

### SQL Files:
- `sql/add_guest_details_to_bookings.sql` (NEW) - Database migration

## Support

If you encounter any issues or need modifications, please let me know!
