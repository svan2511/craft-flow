#!/bin/sh
set -e

echo "========================================"
echo "Starting Laravel Application on Render"
echo "========================================"

# Clear old cached files
rm -f bootstrap/cache/*.php



# Clear all caches
echo "Clearing Laravel caches..."
php artisan optimize:clear

# Wait for PostgreSQL
echo "Waiting for PostgreSQL connection..."
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"

until nc -z "$DB_HOST" "$DB_PORT"; do
  echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done

echo "PostgreSQL is up!"

# Run Migrations
# NOTE: migrate (NOT migrate:fresh) so restarts never wipe business data.
echo "Running database migrations..."
php artisan migrate --force --no-interaction


# Final Optimizations (After keys are set)
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "========================================"
echo "✅ Laravel Entrypoint Completed Successfully!"
echo "========================================"

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf