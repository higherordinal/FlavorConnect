# FlavorConnect Image Processing Setup

This document provides instructions for setting up image processing in FlavorConnect on different environments. FlavorConnect uses two image processing libraries:

1. **ImageMagick with Imagick PHP extension**: Primary image processing library for advanced operations
2. **GD Library**: Fallback library for basic image processing when ImageMagick is not available

## Docker Environment

If you're running FlavorConnect in a Docker container, follow these steps to ensure image processing works correctly:

1. Install ImageMagick, Imagick PHP extension, and GD library in your Docker container:

```bash
# Run this inside your Docker container
apt-get update
apt-get install -y imagemagick libmagickwand-dev
pecl install imagick
docker-php-ext-enable imagick

# Install GD library as fallback
apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install -j$(nproc) gd
```

2. Alternatively, you can use the provided installation script:

```bash
# Copy the script to your Docker container
docker cp install-imagemagick-docker.sh your_container_name:/install-imagemagick-docker.sh

# Run the script inside the container
docker exec -it your_container_name bash /install-imagemagick-docker.sh
```

3. Verify the installation:

```bash
# Check ImageMagick
convert -version

# Check PHP extensions
php -m | grep -E 'imagick|gd'
```

## XAMPP Environment

If you're using XAMPP for local development:

1. Install ImageMagick and PHP extensions:

```bash
# For Windows
# Download and install ImageMagick from https://imagemagick.org/script/download.php
# Make sure to check "Install development headers and libraries for C and C++" during installation

# For XAMPP on Linux
sudo apt-get update
sudo apt-get install -y imagemagick php-imagick
sudo apt-get install -y php-gd
```

2. Enable the extensions in php.ini:
   - Open `php.ini` in your XAMPP installation directory
   - Ensure these lines are uncommented:
     ```
     extension=gd
     extension=imagick
     ```
   - Restart Apache

3. Verify the installation:
   - Create a phpinfo.php file with `<?php phpinfo(); ?>`
   - Open it in your browser and search for "gd" and "imagick"

## Bluehost Environment

On Bluehost, both ImageMagick and GD library should be pre-installed. You can verify this through the Bluehost cPanel:

1. Log in to your Bluehost cPanel
2. Go to "PHP Configuration" or "Select PHP Version"
3. Ensure that both "imagick" and "gd" extensions are enabled
4. If not enabled, select them and save your changes

### File Path Considerations

When deploying to Bluehost, you need to update any relative file paths to absolute paths, especially in API files:

```php
// Change this:
require_once('../private/initialize.php');

// To this:
require_once('/home/swbhdnmy/public_html/private/initialize.php');
```

Common files that need path updates:
- API endpoint files (e.g., `/api/toggle_favorite.php`)
- Custom AJAX handlers
- Any files that use relative paths to include core application files

This is necessary because Bluehost's server configuration may resolve relative paths differently than your local development environment. Using absolute paths ensures that all required files can be located correctly.

## How FlavorConnect Uses Image Processing

FlavorConnect uses these libraries for:

1. **Recipe Image Uploads**: Resizing, optimizing, and creating thumbnails
2. **Profile Pictures**: Cropping and resizing user profile images
3. **Gallery Images**: Processing multiple images for recipe galleries

The application will attempt to use ImageMagick first, and fall back to GD if ImageMagick is not available.

## Testing Image Processing

After setting up your environment, you can test the image processing functionality:

1. Navigate to `/test-docker-image.php` in your browser
2. Check the environment information to verify ImageMagick and GD are available
3. Upload a test image to verify the processing works correctly

## Fallback Mechanism

FlavorConnect includes a fallback mechanism for environments where neither ImageMagick nor GD is available. In such cases, the original image will be copied without processing, allowing the application to continue functioning.

## Troubleshooting

If you encounter issues with image processing:

1. Check the error messages in the test page
2. Verify that the upload directory exists and has proper permissions
3. Check if the required PHP extensions are loaded
4. Ensure ImageMagick is installed and accessible

For Docker-specific issues:

1. Make sure the container has internet access to install packages
2. Check if the container has sufficient disk space
3. Verify that the user running the web server has permission to execute ImageMagick commands

For Bluehost-specific issues:

1. Check if your hosting plan supports ImageMagick
2. Contact Bluehost support if you need assistance enabling ImageMagick
3. Verify that your .htaccess file doesn't restrict access to the required commands
