#!/bin/bash

# Update package lists
apt-get update

# Install ImageMagick and PHP extensions
apt-get install -y imagemagick libmagickwand-dev
pecl install imagick
docker-php-ext-enable imagick

# Install GD library as fallback
apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install -j$(nproc) gd

# Restart Apache
service apache2 restart

# Verify installation
echo "Checking ImageMagick installation:"
which convert
convert -version

echo "Checking PHP extensions:"
php -m | grep -E 'imagick|gd'

echo "Installation complete!"
