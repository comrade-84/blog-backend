#!/usr/bin/env bash
# Exit on error
set -e

# Install PHP dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Set up storage directory
php artisan storage:link

# Clear caches
php artisan cache:clear
php artisan config:clear

# Run database migrations
php artisan migrate --force

# Generate key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi
