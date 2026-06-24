#!/bin/bash

set -e

echo "==> Starting Laravel application setup..."

# Create SQLite database file if not exists
echo "==> Setting up SQLite database..."
touch /var/www/html/database/database.sqlite
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/database

# Set proper permissions for storage
echo "==> Setting storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations
echo "==> Running database migrations..."
php artisan migrate --force

# Create storage symlink
echo "==> Creating storage symlink..."
php artisan storage:link --force || true

# Clear and cache config for production
echo "==> Caching configuration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Setup complete! Starting services..."

# Start supervisord
exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
