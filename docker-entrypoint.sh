#!/bin/bash

# Wait for database to be ready
echo "Waiting for database..."
while ! mysqladmin ping -h"db" -u"laravel" -p"secret" --silent; do
    sleep 1
done

echo "Database is ready!"

# Run Laravel setup commands
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate

# Start PHP-FPM
php-fpm
