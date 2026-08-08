#!/bin/sh

set -e

echo "========================================"
echo "Starting Laravel Application on Render"
echo "========================================"

# Remove old Laravel cached files
echo "Removing old Laravel cache files..."

rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/routes.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/events.php

# Wait for PostgreSQL
echo "Waiting for PostgreSQL connection..."

until nc -z "$DB_HOST" "$DB_PORT"; do
    echo "PostgreSQL is unavailable - sleeping"
    sleep 2
done

echo "PostgreSQL is up!"

# Run database migrations FIRST
echo "Running database migrations..."

php artisan migrate --force

echo "Database migrations completed!"

# Now clear Laravel caches
echo "Clearing Laravel caches..."

php artisan optimize:clear

# Rebuild Laravel caches
echo "Optimizing Laravel..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "========================================"
echo "Laravel optimization completed"
echo "========================================"

echo "Starting Supervisor..."

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf