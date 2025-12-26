#!/bin/bash
set -e

echo "=================================="
echo "Starting deployment..."
echo "=================================="

APP_DIR="/var/www/html"
cd $APP_DIR

# Enable maintenance mode
echo "Enabling maintenance mode..."
php artisan down --retry=60

# Pull latest code
echo "Pulling latest code..."
git pull origin main

# Install dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and rebuild caches
echo "Clearing and rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache 2>/dev/null || true

# Restart Horizon
echo "Restarting Horizon..."
php artisan horizon:terminate

# Restart Reverb
echo "Restarting Reverb..."
supervisorctl restart laravel-reverb 2>/dev/null || true

# Restart queue workers
echo "Restarting queue workers..."
supervisorctl restart laravel-worker:* 2>/dev/null || true

# Disable maintenance mode
echo "Disabling maintenance mode..."
php artisan up

echo "=================================="
echo "Deployment completed successfully!"
echo "=================================="
