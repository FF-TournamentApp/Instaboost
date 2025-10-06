# Use official PHP image with Apache
FROM php:8.2-apache

# Enable necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy all files into the web directory
COPY . /var/www/html/

# Expose Render port (Render automatically maps it)
EXPOSE 10000

# Start Apache
CMD ["apache2-foreground"]
