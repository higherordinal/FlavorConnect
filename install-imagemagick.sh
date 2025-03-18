#!/bin/bash

# Update package lists
apt-get update

# Install ImageMagick and PHP extensions
apt-get install -y imagemagick php-imagick

# Install GD library as fallback
apt-get install -y php-gd

# Restart PHP-FPM (adjust service name if needed)
if [ -f /etc/init.d/php-fpm ]; then
    /etc/init.d/php-fpm restart
elif [ -f /etc/init.d/php8.1-fpm ]; then
    /etc/init.d/php8.1-fpm restart
fi

# Verify installation
echo "Checking ImageMagick installation:"
which convert
convert -version

echo "Checking PHP extensions:"
php -m | grep -E 'imagick|gd'

echo "Installation complete!"
