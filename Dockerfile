FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install redis extension
#RUN pecl install redis && docker-php-ext-enable redis

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy apache config
COPY docker/apache/default.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel app source code
COPY . /var/www/html


# Install PHP dependencies
RUN composer install --optimize-autoloader


# Set permissions and link storage
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/public
#    && php artisan storage:link || true

# Expose port 80
EXPOSE 80

# Development command (you may prefer this outside container)
CMD ["apache2-foreground"]
