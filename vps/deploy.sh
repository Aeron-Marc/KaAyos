#!/bin/bash
# KaAyos Complete VPS Deployment Script (Ubuntu 22.04 / 24.04 LTS)
# Run this on your VPS as root:
#   curl -fsSL https://raw.githubusercontent.com/Aeron-Marc/KaAyos/main/vps/deploy.sh | bash

set -e

DOMAIN="kaayos.tech"
EMAIL="hello@kaayos.tech"
DB_NAME="kaayos"
DB_USER="kaayos"
DB_PASS="KaAyos2026DB!"

echo "============================================"
echo "  Deploying KaAyos Ecosystem on VPS"
echo "  Domain: $DOMAIN"
echo "============================================"

echo "=== [1/8] Updating System Packages ==="
apt update && apt upgrade -y
apt install -y nginx git certbot python3-certbot-nginx supervisor curl unzip openssl software-properties-common

echo "=== [2/8] Installing PHP 8.3 & Extensions ==="
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-bcmath php8.3-zip php8.3-gd php8.3-intl php8.3-cli

echo "=== [3/8] Installing MySQL 8.0 & Creating Database ==="
apt install -y mysql-server
systemctl enable --now mysql
mysql -u root -e "
  CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}';
  ALTER USER '${DB_USER}'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}';
  GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
  FLUSH PRIVILEGES;
"

echo "=== [4/8] Installing Node.js 20 & Composer ==="
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "=== [5/8] Installing Python 3 & Virtual Environment ==="
apt install -y python3 python3-pip python3-venv

echo "=== [6/8] Cloning Repositories into /var/www ==="
mkdir -p /var/www
cd /var/www

if [ ! -d "/var/www/KaAyos" ]; then
  git clone https://github.com/Aeron-Marc/KaAyos.git /var/www/KaAyos
else
  cd /var/www/KaAyos && git pull origin main
fi

if [ ! -d "/var/www/KaAyos-Mobile" ]; then
  git clone https://github.com/Aeron-Marc/KaAyos-Mobile.git /var/www/KaAyos-Mobile
else
  cd /var/www/KaAyos-Mobile && git pull origin main
fi

echo "=== Setting up Laravel Web Application ==="
cd /var/www/KaAyos/kaayos
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --force
fi

sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
sed -i 's/LOG_LEVEL=.*/LOG_LEVEL=warning/' .env
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=3306/' .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
sed -i 's/BROADCAST_CONNECTION=.*/BROADCAST_CONNECTION=reverb/' .env
sed -i 's/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/' .env
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=database/' .env
sed -i "s/SESSION_DOMAIN=.*/SESSION_DOMAIN=${DOMAIN}/" .env
sed -i 's/SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/' .env
sed -i "s/SANCTUM_STATEFUL_DOMAINS=.*/SANCTUM_STATEFUL_DOMAINS=${DOMAIN}/" .env

php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data /var/www/KaAyos/kaayos/storage /var/www/KaAyos/kaayos/bootstrap/cache
chmod -R 775 /var/www/KaAyos/kaayos/storage /var/www/KaAyos/kaayos/bootstrap/cache

echo "=== Setting up ML Microservice ==="
cd /var/www/KaAyos/ml_service
python3 -m venv venv
venv/bin/pip install --upgrade pip
venv/bin/pip install -r requirements.txt

echo "=== Setting up Mobile API Microservice ==="
cd /var/www/KaAyos-Mobile
npm ci
mkdir -p /var/www/KaAyos-Mobile/uploads/profiles \
         /var/www/KaAyos-Mobile/uploads/documents \
         /var/www/KaAyos-Mobile/uploads/portfolios \
         /var/www/KaAyos-Mobile/uploads/bookings \
         /var/www/KaAyos-Mobile/uploads/chat
chown -R www-data:www-data /var/www/KaAyos-Mobile/uploads
chmod -R 775 /var/www/KaAyos-Mobile/uploads

echo "=== [7/8] Configuring Nginx Reverse Proxy ==="
# Ensure temporary SSL certificates exist so Nginx can test and boot before Certbot runs
if [ ! -f /etc/letsencrypt/live/${DOMAIN}/fullchain.pem ]; then
  mkdir -p /etc/letsencrypt/live/${DOMAIN}
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/letsencrypt/live/${DOMAIN}/privkey.pem \
    -out /etc/letsencrypt/live/${DOMAIN}/fullchain.pem \
    -subj "/CN=${DOMAIN}"
fi

cp /var/www/KaAyos/vps/nginx/kaayos.conf /etc/nginx/sites-available/kaayos
ln -sf /etc/nginx/sites-available/kaayos /etc/nginx/sites-enabled/kaayos
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "=== [8/8] Configuring Supervisor Background Services ==="
cp /var/www/KaAyos/vps/supervisor/*.conf /etc/supervisor/conf.d/
supervisorctl reread
supervisorctl update
supervisorctl restart all

echo "=== Requesting Official SSL Certificate (Certbot) ==="
if certbot --nginx -d "${DOMAIN}" -d "www.${DOMAIN}" --non-interactive --agree-tos --email "${EMAIL}"; then
  echo "Official Let's Encrypt SSL configured successfully!"
  systemctl reload nginx
else
  echo "Certbot note: Ensure DNS A-Record for ${DOMAIN} points to this VPS IP before requesting official SSL."
fi

echo "============================================"
echo "  KaAyos Deployment Complete!"
echo "  Web Application : https://${DOMAIN}"
echo "  Mobile REST API : https://${DOMAIN}/api"
echo "  ML Microservice : https://${DOMAIN}/ml"
echo "  Reverb WebSocket: wss://${DOMAIN}/app"
echo "============================================"
