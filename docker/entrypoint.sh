#!/bin/sh
set -e

# Ensure storage symlink exists
php artisan storage:link || true

# Run production cache & optimizations
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run database migrations safely
php artisan migrate --force || true

# Run purge on startup to ensure production database is completely clean
php artisan users:purge-non-admin || true

# Start PHP-FPM in background
php-fpm -D

# Start crond for automated tournament scheduler (php artisan schedule:run every minute)
echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root
crond -b -l 8

# Start Nginx in foreground
exec nginx -g "daemon off;"
