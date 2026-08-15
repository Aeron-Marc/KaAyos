# KaAyos — Production Deployment Guide

This document describes the actual steps required to deploy the KaAyos Laravel application to a production environment.

## 1. Requirements

- PHP ^8.3 with extensions: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `json`, `tokenizer`, `xml`
- MySQL 8.0+ (Hostinger provides MySQL/MariaDB on Premium plans)
- Node.js 18+ and npm (for local frontend build only; Hostinger shared hosting does not run Node.js for production)
- Composer (Hostinger may provide Composer via SSH; alternatively build locally and upload `vendor/`)
- Web server: Apache with `mod_rewrite` (Hostinger shared hosting uses Apache)
- **Not supported on Hostinger Premium:** persistent daemon processes, Supervisor, systemd, Nginx configuration, root access

**External service requirements (cannot run on Hostinger Premium shared hosting):**
- Python 3.10+ with `uvicorn` (ML microservice must run on a separate VPS, container, or external host)
- Laravel Reverb WebSocket server (must run on a separate process/host; shared hosting cannot maintain persistent WebSocket listeners)

## 2. Environment Configuration

Copy the environment file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

Set the following variables in `.env`:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kaayos
DB_USERNAME=kaayos_user
DB_PASSWORD=

SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@your-domain.com"
MAIL_FROM_NAME="KaAyos"

ML_SERVICE_URL=http://your-ml-host.example.com:8001

BROADCAST_CONNECTION=log
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_APP_ID=
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

**Hostinger note:** Hostinger prefixes database usernames and database names with your account prefix (e.g., `u123456789_kaayos`). Use the exact names shown in Hostinger **hPanel** → **Databases**.

## 3. MySQL Setup

Create the production database and user in Hostinger **hPanel** → **Databases** → **MySQL Databases**:

1. Create a database (Hostinger will prefix it, e.g., `u123456789_kaayos`).
2. Create a database user (Hostinger will prefix it, e.g., `u123456789_kaayos_user`) and note the password.
3. Add the user to the database with **All Privileges**.

Alternatively, if your Hostinger plan provides SSH/phpMyAdmin access, run:

