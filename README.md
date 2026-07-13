# KaAyos

A home services marketplace platform connecting clients with verified workers in **Tuy, Batangas**, built with Laravel 13, React, Vue, and Blade.

## Tech Stack

- **Backend:** Laravel 13, PHP ^8.3
- **Frontend:** React 19 (client dashboard), Blade + vanilla JS (worker dashboard), Vue 3 (marketing pages)
- **Styling:** Tailwind CSS 4 (via Vite)
- **Database:** SQLite (default) / MySQL
- **Auth:** Laravel Sanctum (API), session-based (web)
- **Queues & Cache:** Database driver
- **Build:** Vite 8, concurrently

## Architecture

Three user roles, each with a dedicated dashboard:

| Role   | Dashboard Tech | Description                                                  |
| ------ | -------------- | ------------------------------------------------------------ |
| Admin  | Blade          | Manage users, workers, verifications, service categories, services, bookings, disputes, and reports |
| Client | React SPA      | Browse/search workers, book services, message workers, leave reviews, manage account |
| Worker | Blade          | View/update job status, manage schedule, track earnings, upload documents & portfolio, manage profile |

## Requirements

- PHP ^8.3
- [Composer](https://getcomposer.org)
- Node.js 18+
- SQLite or MySQL

## Installation

### 1. Clone & enter the project

```bash
git clone <repo-url> kaayos
cd kaayos
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment configuration

```bash
cp .env.example .env
```

Edit `.env` — at minimum configure your database and mail settings:

```ini
DB_CONNECTION=sqlite
# or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=kaayos
# DB_USERNAME=root
# DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-app-password
```

### 4. Generate app key

```bash
php artisan key:generate
```

### 5. Run migrations & seeders

```bash
php artisan migrate --seed
```

### 6. Install & build frontend assets

```bash
npm install --ignore-scripts
npm run build
```

### 7. Start the dev server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Quick Start (all-in-one dev command)

```bash
composer run dev
```

This concurrently runs:
- `php artisan serve` (web server)
- `php artisan queue:listen --tries=1 --timeout=0` (queue worker)
- `php artisan pail --timeout=0` (log viewer)
- `npm run dev` (Vite HMR)

Available at `http://localhost:8000`.

## Test Accounts

After seeding, you can log in with any of the following:

| Role   | Email                  | Password   |
| ------ | ---------------------- | ---------- |
| Admin  | admin@kaayos.com       | password   |
| Client | maria@example.com      | password   |
| Client | john@example.com       | password   |
| Worker | juan@example.com       | password   |
| Worker | elena@example.com      | password   |

All seeded data is scoped to barangays in **Tuy, Batangas**.

## Available Routes

### Public

| Method | URI                        | Description          |
| ------ | -------------------------- | -------------------- |
| GET    | `/`                        | Home page            |
| GET    | `/search`                  | Search workers       |
| GET    | `/services`                | Services listing     |
| GET    | `/login`                   | Login page           |
| POST   | `/login`                   | Login action         |
| POST   | `/logout`                  | Logout               |
| GET    | `/register`                | Registration page    |
| POST   | `/register`                | Registration action  |
| GET    | `/forgot-password`         | Forgot password page |
| GET    | `/reset-password/{token}`  | Reset password page  |
| GET    | `/about`                   | About us page        |
| GET    | `/contact`                 | Contact page         |
| GET    | `/privacy`                 | Privacy policy       |
| GET    | `/terms`                   | Terms of service     |
| GET    | `/safety`                  | Safety guidelines    |

### API (auth:sanctum)

| Method | URI                        | Description                |
| ------ | -------------------------- | -------------------------- |
| POST   | `/password-otp/send`       | Send OTP for password change |
| POST   | `/password-otp/verify`     | Verify OTP & change password |
| POST   | `/email-otp/send`          | Send OTP for email change  |
| POST   | `/email-otp/verify`        | Verify OTP & change email  |
| PUT    | `/api/profile`             | Update profile (name, phone, barangay) |
| PUT    | `/api/preferences`         | Update notification & language prefs |
| POST   | `/api/profile/avatar`      | Upload avatar image        |

### Client (auth, verified)

| Method | URI                               | Description               |
| ------ | --------------------------------- | ------------------------- |
| GET    | `/client/dashboard`               | Client dashboard          |
| GET    | `/client/workers`                 | Browse workers            |
| GET    | `/client/workers/{worker}`        | Worker detail/profile     |
| GET    | `/client/bookings`                | Manage bookings           |
| POST   | `/client/bookings`                | Create a booking          |
| PATCH  | `/client/bookings/{booking}/cancel` | Cancel a booking        |
| POST   | `/client/bookings/{booking}/review` | Submit review           |
| GET    | `/client/messages`                | Messages page             |
| POST   | `/client/messages/send`           | Send a message            |
| GET    | `/client/account/profile`         | Account settings page     |

### Worker (auth, verified, worker)

| Method | URI                                     | Description              |
| ------ | --------------------------------------- | ------------------------ |
| GET    | `/worker/dashboard`                     | Worker dashboard         |
| GET    | `/worker/jobs`                          | Job listings             |
| GET    | `/worker/schedule`                      | Schedule calendar        |
| PATCH  | `/worker/jobs/{booking}/status`         | Update job status        |
| GET    | `/worker/messages`                      | Messages                 |
| POST   | `/worker/messages/send`                 | Send a message           |
| GET    | `/worker/earnings`                      | Earnings report          |
| GET    | `/worker/earnings/export`               | Export earnings          |
| GET    | `/worker/profile`                       | Profile page             |
| PUT    | `/worker/profile`                       | Update profile           |
| POST   | `/worker/profile/avatar`                | Upload avatar            |
| POST   | `/worker/profile/portfolio`             | Upload portfolio image   |
| DELETE | `/worker/profile/portfolio/{id}`        | Delete portfolio image   |
| POST   | `/worker/profile/document`              | Upload verification doc  |
| GET    | `/worker/documents`                     | Documents page           |
| PUT    | `/worker/location`                      | Update current location  |

### Admin (auth, verified, admin)

| Method | URI                                              | Description                   |
| ------ | ------------------------------------------------ | ----------------------------- |
| GET    | `/admin/dashboard`                               | Admin dashboard               |
| GET    | `/admin/users`                                   | User management list          |
| GET    | `/admin/users/{user}`                            | User detail                   |
| POST   | `/admin/users/{user}/suspend`                    | Suspend a user                |
| POST   | `/admin/users/{user}/reactivate`                 | Reactivate a user             |
| GET    | `/admin/workers`                                 | Worker management with filters |
| GET    | `/admin/verification`                            | Worker document verifications |
| GET    | `/admin/verification/{verification}`             | Verification detail           |
| POST   | `/admin/verification/{verification}/approve`     | Approve verification          |
| POST   | `/admin/verification/{verification}/reject`      | Reject verification           |
| GET    | `/admin/service-categories`                      | Manage service categories     |
| POST   | `/admin/service-categories`                      | Create category               |
| PUT    | `/admin/service-categories/{id}`                 | Update category               |
| DELETE | `/admin/service-categories/{id}`                 | Delete category               |
| GET    | `/admin/services`                                | Manage services               |
| POST   | `/admin/services`                                | Create service                |
| PUT    | `/admin/services/{id}`                           | Update service                |
| DELETE | `/admin/services/{id}`                           | Delete service                |
| GET    | `/admin/provider-services`                       | Provider service assignments  |
| GET    | `/admin/bookings`                                | View all bookings             |
| GET    | `/admin/bookings/{booking}`                      | Booking detail                |
| GET    | `/admin/disputes`                                | Dispute management            |
| GET    | `/admin/disputes/{dispute}`                      | Dispute detail                |
| PUT    | `/admin/disputes/{dispute}`                      | Update dispute                |
| GET    | `/admin/reports`                                 | Reports & analytics           |
| GET    | `/admin/reports/export`                          | Export reports                |

## Running with a Queue Worker

Background jobs (e.g., sending emails) require the queue worker:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

This is included automatically in `composer run dev`.

## Realtime Notifications & Chats (Laravel Reverb)

Start the WebSocket server for realtime features:

```bash
php artisan reverb:start
```

For production or external access:

```bash
php artisan reverb:start --port=8080 --host=0.0.0.0
```

## Platform Fee

Configured via `KAAYOS_PLATFORM_FEE_PERCENT` in `.env` (default: `10`).

## Worker Documents

Workers must upload the following for verification:
1. **Government-Issued ID** — PhilID, UMID, Passport, or Driver's License
2. **Police / NBI Clearance** — issued within 6 months
3. **Barangay Clearance** — proof of address
4. **Proof of Competency** — TESDA NC/COC or portfolio photos / character reference
