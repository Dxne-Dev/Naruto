#!/bin/sh
set -e

# Run production cache & optimizations
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations safely
php artisan migrate --force || true

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
exec nginx -g "daemon off;"
