# Bluehost Deployment Fix

Based on our debugging, we've identified that the Bluehost server has a different directory structure than expected, causing the 500 error. This document outlines the steps to fix the issue.

## Current Bluehost Directory Structure

```
/home2/swbhdnmy/public_html/
├── private/             # Exists but empty or missing core subdirectories
└── website_7135c1f5/    # Public website directory
    ├── assets/
    ├── debug.php
    └── ... other public files
```

## Required Directory Structure

We need to ensure the private directory has all the necessary subdirectories and files:

```
/home2/swbhdnmy/public_html/
├── private/
│   ├── core/
│   │   ├── initialize.php
│   │   ├── bootstrap.php
│   │   └── ... other core files
│   ├── config/
│   │   ├── config.php
│   │   ├── api_config.php
│   │   └── ... other config files
│   ├── shared/
│   │   └── ... shared files
│   └── ... other private directories
└── website_7135c1f5/
    └── ... public files
```

## Fix Steps

1. **Create a deployment package**:
   - Create a ZIP file containing the entire `private` directory from your local environment
   - Make sure it includes all subdirectories and files

2. **Upload to Bluehost**:
   - Connect to Bluehost via FTP/SFTP
   - Navigate to `/home2/swbhdnmy/public_html/`
   - If a `private` directory exists, rename it to `private_backup`
   - Upload your `private` directory ZIP file
   - Extract the ZIP file to create the proper directory structure

3. **Test the fix**:
   - Access your website to see if the 500 error is resolved
   - If issues persist, check the error logs in cPanel

## Creating the Deployment Package

From your local environment:

1. Navigate to your FlavorConnect project root
2. Create a ZIP file of the private directory:
   ```
   zip -r private_deploy.zip private/
   ```
3. Upload this ZIP file to Bluehost
4. Extract it in the `/home2/swbhdnmy/public_html/` directory

## Additional Configuration

After deploying the private directory, you may need to:

1. Update database credentials in `private/config/config.php` if they differ on Bluehost
2. Check file permissions (typically 755 for directories, 644 for files)
3. Ensure the `.htaccess` file in the public directory is properly configured

## Verifying the Fix

After completing these steps, run the debug.php script again to verify that:
1. The private directory and its subdirectories exist
2. The initialize.php file can be loaded
3. The database connection works
4. URL generation functions properly

## Long-term Solution

To prevent this issue in the future:
1. Update your deployment process to include the private directory
2. Document the required directory structure in your DEPLOYMENT_GUIDE.md
3. Consider creating a deployment script that verifies the correct structure exists
