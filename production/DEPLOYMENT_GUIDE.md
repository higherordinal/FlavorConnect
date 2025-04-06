# FlavorConnect Deployment Guide

This guide documents the process for deploying the FlavorConnect application from local development to the Bluehost production environment.

## 1. Deployment Configuration Notes

### Important: Custom Add-on Domain Configuration for Bluehost

This deployment guide and the accompanying scripts are specifically configured for a **Bluehost add-on domain setup** where FlavorConnect is hosted as an additional domain within an existing Bluehost account. This is a custom configuration specific to the developer's hosting arrangement and not a standard deployment pattern.

In this specific setup, the application is placed in a subfolder structure:

```
/home2/swbhdnmy/public_html/website_7135c1f5/
```

If you are deploying FlavorConnect as a primary domain or on a different hosting provider, you will need to adjust the paths accordingly. In a standard single-domain hosting setup, your paths would likely be simpler, such as:

```
/home/username/public_html/
```

To adapt this deployment process for your specific hosting environment:

1. Modify the `PROJECT_ROOT` constant in `bluehost_config.php` to match your server's directory structure
2. Update all absolute paths in the deployment script to reflect your hosting environment
3. Adjust the WWW_ROOT constant to use your domain name

The core deployment strategy (replacing configuration files, updating the url_for function, etc.) remains valid regardless of hosting setup, but the specific paths will need to be customized to your environment.

**Note for other developers:** This deployment script is primarily designed to make deployment easy for the original developer with their specific Bluehost configuration. If you're deploying FlavorConnect to your own hosting environment, you'll need to adapt these scripts and paths to match your specific hosting setup.

## 2. Automated Deployment Process

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

### Comprehensive Deployment Script

A comprehensive deployment script is provided to automatically prepare the FlavorConnect application for Bluehost:

1. **Run the deployment script**:
   ```bash
   # On Windows
   cd production
   deploy_bluehost.bat
   
   # On Linux/Mac
   cd production
   chmod +x deploy_bluehost.sh
   ./deploy_bluehost.sh
   ```

2. **What the deployment script does**:
   - Scans all PHP files in the public directory recursively
   - Updates all relative paths to use absolute paths for the Bluehost environment
   - Fixes directory structure issues (handles the fact that 'public' is the document root)
   - Replaces key files with Bluehost-optimized versions:
     - RecipeImageProcessor.class.php
     - Recipe.class.php
     - .htaccess files
   - Updates configuration files for the production environment:
     - Replaces config.php with bluehost_config.php
     - Replaces api_config.php with bluehost_api_config.php
   - Updates the url_for() function in core_utilities.php to use WWW_ROOT in production mode
     - Updates initialize.php comments for clarity
   - Creates backups of all modified files
   - Provides a summary of changes made

### Manual Deployment (Not Recommended)

The automated deployment script is strongly recommended as it handles all necessary changes in a consistent manner. Manual deployment is complex and error-prone, requiring updates to numerous files and configurations.

## 3. API Endpoint Changes

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

## 4. Configuration Files

### Direct Configuration Approach

The deployment script implements a direct configuration approach for Bluehost:

1. **Configuration Replacements**:
   - `config.php` is replaced with `bluehost_config.php`
   - `api_config.php` is replaced with `bluehost_api_config.php`

2. **Key Configuration Settings**:

```php
// Database Configuration
$config = [
    // Bluehost database settings
    'db_host' => 'localhost',
    'db_port' => 3306,
    'db_user' => 'swbhdnmy_user',
    'db_pass' => '@Connect4establish',
    'db_name' => 'swbhdnmy_db_flavorconnect'
];

// Path Configuration
define('PROJECT_ROOT', '/home2/swbhdnmy/public_html/website_7135c1f5');
define('PUBLIC_PATH', PROJECT_ROOT);
define('PRIVATE_PATH', PROJECT_ROOT . '/private');

// URL Configuration
define('WWW_ROOT', 'https://flavorconnect.space');

// Environment Setting
define('ENVIRONMENT', 'production');
```

3. **Benefits of Direct Configuration**:
   - No environment detection logic needed
   - Simplified codebase for production
   - Reduced risk of configuration errors
   - Consistent behavior across the application

