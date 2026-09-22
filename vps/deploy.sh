#!/bin/bash
# KaAyos VPS Deployment Script
# Run this on your Hostinger VPS as root

set -e

echo "=== Updating system ==="
apt update && apt upgrade -y

echo "=== Installing Nginx, Supervisor, Certbot ==="
apt install -y nginx git certbot python3-certbot-nginx supervisor

echo "=== Installing PHP 8.3 + extensions ==="
apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-bcmath php8.3-zip php8.3-gd php8.3-intl

echo "=== Installing MySQL 8.0 ==="
apt install -y mysql-server
mysql -u root -e "
  CREATE DATABASE IF NOT EXISTS kaayos;
  CREATE USER IF NOT EXISTS 'kaayos'@'localhost' IDENTIFIED BY 'KaAyos2026DB!';
  GRANT ALL ON kaayos.* TO 'kaayos'@'localhost';
  FLUSH PRIVILEGES;
"

echo "=== Installing Node.js 20 ==="
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

echo "=== Installing Composer ==="
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "=== Installing Python 3.12 ==="
apt install -y python3 python3-pip python3-venv

echo "=== Cloning KaAyos repository ==="
cd /var/www
git clone https://github.com/Aeron-Marc/KaAyos.git
cd KaAyos

echo "=== Installing PHP dependencies ==="
cd kaayos
composer install --no-dev --optimize-autoloader

echo "=== Installing Node dependencies and building assets ==="
npm ci
npm run build

echo "=== Setting up .env ==="
cp .env.example .env
php artisan key:generate

echo "=== Configuring .env for production ==="
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's|APP_URL=http://localhost|APP_URL=https://kaayos.tech|' .env
sed -i 's/LOG_LEVEL=debug/LOG_LEVEL=warning/' .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/DB_DATABASE=kaayos/DB_DATABASE=kaayos/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=kaayos/' .env
sed -i "s/DB_PASSWORD=/DB_PASSWORD=KaAyos2026DB!/" .env
sed -i 's/BROADCAST_CONNECTION=log/BROADCAST_CONNECTION=reverb/' .env
sed -i 's/SESSION_DRIVER=database/SESSION_DRIVER=database/' .env
sed -i 's/SESSION_DOMAIN=null/SESSION_DOMAIN=kaayos.tech/' .env
sed -i 's/SESSION_SECURE_COOKIE=false/SESSION_SECURE_COOKIE=true/' .env
sed -i 's/SANCTUM_STATEFUL_DOMAINS=localhost.*/SANCTUM_STATEFUL_DOMAINS=kaayos.tech/' .env
sed -i 's/MAIL_MAILER=log/MAIL_MAILER=smtp/' .env
sed -i 's|MAIL_FROM_ADDRESS="hello@example.com"|MAIL_FROM_ADDRESS="hello@kaayos.tech"|' .env

echo "=== Running migrations ==="
php artisan migrate --force

echo "=== Setting up storage link ==="
php artisan storage:link

echo "=== Caching config, routes, views ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Setting permissions ==="
chown -R www-data:www-data /var/www/KaAyos/kaayos/storage
chown -R www-data:www-data /var/www/KaAyos/kaayos/bootstrap/cache
chmod -R 775 /var/www/KaAyos/kaayos/storage
chmod -R 775 /var/www/KaAyos/kaayos/bootstrap/cache

echo "=== Setting up ML microservice ==="
cd /var/www/KaAyos/ml_service
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
deactivate

echo "=== Configuring Nginx ==="
cp /var/www/KaAyos/vps/nginx/kaayos.conf /etc/nginx/sites-available/kaayos
ln -sf /etc/nginx/sites-available/kaayos /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

echo "=== Setting up Supervisor for background services ==="
cp /var/www/KaAyos/vps/supervisor/*.conf /etc/supervisor/conf.d/
supervisorctl reread
supervisorctl update
supervisorctl start kaayos-*

echo "=== Setting up SSL with Let's Encrypt ==="
certbot --nginx -d kaayos.tech -d www.kaayos.tech --non-interactive --agree-tos --email hello@kaayos.tech

echo "=== Setting up SSL auto-renewal ==="
echo "0 3 * * * certbot renew --quiet" | crontab -

echo ""
echo "============================================"
echo "  KaAyos deployed successfully!"
echo "  Visit: https://kaayos.tech"
echo "  Admin: admin@kaayos.tech / password"
echo "============================================"
