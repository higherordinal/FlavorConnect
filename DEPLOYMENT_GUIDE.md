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
| Root pages (index.php, about.php, 404.php) | `require_once('../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| API files | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Member pages | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Admin pages (admin/*.php) | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Admin subdirectories (admin/users/*.php, admin/categories/*.php) | `require_once('../../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Admin deeper subdirectories (admin/categories/type/*.php, admin/categories/diet/*.php, admin/categories/style/*.php, admin/categories/measurement/*.php) | `require_once('../../../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |
| Recipe pages | `require_once('../../private/core/initialize.php');` | `require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');` |

#### Configuration Constants in bluehost_config.php

The bluehost_config.php file already contains all necessary constants for the production environment:

```php
// Path Configuration - Already defined in bluehost_config.php
define('PROJECT_ROOT', '/home2/swbhdnmy/public_html/website_7135c1f5');
define('PUBLIC_PATH', PROJECT_ROOT . '/public');
define('PRIVATE_PATH', PROJECT_ROOT . '/private');
define('SHARED_PATH', PRIVATE_PATH . '/shared');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');

// URL Configuration - Already defined in bluehost_config.php
define('WWW_ROOT', isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] : '');

// Database Configuration - Already defined in bluehost_config.php
define('DB_HOST', $config['db_host']);
define('DB_USER', $config['db_user']);
define('DB_PASS', $config['db_pass']);
define('DB_NAME', $config['db_name']);
```

No changes are needed to these constants as they are already optimized for the Bluehost environment.

### Path Adjustment Strategy

1. **Search for all initialize.php includes**:
   ```
   grep -r "require.*initialize.php" --include="*.php" .
   ```

2. **Update each file** with the absolute path for the live server

## 2. API Endpoint Changes

### API Base URL

```php
// Local development (in private/core/config.php or api_config.php)
define('API_BASE_URL', 'http://localhost/FlavorConnect/api');

// Live server (Heroku)
define('API_BASE_URL', 'https://flavorconnect-api.herokuapp.com');
```

### Update API Configuration

The API configuration is  defined in:
- `/private/core/api_config.php` 

## 3. Configuration Files

### Database and Application Configuration

For Bluehost deployment, use the dedicated Bluehost configuration file instead of the development config:

1. The `bluehost_config.php` file already contains the correct settings for your Bluehost environment:

```php
// Bluehost-specific configurations from bluehost_config.php
$config = [
    // Default settings
    'development_mode' => false,
    'session_expiry' => 86400,  // 24 hours in seconds
    'max_file_size' => 10485760,  // 10MB in bytes
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'timezone' => 'America/New_York',
    
    // Bluehost database settings
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_user' => 'swbhdnmy_user',
    'db_pass' => '@Connect4establish',
    'db_name' => 'swbhdnmy_db_flavorconnect'
];

// Define database constants
define('DB_HOST', $config['db_host']);
define('DB_PORT', $config['db_port']);
define('DB_USER', $config['db_user']);
define('DB_PASS', $config['db_pass']);
define('DB_NAME', $config['db_name']);

// Error Reporting - Production settings
error_reporting(0);
ini_set('display_errors', '0');
```

2. For the production version, directly modify `initialize.php` to use the Bluehost configuration file:

```php
// In private/core/initialize.php

// Load the Bluehost configuration file
require_once(PRIVATE_PATH . '/config/bluehost_config.php');
```

This approach eliminates environment detection logic and simplifies the codebase for production. The development version will continue to use `config.php`, while the production version will use `bluehost_config.php` exclusively.

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

The repository contains Bluehost-specific .htaccess files that are optimized for the production environment. During deployment, you need to rename these files:

#### Main .htaccess (in public directory)

1. Locate the Bluehost-specific file: `public/.bluehost-htaccess`
2. **Delete** any existing `.htaccess` file in the public directory
3. Rename `.bluehost-htaccess` to `.htaccess` when deploying to Bluehost

The Bluehost-specific version includes:
- Absolute path references instead of relative paths
- Enhanced security headers for production
- Cache control for static assets
- No BASE_PATH detection (not needed in production)

```apache
# Bluehost-optimized .htaccess
RewriteEngine On

# Handle front controller pattern for all URLs that don't exist as files or directories
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ /router.php [L]

# Additional security headers for production
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header set Referrer-Policy "strict-origin-when-cross-origin"

# Cache control for static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    # Additional cache rules...
</IfModule>
```

#### API .htaccess (in public/api directory)

1. Locate the Bluehost-specific file: `public/api/.bluehost-htaccess`
2. **Delete** any existing `.htaccess` file in the public/api directory
3. Rename `.bluehost-htaccess` to `.htaccess` when deploying to Bluehost

The Bluehost-specific version includes:
- Updated RewriteBase for the production URL structure
- Additional security headers

```apache
# FlavorConnect API Configuration for Bluehost
RewriteEngine On
# Use absolute path for Bluehost
RewriteBase /api/

# Route all API requests to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]

# Additional security headers
Header set X-Content-Type-Options "nosniff"
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
- [ ] Verify the constants in bluehost_config.php are correct for your Bluehost environment
- [ ] Create/update api-config.js with Heroku endpoints
- [ ] Update member_header.php to include api-config.js
- [ ] Update recipe-favorite.js to use the centralized API configuration
- [ ] Verify `bluehost_config.php` contains the correct Bluehost database settings
- [ ] Modify `initialize.php` in the production version to directly load `bluehost_config.php` instead of `config.php`
- [ ] Set appropriate file permissions
- [ ] Configure error reporting for production
- [ ] Test all major functionality after deployment
- [ ] Verify all CSS and JS files are loading correctly
- [ ] Check for any hardcoded URLs and update them
- [ ] Verify CORS configuration on Heroku API
- [ ] Check PHP version and required extensions
- [ ] Delete existing `.htaccess` files and rename `.bluehost-htaccess` files to `.htaccess` in both the main public directory and the API directory
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