## 5. File Permissions

After uploading files to the live server, ensure proper permissions:

```bash
# Set directories to 755
find /path/to/live/site -type d -exec chmod 755 {} \;

# Set files to 644
find /path/to/live/site -type f -exec chmod 644 {} \;

# Make specific scripts executable if needed
chmod +x /path/to/live/site/some_script.sh
```

## 6. Image Upload and Display

### Image Handling in Production

The deployment script addresses image upload and display issues on Bluehost:

1. **RecipeImageProcessor Class**:
   - The deployment script replaces the standard RecipeImageProcessor class with a Bluehost-optimized version
   - This version handles the Bluehost directory structure correctly
   - Ensures uploaded images are stored in the correct location

2. **Recipe Class**:
   - The deployment script replaces the standard Recipe class with a Bluehost-optimized version
   - The optimized version skips file existence checks in the production environment
   - Ensures image paths are always returned correctly

3. **Image Path Resolution**:
   - The `url_for()` function is updated to use the WWW_ROOT constant for generating absolute URLs in production mode
   - The updated function includes a specific condition for the production environment:
   ```php
   // For production environment (Bluehost)
   if (ENVIRONMENT === 'production' && defined('WWW_ROOT')) {
     return WWW_ROOT . $script_path;
   }
   ```
   - This ensures that image paths are correctly formed in the production environment using the domain specified in WWW_ROOT

### Troubleshooting Image Issues

If you encounter image upload or display issues after deployment:

1. Check the error logs in `private/logs/`
2. Verify that the image directories exist and have the correct permissions
3. Confirm that the WWW_ROOT constant is set correctly in the configuration
4. Test the image upload functionality using the diagnostic script: `upload_test.php`

## 7. Heroku API Integration

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

## 8. Additional Server Configuration

### .htaccess Files

The repository contains Bluehost-specific .htaccess files in the `production` folder that are optimized for the production environment. During deployment, you need to copy and rename these files:

#### Main .htaccess (in public directory)

1. Locate the Bluehost-specific file: `production/.bluehost-main-htaccess`
2. **Delete** any existing `.htaccess` file in the public directory
3. Copy `.bluehost-main-htaccess` to the public directory and rename it to `.htaccess`

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

1. Locate the Bluehost-specific file: `production/.bluehost-api-htaccess`
2. **Delete** any existing `.htaccess` file in the public/api directory
3. Copy `.bluehost-api-htaccess` to the public/api directory and rename it to `.htaccess`

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

## 9. Deployment Checklist

- [ ] Run the comprehensive deployment script (`deploy_bluehost.php`) which handles:
  - [ ] Updating all relative paths to absolute paths
  - [ ] Replacing config files with Bluehost-optimized versions
  - [ ] Updating the url_for() function to use WWW_ROOT in production mode
  - [ ] Replacing RecipeImageProcessor and Recipe classes with Bluehost-optimized versions
  - [ ] Updating .htaccess files
- [ ] Verify the constants in bluehost_config.php are correct for your Bluehost environment
- [ ] Create/update api-config.js with Heroku endpoints
- [ ] Update member_header.php to include api-config.js
- [ ] Update recipe-favorite.js to use the centralized API configuration
- [ ] Copy `production/RecipeImageProcessor.live.class.php` to private/classes directory
- [ ] Set appropriate file permissions
- [ ] Configure error reporting for production
- [ ] Test all major functionality after deployment
- [ ] Verify all CSS and JS files are loading correctly
- [ ] Check for any hardcoded URLs and update them
- [ ] Verify CORS configuration on Heroku API
- [ ] Check PHP version and required extensions
- [ ] Copy `.htaccess` files from production folder to their respective directories:
  - [ ] Copy `production/.bluehost-main-htaccess` to public/.htaccess
  - [ ] Copy `production/.bluehost-api-htaccess` to public/api/.htaccess
- [ ] Validate .htaccess configurations

## 10. Troubleshooting Common Issues

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

## 11. Reverting to Previous Version

If needed, you can revert to a previous version:

```bash
# Using Git (if available)
git checkout previous_commit_hash

# Manual backup restoration
cp -r /backup/flavorconnect/* /path/to/live/site/
