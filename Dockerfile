# Use official PHP image with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Optional: install mysqli if your PHP project uses MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy your project files to Apache's web root
COPY . /var/www/html/

# Set file permissions (optional, but helps with uploads)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (default Apache port)
EXPOSE 80
