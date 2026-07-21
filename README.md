# KaAyos

A home services marketplace platform connecting clients with verified workers in **Tuy, Batangas**, built with Laravel 13.

## Tech Stack

- **Backend:** Laravel 13, PHP ^8.3
- **Frontend:** React 19 (client SPA), Blade + vanilla JS (worker & admin dashboards)
- **Styling:** Tailwind CSS 4 (via Vite)
- **Database:** SQLite (default) / MySQL
- **Auth:** Laravel Sanctum (API), session-based (web)
- **Realtime:** Laravel Reverb (WebSockets for chat & notifications)
- **Queues & Cache:** Database driver
- **Build:** Vite 8, concurrently
- **ML Microservice:** Python FastAPI (scikit-learn for geospatial clustering & worker matching)
- **AI Chatbot:** OpenAI / Gemini-powered assistant (`/api/chat`)

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
- Python 3.10+ (for ML microservice, optional)

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

Additional custom config variables (add to `.env` as needed):

| Variable                      | Default          | Description                              |
| ----------------------------- | ---------------- | ---------------------------------------- |
| `KAAYOS_PLATFORM_FEE_PERCENT` | `10`             | Platform fee percentage on worker earnings |
| `KAAYOS_BOOKING_EXPIRY_HOURS` | `24`             | Hours before unaccepted bookings expire   |
| `KAAYOS_MAX_CONCURRENT_JOBS`  | `3`              | Max active jobs per worker                |
| `KAAYOS_NO_SHOW_MINUTES`      | `60`             | Minutes before worker is marked no-show   |
| `CHATBOT_PROVIDER`            | `openai`         | AI provider (`openai` or `gemini`)        |
| `CHATBOT_API_KEY`             | —                | API key for chatbot provider              |
| `CHATBOT_MODEL`               | `gpt-4o-mini`    | Model for chatbot                         |

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
npm install
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

After seeding, you can log in with any of the following (password: `password`):

| Role   | Email                  | Name              | Service         |
| ------ | ---------------------- | ----------------- | --------------- |
| Admin  | admin@kaayos.com       | Admin KaAyos      | —               |
| Client | maria@example.com      | Maria Santos      | —               |
| Client | john@example.com       | John Villanueva   | —               |
| Worker | juan@example.com       | Juan Dela Cruz    | Plumbing        |
| Worker | elena@example.com      | Elena Santos      | Cleaning        |
| Worker | marco@example.com      | Marco Reyes       | Electrical      |

All seeded data is scoped to barangays in **Tuy, Batangas**.

## Booking Lifecycle

```
new → accepted → en_route → in_progress → completed
```

- **Cancellation** allowed from any status except `completed` or `cancelled`.
- **Rescheduling** supported — either party can request, the other accepts/declines.
- **Booking Photos** — workers can upload job-site photos during a booking.
- **Booking History** — full audit trail of all status changes.
- **Reference Code** — auto-generated format `BK-YYYYMMDD-XXXX`.

### Statuses

| Status       | Description                             |
| ------------ | --------------------------------------- |
| `new`        | Created by client, awaiting worker       |
| `accepted`   | Worker accepted the job                  |
| `en_route`   | Worker is traveling to the job site      |
| `in_progress`| Work is being performed                  |
| `completed`  | Job finished successfully                |
| `cancelled`  | Cancelled by client, worker, or admin    |

## Platform Fee

Configured via `KAAYOS_PLATFORM_FEE_PERCENT` in `.env` (default: `10%`). Deducted from worker earnings upon job completion.

## Worker Verification

Workers must upload the following for admin approval:
1. **Government-Issued ID** — PhilID, UMID, Passport, or Driver's License
2. **Police / NBI Clearance** — issued within 6 months
3. **Barangay Clearance** — proof of address
4. **Proof of Competency** — TESDA NC/COC or portfolio photos / character reference

Statuses: `pending` → `approved` | `rejected`

## Realtime Features (Laravel Reverb)

Start the WebSocket server for realtime chat & notifications:

```bash
php artisan reverb:start
```

For production or external access:

```bash
php artisan reverb:start --port=8080 --host=0.0.0.0
```

## AI Chatbot

An AI assistant is available at `POST /api/chat` (authenticated). Configurable via `CHATBOT_PROVIDER`, `CHATBOT_API_KEY`, and `CHATBOT_MODEL` env vars. Supports OpenAI and Gemini backends.

## ML Microservice

Located in `ml_service/` — a FastAPI-based Python microservice providing:
- **Geospatial clustering** (DBSCAN) of workers by location
- **Worker matching** (Random Forest) using distance, rating, completion rate, and experience
- **Model retraining** endpoint

```bash
cd ml_service
pip install -r requirements.txt
uvicorn main:app --port 8000
```

## Routes

### Public Web

| Method | URI                        | Description          |
| ------ | -------------------------- | -------------------- |
| GET    | `/`                        | Home page            |
| GET    | `/search`                  | Search workers       |
| GET    | `/services`                | Services listing     |
| GET    | `/workers/{worker}`        | Worker public profile|
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

### API (Sanctum auth)

