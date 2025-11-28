# Provider Sidebar Component Usage Guide

## Overview
The provider sidebar is now a reusable component located in `includes/provider_sidebar.php`. This eliminates code duplication across all provider dashboard pages.

## How to Use

### Step 1: Set Current Page Variable
Before including the sidebar, set the `$current_page` variable to highlight the active menu item:

```php
$current_page = 'dashboard'; // Options: dashboard, bookings, services, add_service, profile, premium, settings
```

### Step 2: Include the Sidebar
```php
include '../includes/provider_sidebar.php';
```

## Complete Example

```php
<?php
require_once '../settings/core.php';

// Check if user is provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'provider') {
    header('Location: ../login/login.php');
    exit();
}

// Load any page-specific data here
// ...

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Page - TourLink Provider</title>
    <!-- Include sidebar CSS -->
    <link href="../css/provider_dashboard.css" rel="stylesheet">
    <!-- Other CSS and meta tags -->
</head>
<body>
    <?php
    // Set current page for sidebar highlighting
    $current_page = 'services'; // Change this based on current page

    // Include reusable sidebar
    include '../includes/provider_sidebar.php';
    ?>

    <!-- Your page content here -->
    <main class="main-content">
        <h1>My Page Content</h1>
        <!-- ... -->
    </main>
</body>
</html>
```

## Available Page Values

| Page | `$current_page` value |
|------|---------------------|
| Provider Dashboard | `'dashboard'` |
| Manage Bookings | `'bookings'` |
| My Services | `'services'` |
| Add Service | `'add_service'` |
| Business Profile | `'profile'` |
| Premium Subscription | `'premium'` |
| Account Settings | `'settings'` |

## What the Sidebar Component Does Automatically

1. **Authentication Check**: Ensures user is logged in as provider
2. **Load Provider Data**: Fetches provider information if not already loaded
3. **Pending Bookings Count**: Shows badge with count of pending bookings
4. **Premium Status**: Shows "Active" badge if provider has premium subscription
5. **Highlight Active Page**: Adds 'active' class to current page menu item

## Data Variables Used by Sidebar

The sidebar component automatically loads these if they don't exist:

- `$provider` - Provider profile data
- `$pending_bookings` - Array of pending bookings
- `$has_premium` - Boolean indicating premium status

**Optional**: If you've already loaded these variables in your page, the sidebar will reuse them (more efficient).

## Path Considerations

### If your page is in `/admin/` folder:
```php
include '../includes/provider_sidebar.php';
```

### If your page is in `/view/provider/` folder:
```php
include '../../includes/provider_sidebar.php';
```

## Benefits of This Approach

✅ **DRY Principle**: Write once, use everywhere
✅ **Easy Maintenance**: Update sidebar in one place
✅ **Consistent UI**: All pages look the same
✅ **Dynamic Highlighting**: Automatically highlights active page
✅ **Automatic Data Loading**: Fetches required data if not present
✅ **Performance**: Reuses data if already loaded

## Migrating Existing Pages

To migrate an existing provider page:

1. Find the sidebar HTML in your page (from `<aside class="sidebar">` to `</aside>`)
2. Delete the entire sidebar HTML
3. Add these two lines where the sidebar was:
```php
<?php
$current_page = 'your_page_name'; // Set appropriate value
include '../includes/provider_sidebar.php'; // Adjust path if needed
?>
```
4. Done! Test the page to ensure sidebar appears correctly.

## Example: Converting manage_services.php

**Before:**
```php
<body>
    <aside class="sidebar">
        <!-- 70+ lines of sidebar HTML -->
    </aside>
    <main class="main-content">
        <!-- content -->
    </main>
</body>
```

**After:**
```php
<body>
    <?php
    $current_page = 'services';
    include '../includes/provider_sidebar.php';
    ?>
    <main class="main-content">
        <!-- content -->
    </main>
</body>
```

**Result:** 70+ lines reduced to 4 lines! ✨