```sql
CREATE DATABASE IF NOT EXISTS `u123456789_kaayos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'u123456789_kaayos_user'@'localhost' IDENTIFIED BY 'secure-password';
GRANT ALL PRIVILEGES ON `u123456789_kaayos`.* TO 'u123456789_kaayos_user'@'localhost';
FLUSH PRIVILEGES;
```

Update `.env` with the exact prefixed database name, username, and password.

## 4. Composer Installation

Install PHP dependencies with optimized autoloader:

```bash
composer install --no-dev --optimize-autoloader --no-scripts
```

**Hostinger note:** If Composer is not available in your Hostinger SSH terminal, run `composer install` locally and upload the entire `vendor/` directory to the server via File Manager or FTP. Do not run `composer install` with the `--scripts` flag on shared hosting.

## 5. npm Installation / Local Build

Hostinger shared hosting does not provide Node.js for production asset compilation. Build frontend assets locally before uploading:

```bash
npm install --ignore-scripts
npm run build
```

Upload the generated `public/build/` directory to the server. The Laravel Vite plugin will serve these pre-built assets in production.

## 6. Vite Production Build

The production build is handled locally by `npm run build` (step 5). This compiles:
- `resources/css/app.css`
- `resources/css/landing.css`
- `resources/js/echo.js`
- `resources/js/chatbot.js`

Output is written to `public/build/`. Ensure the `public/build/manifest.json` file is uploaded to the server.

## 7. Laravel Storage Link

Create the symbolic link for public storage:

```bash
php artisan storage:link
```

**Hostinger note:** Some shared hosting environments restrict symbolic links. If `storage:link` fails or uploaded images do not appear, create the `public/storage` directory manually and copy or sync files from `storage/app/public` into it after each upload. Alternatively, configure the filesystem disk to `local` and serve files through a controller route if public URLs are not required.

## 8. Database Migration

Run migrations against the production database:

```bash
php artisan migrate --force
```

Do **not** run `migrate:fresh` in production. Seeders are not required for normal production deployment.

## 9. Cache / Config Optimization

Clear and cache configuration, routes, and views:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 10. Queue Worker

Hostinger Premium shared hosting does **not** support persistent daemon processes or Supervisor. You have two options for background jobs (email notifications, booking expiry processing):

### Option A: Synchronous Driver (Recommended for Shared Hosting)

Set in `.env`:

```ini
QUEUE_CONNECTION=sync
```

This sends all queued notifications synchronously during the web request. No background worker is required. This is the simplest and most reliable approach on Hostinger.

### Option B: Cron-Based Queue Worker

If you need asynchronous processing, keep `QUEUE_CONNECTION=database` and set up a Hostinger Cron Job (see step 11) to run:

```bash
php artisan queue:work --stop-when-empty --tries=3 --max-time=300
```

This processes pending jobs and exits. It does **not** run as a persistent daemon. Schedule it to run every 1–5 minutes.

**Do not use `queue:listen` in production, and do not attempt to run Supervisor or systemd on Hostinger.**

## 11. Scheduler

The application has one scheduled task:

```bash
php artisan schedule:run
```

This cancels expired bookings hourly. On Hostinger, configure this via **hPanel** → **Cron Jobs**:

1. Set the command to:
   ```bash
   cd /home/username/domains/your-domain.com/public_html/kaayos && php artisan schedule:run >> /dev/null 2>&1
   ```
   Replace the path with your actual Laravel installation path on Hostinger.
2. Set the frequency to **Every 1 minute**.

If you are using Option B for queues (cron-based worker), you can combine the scheduler and queue worker into a single cron entry:

```bash
cd /home/username/domains/your-domain.com/public_html/kaayos && php artisan schedule:run >> /dev/null 2>&1 && php artisan queue:work --stop-when-empty --tries=3 --max-time=300 >> /dev/null 2>&1
```

## 12. Mail Configuration

Production mail is configured via the `.env` variables listed in step 2. Supported mailers include `smtp`, `sendmail`, `mailgun`, `ses`, `postmark`, and `resend`.

Set `MAIL_MAILER=smtp` and provide your SMTP credentials. The application sends:
- Email verification notifications
- Booking cancellation notifications
- Password reset emails

## 13. Reverb / WebSocket Configuration

**Laravel Reverb cannot run on Hostinger Premium shared hosting.** Hostinger does not allow persistent TCP listeners or daemon processes, which are required for WebSocket servers.

### What this means for KaAyos on Hostinger:

- **Real-time chat** via WebSockets is unavailable on the shared hosting environment.
- **Broadcast events** (e.g., booking notifications) will fall back to the `log` broadcaster if `BROADCAST_CONNECTION=log` is set in `.env`.
- The existing Reverb code and channels remain in the application. If WebSocket support is required, deploy Reverb on a separate VPS, Docker container, or external service and point the Laravel app to it via `.env`.

### Hostinger-compatible configuration:

In `.env`:

```ini
BROADCAST_CONNECTION=log
```

Keep `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_APP_ID`, `REVERB_HOST`, `REVERB_PORT`, and `REVERB_SCHEME` in `.env.example` so the app remains ready for an external Reverb deployment if needed.

**Do not attempt to run `php artisan reverb:start` on Hostinger shared hosting.**

## 14. ML Service Startup

**The FastAPI ML microservice cannot run on Hostinger Premium shared hosting.** Hostinger does not provide Python `uvicorn` hosting or persistent background processes for external services.

### External service requirement:

Deploy `ml_service/` on a separate host that supports Python 3.10+:
- A VPS (DigitalOcean, Linode, AWS EC2, etc.)
- A container platform (Docker on a VPS)
- A Python hosting service (Render, Fly.io, Railway, etc.)

Start the ML service on that external host:

```bash
cd ml_service
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8001
```

Set `ML_SERVICE_URL` in `kaayos/.env` to the external address, e.g.:

```ini
ML_SERVICE_URL=https://ml.your-domain.com
```

For production, restrict CORS origins by setting `ML_CORS_ORIGINS` in the ML service environment (comma-separated origins). Do not use `--reload` in production.

If the ML service is unreachable, KaAyos degrades gracefully: worker suggestions fall back to distance-based ranking, and the chatbot continues to operate.

## 15. Web Server Document Root

Hostinger Premium uses **Apache** with `mod_rewrite` enabled. Point your domain or subdomain document root to the `kaayos/public/` directory.

### Hostinger File Structure

A common Hostinger layout:

```
/home/username/domains/your-domain.com/
├── kaayos/              # Laravel application root (outside public_html is ideal)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── ml_service/      # External service — do NOT upload here unless hosted elsewhere
│   ├── public/          # <-- point domain document root here
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   └── vendor/
```

In **hPanel** → **Domains** → **DNS/Nameservers** or **Hosting** → **Change document root**, set the root to the `public` folder.

### Apache

The `.htaccess` file in `public/` handles routing. Ensure `mod_rewrite` is enabled (it is by default on Hostinger).

If Hostinger's control panel does not allow changing the document root to a subdirectory, upload the contents of `kaayos/public/` into `public_html/` and place the rest of the Laravel application one level above `public_html/` (e.g., `~/kaayos/`), then update the paths in `public/index.php` accordingly:

```php
require __DIR__.'/../kaayos/vendor/autoload.php';
$app = require_once __DIR__.'/../kaayos/bootstrap/app.php';
```

**Do not use Nginx configuration on Hostinger shared hosting.**

## 16. HTTPS Requirement

Production must use HTTPS. In Hostinger **hPanel**, enable **SSL** for your domain (Hostinger provides free Let's Encrypt certificates). Set `APP_URL` to `https://your-domain.com` and configure `SESSION_SECURE_COOKIE=true`.

If Hostinger automatically redirects HTTP to HTTPS, ensure `.htaccess` does not conflict.

## 17. File Permissions

Ensure the following directories are writable:

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

On Hostinger, you can set permissions via **File Manager** → right-click folder → **Permissions**. Ensure the web server user has write access to:
- `storage/logs/`
- `storage/framework/`
- `storage/app/`
- `bootstrap/cache/`

If you cannot change permissions via SSH, use Hostinger File Manager.

## 18. Production Verification

After deployment, verify the application through the browser and Hostinger tools:

```bash
# Health check (via browser or curl if SSH is available)
curl https://your-domain.com/up

# Laravel configuration (if SSH is available)
php artisan config:show app.env
php artisan config:show database.default

# Migrations are up to date (if SSH is available)
php artisan migrate:status
```

In Hostinger **hPanel**:
- Check **Hosting** → **PHP Version** to confirm PHP 8.3 is selected.
- Check **Databases** → **phpMyAdmin** to confirm tables exist.
- Check **Cron Jobs** to confirm the scheduler is active.
- Check **Error Logs** if the site shows a 500 error.

If SSH is not available, you can create a temporary route/controller to output `config('app.env')` and `config('database.default')` for debugging, then remove it afterward.

## 19. Rollback Considerations

- Keep the previous release directory or Git tag available for quick rollback.
- Database migrations are additive only. Do not delete or modify existing migrations.
- If a deployment fails, revert the code, clear caches (`php artisan config:clear`, `php artisan route:clear`, `php artisan view:clear`), and restart queue workers.
- The SQLite database (`database/database.sqlite`) is preserved in the repository as a development fallback but is not used in production.
