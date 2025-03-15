# FlavorConnect Deployment Guide

This guide documents the necessary changes when deploying the FlavorConnect application from local development to a live server environment.

## 1. Path Adjustments

### Critical Path Changes

When deploying to the live server, ensure these paths are updated:

#### Initialize Path in index.php

```php
// Local development path (relative)
require_once('../private/core/initialize.php');

// Live server path (absolute)
require_once('/home2/swbhdnmy/public_html/website_7135c1f5/private/core/initialize.php');
```

#### Other Critical Paths

Check and update paths in these files:
- All files in `/public` that include initialize.php
- All files in `/public/admin` that include initialize.php
- All files in `/public/member` that include initialize.php
- All API endpoint files that include initialize.php

### Path Adjustment Strategy

1. **Search for all initialize.php includes**:
   ```
   grep -r "require.*initialize.php" --include="*.php" .
   ```

2. **Update each file** with the absolute path for the live server

## 2. API Endpoint Changes

### API Base URL

```php
// Local development
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
// Local development
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

## 6. Deployment Checklist

- [ ] Update all initialize.php paths to absolute paths
- [ ] Update API endpoint URLs to Heroku
- [ ] Update database configuration
- [ ] Set appropriate file permissions
- [ ] Configure error reporting for production
- [ ] Test all major functionality after deployment
- [ ] Verify all CSS and JS files are loading correctly
- [ ] Check for any hardcoded URLs and update them

## 7. Troubleshooting Common Issues

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

## 8. Reverting to Previous Version

If needed, you can revert to a previous version:

```bash
# Using Git (if available)
git checkout previous_commit_hash

# Manual backup restoration
cp -r /backup/flavorconnect/* /path/to/live/site/
```
