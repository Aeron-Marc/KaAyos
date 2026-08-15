# KaAyos

A home services marketplace platform connecting clients with verified workers in **Tuy, Batangas**, built with Laravel 13.

## Setup & Running

Installation, running each service (Laravel server, queue worker, Vite, ML microservice, Reverb), and test accounts are documented in **[SETUP.md](SETUP.md)**.

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

## AI Chatbot

An AI assistant is available on the homepage (guest) and at `POST /api/chat` (client area). Configurable via `CHATBOT_PROVIDER`, `CHATBOT_API_KEY`, and `CHATBOT_MODEL` env vars. Currently configured for OpenRouter.

## ML Microservice

Located in `ml_service/` at the project root — a FastAPI-based Python microservice providing:
- **Geospatial clustering** (DBSCAN) of workers by location
- **Worker matching** (Random Forest) using distance, rating, completion rate, and experience
- **Model retraining** endpoint

See [SETUP.md](SETUP.md) — Terminal 1 — for how to run it.

## Routes

See [ROUTES.md](ROUTES.md) for the full route reference (public, API, client, worker, admin) and rate limiting details.
