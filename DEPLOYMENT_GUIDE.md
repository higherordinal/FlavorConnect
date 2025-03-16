# FlavorConnect Deployment Guide

This guide documents the necessary changes when deploying the FlavorConnect application from local development to a live server environment.

## 1. Path Adjustments

### Critical Path Changes

When deploying to the live server, ensure these paths are updated:

#### Initialize Path in index.php and All Entry Points

```php
// Local development path (relative)
require_once('../private/core/initialize.php');

// Live server path (absolute)
require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');
```

#### Specific Path Changes Required

| File Type | Local Path | Live Path |
|-----------|------------|-----------|
| Main index.php | `require_once('../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| API files | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Member pages | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Admin pages | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Recipe pages | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |

#### Path Constants in initialize.php

In `private/core/initialize.php`, update these path constants:

```php
// Local development
define("PRIVATE_PATH", dirname(__FILE__));
define("PROJECT_PATH", dirname(PRIVATE_PATH));
define("PUBLIC_PATH", PROJECT_PATH . '/public');
define("SHARED_PATH", PRIVATE_PATH . '/shared');

// Live server (absolute paths)
define("PRIVATE_PATH", '/home2/swbhdnmy/public_html/website_7135c1f5/private');
define("PROJECT_PATH", '/home2/swbhdnmy/public_html/website_7135c1f5');
define("PUBLIC_PATH", '/home2/swbhdnmy/public_html/website_7135c1f5');
define("SHARED_PATH", PRIVATE_PATH . '/shared');
```

#### URL Constants in initialize.php

```php
// Local development
define("WWW_ROOT", '/FlavorConnect/public');

// Live server
define("WWW_ROOT", '');
```

### Path Adjustment Strategy

1. **Search for all initialize.php includes**:
   ```
   grep -r "require.*initialize.php" --include="*.php" .
   ```

2. **Update each file** with the absolute path for the live server

3. **Check all path constants** in initialize.php

## 2. API Endpoint Changes

### API Base URL

```php
// Local development (in private/core/config.php or api_config.php)
define('API_BASE_URL', 'http://localhost/FlavorConnect/api');

// Live server (Heroku)
define('API_BASE_URL', 'https://flavorconnect-api.herokuapp.com');
```

### Update API Configuration

The API configuration is typically defined in:
- `/private/core/config.php`
- `/private/core/api_config.php` (if exists)

## 3. Database Configuration

Ensure the database configuration is updated for the live environment:

```php
// Local development (in private/core/database.php)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'flavorconnect');

// Live server
define('DB_HOST', 'your_live_db_host');
define('DB_USER', 'your_live_db_user');
define('DB_PASS', 'your_live_db_password');
define('DB_NAME', 'your_live_db_name');
```

## 4. File Permissions

After uploading files to the live server, ensure proper permissions:

```bash
# Set directories to 755
find /path/to/live/site -type d -exec chmod 755 {} \;

# Set files to 644
find /path/to/live/site -type f -exec chmod 644 {} \;

# Make specific scripts executable if needed
chmod +x /path/to/live/site/some_script.sh
```

## 5. Environment-Specific Configuration

### Error Reporting

```php
// Development: Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Production: Hide errors from users
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
// Log errors instead
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### Cache Busting

Ensure CSS and JS files use cache busting in production:

```php
// Example in header.php or footer.php
<link rel="stylesheet" href="<?php echo url_for('/assets/css/main.css?v=' . filemtime(SITE_ROOT . '/public/assets/css/main.css')); ?>">
```

## 6. Heroku API Integration

### Heroku API Endpoints

When deploying our local version to replace the live version, ensure these Heroku API endpoints are properly integrated:

```javascript
// Heroku API Base URL
const HEROKU_API_URL = 'https://flavorconnect-api.herokuapp.com';

// Key Endpoints to Preserve
const ENDPOINTS = {
  toggleFavorite: `${HEROKU_API_URL}/favorites/toggle`,
  getFavorites: `${HEROKU_API_URL}/favorites`,
  checkFavorite: `${HEROKU_API_URL}/favorites/check`
};
```

### JavaScript Integration Strategy

Our local environment uses PHP-based API endpoints, while the live environment uses Node.js endpoints on Heroku. The JavaScript is already designed to handle both environments:

1. **Maintain the Dual-Environment Support**:
   ```javascript
   // In recipe-favorites.js and other files
   if (window.FlavorConnect.favorites && typeof window.FlavorConnect.favorites.toggle === 'function') {
     // Use Heroku API through the favorites utility
     const result = await window.FlavorConnect.favorites.toggle(recipeId);
   } else {
     // Fallback to direct fetch for local development
     await this.directUnfavorite(recipeId, button);
   }
   ```

2. **Update the favorites.js Utility**:
   When deploying to live, ensure the favorites.js utility is configured to use the Heroku API endpoints:

   ```javascript
   // In favorites.js
   window.FlavorConnect.favorites = {
     apiBaseUrl: 'https://flavorconnect-api.herokuapp.com',
     
     toggle: async function(recipeId) {
       const response = await fetch(`${this.apiBaseUrl}/favorites/toggle`, {
         method: 'POST',
         headers: { 'Content-Type': 'application/json' },
         body: JSON.stringify({ recipe_id: recipeId }),
         credentials: 'include'
       });
       return await response.json();
     },
     
     // Other methods...
   };
   ```

3. **CORS Configuration**:
   Ensure the Heroku API has proper CORS configuration to accept requests from your live domain:

   ```javascript
   // In Heroku Node.js API
   app.use(cors({
     origin: 'https://your-live-domain.com',
     credentials: true
   }));
   ```

## 7. Deployment Checklist

- [ ] Update all initialize.php paths to absolute paths
- [ ] Update path constants in initialize.php
- [ ] Update URL constants in initialize.php
- [ ] Update API endpoint URLs to Heroku
- [ ] Update database configuration
- [ ] Set appropriate file permissions
- [ ] Configure error reporting for production
- [ ] Test all major functionality after deployment
- [ ] Verify all CSS and JS files are loading correctly
- [ ] Check for any hardcoded URLs and update them
- [ ] Ensure favorites.js utility is configured for Heroku API
- [ ] Verify CORS configuration on Heroku API

## 8. Troubleshooting Common Issues

### 500 Server Error

If you encounter a 500 server error, check:
1. PHP error logs (usually in `/home/username/logs/error_log`)
2. File permissions
3. Path issues in include/require statements
4. Database connection issues

### CSS/JS Not Loading

1. Check browser console for 404 errors
2. Verify paths in HTML source
3. Ensure cache busting parameters are working

### API Connection Issues

1. Verify API endpoints are correct
2. Check for CORS configuration
3. Test API endpoints directly

## 9. Reverting to Previous Version

If needed, you can revert to a previous version:

```bash
# Using Git (if available)
git checkout previous_commit_hash

# Manual backup restoration
cp -r /backup/flavorconnect/* /path/to/live/site/
