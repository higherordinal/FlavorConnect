FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install zip pdo pdo_mysql mysqli

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod alias

# Set the working directory
WORKDIR /var/www/html

# Copy the application files
COPY . /var/www/html/

# Copy Apache configuration
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Move users directory to public
RUN cp -r /var/www/html/private/users /var/www/html/public/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
