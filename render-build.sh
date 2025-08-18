#!/usr/bin/env bash
# Exit on error
set -o errexit

# Create directory for logs
mkdir -p storage/logs
chmod -R 777 storage/logs

# Create directory for framework cache
mkdir -p bootstrap/cache
chmod -R 777 bootstrap/cache

# Create directory for storage cache
mkdir -p storage/framework/{sessions,views,cache}
chmod -R 777 storage/framework

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Create storage symlink
php artisan storage:link

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Copy .env.example to .env if .env doesn't exist
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Generate key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Run migrations
php artisan migrate --force
