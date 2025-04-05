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

### Heroku API Configuration

To integrate the existing Heroku API with our simplified local favorites functionality, create or update the following file:

#### Create public/assets/js/utils/api-config.js

```javascript
/**
 * FlavorConnect API Configuration
 * This file configures the API endpoints based on the environment
 */

window.FlavorConnect = window.FlavorConnect || {};

// Determine if we're in production or development
window.FlavorConnect.isProduction = window.location.hostname !== 'localhost' && 
                                   !window.location.hostname.includes('127.0.0.1');

// Set the API base URL based on environment
window.FlavorConnect.apiBaseUrl = window.FlavorConnect.isProduction 
    ? 'https://flavorconnect-api.herokuapp.com'  // Production (Heroku)
    : '/api';                                    // Local development

// Configure API endpoints
window.FlavorConnect.apiEndpoints = {
    // Favorites endpoints
    favorites: {
        toggle: `${window.FlavorConnect.apiBaseUrl}${window.FlavorConnect.isProduction ? '/favorites/toggle' : '/toggle_favorite.php'}`,
        check: `${window.FlavorConnect.apiBaseUrl}${window.FlavorConnect.isProduction ? '/favorites/check' : '/toggle_favorite.php'}`,
        getAll: `${window.FlavorConnect.apiBaseUrl}${window.FlavorConnect.isProduction ? '/favorites' : '/get_favorites.php'}`
    },
    // Add other API endpoints here as needed
};

// Global API utility functions
window.toggleFavorite = async function(recipeId) {
    try {
        const endpoint = window.FlavorConnect.apiEndpoints.favorites.toggle;
        const method = 'POST';
        const headers = {
            'Content-Type': 'application/json'
        };
        const body = JSON.stringify({ recipe_id: recipeId });
        
        const response = await fetch(endpoint, {
            method: method,
            headers: headers,
            body: body,
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error toggling favorite:', error);
        return { success: false, error: error.message };
    }
};

// Add other global API functions as needed
```

#### Update private/shared/member_header.php

Add the API configuration script before other JavaScript files:

```php
<!-- API Configuration -->
<script src="<?php echo url_for('/assets/js/utils/api-config.js'); ?>?v=<?php echo time(); ?>" defer></script>

<!-- Other scripts -->
<script src="<?php echo url_for('/assets/js/components/recipe-favorite.js'); ?>?v=<?php echo time(); ?>" defer></script>
```

### Updating Favorites Functionality

#### Update public/assets/js/components/recipe-favorite.js

Modify the recipe-favorite.js utility to use the centralized API configuration:

```javascript
/**
 * FlavorConnect Favorites Utility
 * Provides functions for managing recipe favorites
 */

// Initialize FlavorConnect namespace
window.FlavorConnect = window.FlavorConnect || {};

// Favorites utility functions
window.FlavorConnect.favorites = {
    /**
     * Toggle the favorite status of a recipe
     * @param {number} recipeId - The ID of the recipe to toggle
     * @returns {Promise<Object>} - Object with success and isFavorited properties
     */
    toggle: async function(recipeId) {
        try {
            // Use the configured endpoint from api-config.js
            const endpoint = window.FlavorConnect.apiEndpoints.favorites.toggle;
            const method = 'POST';
            const headers = {
                'Content-Type': 'application/json'
            };
            const body = JSON.stringify({ recipe_id: recipeId });
            
            const response = await fetch(endpoint, {
                method: method,
                headers: headers,
                body: body,
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error toggling favorite:', error);
            return { success: false, error: error.message };
        }
    },
    
    // Other methods...
};
```

### CORS Configuration for Heroku API

Ensure the Heroku API has proper CORS configuration to accept requests from your live domain:

```javascript
// In Heroku Node.js API (app.js or server.js)
const cors = require('cors');

app.use(cors({
  origin: ['https://your-live-domain.com', 'http://localhost:8080'],
  credentials: true,
  methods: ['GET', 'POST', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization']
}));
```

## 7. Additional Server Configuration

### .htaccess Files

Ensure that all .htaccess files are properly configured for the live environment:

#### Main .htaccess (in public directory)

```apache
# Enable URL rewriting
RewriteEngine On

# Base directory for rewrites
RewriteBase /

# Redirect to HTTPS if not already
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Don't rewrite if the file or directory exists
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Rewrite all other URLs to index.php
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

#### API .htaccess (in public/api directory)

```apache
# Enable CORS
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"

# Handle OPTIONS requests
RewriteEngine On
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

# Rewrite API requests
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?endpoint=$1 [QSA,L]
```

### PHP Version and Extensions

Ensure the live server has the correct PHP version and required extensions:

```php
// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('PHP version 7.4 or higher is required. Current version: ' . PHP_VERSION);
}

// Check required extensions
$required_extensions = ['mysqli', 'json', 'mbstring', 'gd'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        die("Required PHP extension not loaded: $ext");
    }
}
```

## 8. Deployment Checklist

- [ ] Update all initialize.php paths to absolute paths
- [ ] Update path constants in initialize.php
- [ ] Update URL constants in initialize.php
- [ ] Create/update api-config.js with Heroku endpoints
- [ ] Update member_header.php to include api-config.js
- [ ] Update recipe-favorite.js to use the centralized API configuration
- [ ] Update database configuration
- [ ] Set appropriate file permissions
- [ ] Configure error reporting for production
- [ ] Test all major functionality after deployment
- [ ] Verify all CSS and JS files are loading correctly
- [ ] Check for any hardcoded URLs and update them
- [ ] Verify CORS configuration on Heroku API
- [ ] Check PHP version and required extensions
- [ ] Validate .htaccess configurations

## 9. Troubleshooting Common Issues

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

### Database Connection Issues

1. Verify database credentials
2. Check database server availability
3. Ensure database user has proper permissions

### Session Issues

1. Check session configuration in php.ini
2. Verify session directory permissions
3. Test session functionality with a simple script

## 10. Reverting to Previous Version

If needed, you can revert to a previous version:

```bash
# Using Git (if available)
git checkout previous_commit_hash

# Manual backup restoration
cp -r /backup/flavorconnect/* /path/to/live/site/
