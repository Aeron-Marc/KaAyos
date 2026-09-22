#!/bin/bash
# KaAyos VPS Quick Update Script
# Run this on your Hostinger VPS as root: bash /var/www/KaAyos/vps/update.sh

set -e

echo "=== Pulling latest changes from Git ==="
cd /var/www/KaAyos
git pull origin main

echo "=== Updating Laravel application ==="
cd /var/www/KaAyos/kaayos

# Composer dependencies (non-interactive as root)
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets
npm ci
npm run build

# Run any pending migrations
php artisan migrate --force

# Ensure storage link exists
php artisan storage:link || true

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /var/www/KaAyos/kaayos/storage
chown -R www-data:www-data /var/www/KaAyos/kaayos/bootstrap/cache
chmod -R 775 /var/www/KaAyos/kaayos/storage
chmod -R 775 /var/www/KaAyos/kaayos/bootstrap/cache

echo "=== Updating Nginx config ==="
cp /var/www/KaAyos/vps/nginx/kaayos.conf /etc/nginx/sites-available/kaayos
nginx -t
systemctl reload nginx

echo "=== Restarting Background Workers (Supervisor) ==="
supervisorctl reread
supervisorctl update
supervisorctl restart all

echo ""
echo "============================================"
echo "  KaAyos updated successfully on VPS!"
echo "============================================"
