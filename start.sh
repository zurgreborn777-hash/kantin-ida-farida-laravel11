#!/bin/bash

set -e

echo "==> Starting Laravel application setup..."

# Use production environment file for Railway
echo "==> Using .env.production as .env..."
cp /var/www/html/.env.production /var/www/html/.env 2>/dev/null || true

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
php artisan migrate --force || {
    echo "!! Migration failed, continuing anyway..."
}

# Seed database (admin user + sample data)
echo "==> Seeding database..."
php artisan db:seed --force || {
    echo "!! Seeding failed, continuing anyway..."
}

# Create storage symlink
echo "==> Creating storage symlink..."
php artisan storage:link --force || true

# Cache config, routes, and views for production
echo "==> Optimizing Laravel..."
php artisan optimize

# Set nginx port from Railway's PORT env var (default 8080)
PORT=${PORT:-8080}
echo "==> Configuring nginx to listen on port $PORT..."
sed -i "s/listen 8080/listen $PORT/g" /etc/nginx/sites-enabled/default
sed -i "s/listen \[::\]:8080/listen [::]:$PORT/g" /etc/nginx/sites-enabled/default

echo "==> Setup complete! Starting services..."

# Start supervisord
exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf

