#!/bin/bash
# KaAyos Quick Update Script
# Run on VPS as root: bash /var/www/KaAyos/vps/update.sh

set -e

echo "=== [1/4] Pulling Latest Git Changes ==="
cd /var/www/KaAyos
git pull origin main

cd /var/www/KaAyos-Mobile
git pull origin main

echo "=== [2/4] Updating Laravel Application ==="
cd /var/www/KaAyos/kaayos
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data /var/www/KaAyos/kaayos/storage /var/www/KaAyos/kaayos/bootstrap/cache
chmod -R 775 /var/www/KaAyos/kaayos/storage /var/www/KaAyos/kaayos/bootstrap/cache

echo "=== [3/4] Updating Mobile API Backend ==="
cd /var/www/KaAyos-Mobile
npm ci
chown -R www-data:www-data /var/www/KaAyos-Mobile/uploads
chmod -R 775 /var/www/KaAyos-Mobile/uploads

echo "=== [4/4] Updating Nginx & Supervisor ==="
cp /var/www/KaAyos/vps/nginx/kaayos.conf /etc/nginx/sites-available/kaayos
nginx -t
systemctl reload nginx

cp /var/www/KaAyos/vps/supervisor/*.conf /etc/supervisor/conf.d/
supervisorctl reread
supervisorctl update
supervisorctl restart all

echo "============================================"
echo "  KaAyos successfully updated on VPS!"
echo "============================================"
