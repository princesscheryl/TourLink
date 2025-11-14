# TourLink Dark Mode Feature

## Overview
TourLink now supports dark mode across all pages with automatic theme persistence and smooth transitions.

## Features
- ✅ Toggle between light and dark themes
- ✅ Saves user preference in localStorage
- ✅ Respects system color scheme preference
- ✅ Smooth CSS transitions
- ✅ Consistent theming across all pages
- ✅ Toggle accessible from profile dropdown menu

## Files Added

### CSS
- `/css/dark-mode.css` - CSS variables and dark theme styles

### JavaScript
- `/js/dark-mode.js` - Theme management and persistence logic

## Pages Updated
All major pages now include dark mode support:
- ✅ `index_tourlink.php` - Homepage
- ✅ `view/profile_settings.php` - Profile settings
- ✅ `view/single_service.php` - Service details
- ✅ `admin/bookings.php` - Bookings page
- ✅ `admin/provider_profile.php` - Provider profile edit

## How It Works

### 1. CSS Variables
The system uses CSS custom properties for colors:
```css
:root {
    --bg-primary: #ffffff;
    --text-primary: #1a1a1a;
    /* ... */
}

[data-theme="dark"] {
    --bg-primary: #1a1a1a;
    --text-primary: #e0e0e0;
    /* ... */
}
```

### 2. Theme Persistence
User preference is saved in localStorage:
- Key: `tourlink-theme`
- Values: `'light'` or `'dark'`

### 3. System Preference Detection
Automatically detects user's OS theme preference if no saved preference exists.

### 4. Toggle Function
Global function `toggleTheme()` available on all pages to switch themes.

## Usage

### For Users
1. Click on profile icon in navigation
2. Select "Dark Mode" from dropdown
3. Theme switches and preference is saved
4. Icon changes to sun/moon accordingly

### For Developers

#### Adding Dark Mode to New Pages
1. Include CSS in `<head>`:
   ```html
   <link href="path/to/css/dark-mode.css" rel="stylesheet">
   ```

2. Include JS before other scripts:
   ```html
   <script src="path/to/js/dark-mode.js"></script>
   ```

3. Use CSS variables for colors:
   ```css
   .my-element {
       background-color: var(--bg-primary);
       color: var(--text-primary);
   }
   ```

#### Adding Toggle Button
```html
<button onclick="toggleTheme()" id="themeToggle">
    <i class="fa fa-moon"></i>
    <span class="toggle-text">Dark Mode</span>
</button>
```

## Color Palette

### Light Mode
- Background Primary: `#ffffff`
- Background Secondary: `#f8f9fa`
- Text Primary: `#1a1a1a`
- Text Secondary: `#666666`

### Dark Mode
- Background Primary: `#1a1a1a`
- Background Secondary: `#2d2d2d`
- Text Primary: `#e0e0e0`
- Text Secondary: `#b0b0b0`

### Brand Colors (Consistent)
- Primary: `#2d6a4f` (Green)
- Dark: `#1b4332`
- Accent: `#ffd700` (Gold)
- Danger: `#dc3545` (Red)

## Browser Support
- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers

## Notes
- Theme preference persists across browser sessions
- Smooth 0.3s transitions between themes
- No page reload required when switching themes
- Images slightly dimmed in dark mode (90% opacity) for better contrast
