# KaAyos

A home services marketplace platform connecting clients with verified workers in **Tuy, Batangas**, built with Laravel 13.

## Tech Stack

- **Backend:** Laravel 13, PHP ^8.3
- **Frontend:** Blade + vanilla JS (client, worker & admin dashboards)
- **Styling:** Tailwind CSS 4 (via Vite)
- **Database:** SQLite (default) / MySQL
- **Auth:** Laravel Sanctum (API), session-based (web)
- **Realtime:** Laravel Reverb (WebSockets for chat & notifications)
- **Queues & Cache:** Database driver
- **Build:** Vite 8, concurrently
- **ML Microservice:** Python FastAPI (scikit-learn for geospatial clustering & worker matching)
- **AI Chatbot:** OpenRouter-powered assistant (`/api/chat`)

## Project Structure

```
KaAyos/
├── kaayos/                        # Laravel application
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── backups/               # SQL backups (kaayos_db.sql)
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── database.sqlite
│   ├── public/
│   ├── resources/
│   │   ├── js/
│   │   │   ├── chatbot.js         # AI chatbot (Vite entry)
│   │   │   └── echo.js
│   │   ├── css/
│   │   └── views/
│   ├── routes/
│   ├── storage/
│   └── vite.config.js
├── ml_service/                    # Python FastAPI microservice (project root)
│   ├── main.py
│   ├── test_ml.py
│   ├── requirements.txt
│   ├── run.bat
│   ├── .venv/                     # Python virtual environment (local)
│   └── models/                    # Trained ML models
├── composer.json
├── package.json
└── README.md
```

## Architecture

Three user roles, each with a dedicated dashboard:

| Role   | Dashboard Tech | Description                                                  |
| ------ | -------------- | ------------------------------------------------------------ |
| Admin  | Blade          | Manage users, workers, verifications, service categories, services, bookings, disputes, and reports |
| Client | Blade + vanilla JS | Browse/search workers, book services, message workers, leave reviews, manage account |
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
| `CHATBOT_PROVIDER`            | `openrouter`     | AI provider (currently OpenRouter)        |
| `CHATBOT_API_KEY`             | —                | API key for chatbot provider              |
| `CHATBOT_MODEL`               | `openai/gpt-4o-mini` | Model for chatbot                         |
| `ML_SERVICE_URL`              | `http://127.0.0.1:8001` | ML microservice base URL              |

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

## Quick Start (all-in-one Laravel dev command)

```bash
composer run dev
```

This concurrently runs:
- `php artisan serve` (web server)
- `php artisan queue:listen --tries=1 --timeout=0` (queue worker)
- `php artisan pail --timeout=0` (log viewer)
- `npm run dev` (Vite HMR)

Available at `http://localhost:8000`.

> **Note:** The ML microservice must be started separately. See **ML Microservice** below.

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
- **Auto-Cancellation** — Bookings are automatically cancelled when stale:
  - `new` bookings with no worker response after 24 hours
  - `accepted` jobs where the worker doesn't start within 60 minutes of the scheduled time (no-show)
  - `en_route` jobs that don't transition to `in_progress` within 2 hours of the scheduled time
  - `in_progress` jobs not updated for over 12 hours
- **Cancellation Reasons** — Both client and worker can provide an optional reason when cancelling. Auto-cancellations include a system-generated reason. Reasons are displayed in the booking detail modal on both the client and worker dashboards.
- **Worker Reporting** — Clients can submit a report against a worker after a completed booking. Reports are stored as disputes of type `worker_report` and are visible in the admin dispute panel.
- **Rescheduling** supported — either party can request, the other accepts/declines.
- **Booking Photos** — workers can upload job-site photos during a booking.
- **Booking History** — full audit trail of all status changes.
- **Reference Code** — auto-generated format `BK-YYYYMMDD-XXXX`.

### Statuses

| Status       | Description                             | Auto-Cancel Trigger |
| ------------ | --------------------------------------- | ------------------- |
| `new`        | Created by client, awaiting worker       | No response within 24 hours |
| `accepted`   | Worker accepted the job                  | Worker doesn't start within 60 min of scheduled time |
| `en_route`   | Worker is traveling to the job site      | No start within 2 hours of scheduled time |
| `in_progress`| Work is being performed                  | No update for 12 hours |
| `completed`  | Job finished successfully                | — |
| `cancelled`  | Cancelled by client, worker, or admin    | — |

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

An AI assistant is available at `POST /api/chat` (authenticated). Configurable via `CHATBOT_PROVIDER`, `CHATBOT_API_KEY`, and `CHATBOT_MODEL` env vars. Currently configured for OpenRouter.

## ML Microservice

Located in `ml_service/` at the project root — a FastAPI-based Python microservice providing:
- **Geospatial clustering** (DBSCAN) of workers by location
- **Worker matching** (Random Forest) using distance, rating, completion rate, and experience
- **Model retraining** endpoint

Ensure `ML_SERVICE_URL` in `kaayos/.env` matches the service port (default `http://127.0.0.1:8001`).

```bash
cd ml_service
pip install -r requirements.txt
uvicorn main:app --host 127.0.0.1 --port 8001 --reload
```

API docs available at `http://127.0.0.1:8001/docs`.

## Routes

See [ROUTES.md](ROUTES.md) for the full route reference (public, API, client, worker, admin) and rate limiting details.

## Running with a Queue Worker

Background jobs (e.g., sending emails) require the queue worker:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

This is included automatically in `composer run dev`.