| Method | URI                                  | Description                       |
| ------ | ------------------------------------ | --------------------------------- |
| POST   | `/api/login`                         | API login                         |
| POST   | `/api/register`                      | API registration                  |
| POST   | `/api/logout`                        | API logout                        |
| GET    | `/api/user`                          | Current user                      |
| POST   | `/password-otp/send`                 | Send OTP for password change      |
| POST   | `/password-otp/verify`               | Verify OTP & change password      |
| POST   | `/email-otp/send`                    | Send OTP for email change         |
| POST   | `/email-otp/verify`                  | Verify OTP & change email         |
| PUT    | `/api/profile`                       | Update profile                    |
| PUT    | `/api/preferences`                   | Update preferences                |
| POST   | `/api/profile/avatar`                | Upload avatar                     |
| GET    | `/api/categories`                    | List service categories           |
| GET    | `/api/workers`                       | Browse workers                    |
| GET    | `/api/workers/{id}`                  | Worker detail                     |
| GET    | `/api/bookings`                      | List user bookings                |
| POST   | `/api/bookings`                      | Create booking                    |
| POST   | `/api/bookings/{booking}/cancel`     | Cancel booking                    |
| POST   | `/api/bookings/{booking}/review`     | Submit review                     |
| POST   | `/api/bookings/{booking}/reschedule` | Request reschedule                |
| GET    | `/api/conversations`                 | List conversations                |
| GET    | `/api/conversations/{conv}/messages` | Poll messages                     |
| POST   | `/api/conversations/{conv}/messages` | Send message                      |
| POST   | `/api/conversations/{conv}/messages/read` | Mark messages read          |

### Client Web (auth, verified)

| Method | URI                               | Description               |
| ------ | --------------------------------- | ------------------------- |
| GET    | `/client/dashboard`               | Client dashboard          |
| GET    | `/client/dashboard/notifications` | Dashboard notifications   |
| GET    | `/client/workers`                 | Browse workers            |
| GET    | `/client/workers/{worker}`        | Worker detail/profile     |
| GET    | `/client/bookings`                | Manage bookings           |
| POST   | `/client/bookings`                | Create a booking          |
| POST   | `/client/bookings/{booking}/cancel`    | Cancel a booking      |
| POST   | `/client/bookings/{booking}/review`    | Submit review         |
| POST   | `/client/bookings/{booking}/reschedule` | Request reschedule    |
| POST   | `/client/bookings/{booking}/reschedule-respond` | Respond to reschedule |
| GET    | `/client/messages`                | Messages page             |
| GET    | `/client/messages/poll/{conv}`    | Poll messages             |
| POST   | `/client/messages/send`           | Send a message            |
| POST   | `/client/messages/{conv}/read`    | Mark messages read        |
| GET    | `/client/reviews`                 | My reviews                |
| GET    | `/client/account/profile`         | Account settings          |

### Worker Web (auth, verified, worker)

| Method | URI                                     | Description              |
| ------ | --------------------------------------- | ------------------------ |
| GET    | `/worker/dashboard`                     | Worker dashboard         |
| GET    | `/worker/dashboard/notifications`       | Dashboard notifications  |
| GET    | `/worker/dashboard/data`                | Dashboard JSON data      |
| GET    | `/worker/jobs`                          | Job listings             |
| GET    | `/worker/schedule`                      | Schedule calendar        |
| PATCH  | `/worker/jobs/{booking}/status`         | Update job status        |
| POST   | `/worker/jobs/{booking}/photo`          | Upload job photo         |
| POST   | `/worker/jobs/{booking}/cancel`         | Cancel a job             |
| POST   | `/worker/jobs/{booking}/reschedule`      | Request reschedule       |
| POST   | `/worker/jobs/{booking}/reschedule-respond` | Respond to reschedule |
| GET    | `/worker/messages`                      | Messages                 |
| GET    | `/worker/messages/poll/{conv}`          | Poll messages            |
| POST   | `/worker/messages/send`                 | Send a message           |
| POST   | `/worker/messages/{conv}/read`          | Mark messages read       |
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

### Admin Web (auth, verified, admin)

| Method | URI                                              | Description                   |
| ------ | ------------------------------------------------ | ----------------------------- |
| GET    | `/admin/dashboard`                               | Admin dashboard               |
| GET    | `/admin/users`                                   | User management list          |
| GET    | `/admin/users/{user}`                            | User detail                   |
| POST   | `/admin/users/{user}/suspend`                    | Suspend a user                |
| POST   | `/admin/users/{user}/reactivate`                 | Reactivate a user             |
| GET    | `/admin/workers`                                 | Worker management with filters|
| GET    | `/admin/verification`                            | Worker document verifications |
| GET    | `/admin/verification/{verification}`             | Verification detail           |
| POST   | `/admin/verification/{verification}/approve`     | Approve verification          |
| POST   | `/admin/verification/{verification}/reject`      | Reject verification           |
| GET    | `/admin/service-categories`                      | Service categories            |
| GET    | `/admin/service-categories/create`               | Create category page          |
| POST   | `/admin/service-categories`                      | Create category               |
| GET    | `/admin/service-categories/{id}/edit`            | Edit category page            |
| PUT    | `/admin/service-categories/{id}`                 | Update category               |
| DELETE | `/admin/service-categories/{id}`                 | Delete category               |
| GET    | `/admin/services`                                | Manage services               |
| GET    | `/admin/services/create`                         | Create service page           |
| POST   | `/admin/services`                                | Create service                |
| GET    | `/admin/services/{id}/edit`                      | Edit service page             |
| PUT    | `/admin/services/{id}`                           | Update service                |
| DELETE | `/admin/services/{id}`                           | Delete service                |
| GET    | `/admin/provider-services`                       | Provider service assignments  |
| GET    | `/admin/bookings`                                | View all bookings             |
| GET    | `/admin/bookings/{booking}`                      | Booking detail                |
| POST   | `/admin/bookings/{booking}/cancel`               | Cancel a booking              |
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

## Rate Limiting

| Endpoint              | Limit              |
| --------------------- | ------------------ |
| Login                 | 5/min per email+IP |
| Registration          | 3/hr per IP        |
| Email OTP Send        | 3/hr per user      |
| Email OTP Verify      | 5/hr per user      |
| Client Booking Create | 10/min             |
| Message Polling       | 30/min             |
