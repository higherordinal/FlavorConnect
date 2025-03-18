# FlavorConnect Image Processing Setup

This document provides instructions for setting up image processing in FlavorConnect on different environments.

## Docker Environment

If you're running FlavorConnect in a Docker container, follow these steps to ensure image processing works correctly:

1. Install ImageMagick and required PHP extensions in your Docker container:

```bash
# Run this inside your Docker container
apt-get update
apt-get install -y imagemagick php-imagick
apt-get install -y php-gd
```

2. Alternatively, you can use the provided installation script:

```bash
# Copy the script to your Docker container
docker cp install-imagemagick.sh your_container_name:/install-imagemagick.sh

# Run the script inside the container
docker exec -it your_container_name bash /install-imagemagick.sh
```

3. Verify the installation:

```bash
# Check if ImageMagick is installed
docker exec -it your_container_name convert -version

# Check if PHP extensions are loaded
docker exec -it your_container_name php -m | grep -E 'imagick|gd'
```

4. Ensure the upload directory has proper permissions:

```bash
# Set proper permissions for the upload directory
docker exec -it your_container_name chmod -R 755 /var/www/html/public/assets/uploads
```

## Bluehost Environment

If you're hosting FlavorConnect on Bluehost, follow these steps:

1. Check if ImageMagick is already installed (it usually is on Bluehost):

```bash
/usr/bin/convert -version
```

2. If ImageMagick is not available, contact Bluehost support to enable it for your account.

3. Ensure the PHP extensions are loaded:

```php
<?php
// Create a phpinfo.php file with this content
phpinfo();
?>
```

Upload this file to your server and check if the GD and Imagick extensions are loaded.

4. Set proper permissions for the upload directory:

```bash
chmod -R 755 public_html/public/assets/uploads
```

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
