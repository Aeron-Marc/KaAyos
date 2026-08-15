# KaAyos — Setup & Running Guide

Step-by-step commands to set up and run the full KaAyos system. Each service runs in its **own terminal** — open one terminal per step below.

> All paths are relative to the project root (`KaAyos/`). The Laravel app lives in `kaayos/`, the ML service in `ml_service/`.

---

## 1. Requirements

| Tool            | Version / Notes                              |
| --------------- | -------------------------------------------- |
| PHP             | ^8.3                                          |
| Composer        | latest                                        |
| Node.js         | 18+                                           |
| MySQL or SQLite | DB of choice                                  |
| Python          | 3.10+ (only needed for the ML microservice)   |

---

## 2. One-time setup (run once, in one terminal)

```bash
cd kaayos
composer install
```

Create your environment file and configure it:

```bash
cp .env.example .env        # Windows cmd:  copy .env.example .env
```

Edit `.env` and at minimum set:

```ini
# Database — pick ONE
DB_CONNECTION=mysql
DB_DATABASE=kaayos
DB_USERNAME=root
DB_PASSWORD=

# Or use SQLite instead (simplest for testing):
# DB_CONNECTION=sqlite

# AI chatbot (optional — without a key the chatbot uses scripted replies)
CHATBOT_PROVIDER=openrouter
CHATBOT_API_KEY=your-openrouter-key

# ML microservice URL — must match the port you start it on (see Terminal 1)
ML_SERVICE_URL=http://127.0.0.1:8001
```

Then finish the setup:

```bash
php artisan key:generate
php artisan migrate --seed
npm install
php artisan storage:link    # required so uploaded files / avatars display
```

---

## 3. Start the services — one terminal per service

### Terminal 1 — ML Microservice (Python, port 8001)

```bash
cd ml_service
.venv\Scripts\activate      # Windows. On macOS/Linux:  source .venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --host 127.0.0.1 --port 8001 --reload
```

- API docs: `http://127.0.0.1:8001/docs`
- Or double-click `run.bat` (does the same).
- This must match `ML_SERVICE_URL` in `kaayos/.env`.

### Terminal 2 — Laravel Web Server (port 8000)

```bash
cd kaayos
php artisan serve
```

- Open `http://localhost:8000` in your browser.

### Terminal 3 — Queue Worker (background jobs)

```bash
cd kaayos
php artisan queue:listen --tries=1 --timeout=0
```

Handles background jobs (e.g. booking auto-cancellation, emails).

### Terminal 4 — Frontend Assets (Vite dev server)

```bash
cd kaayos
npm run dev
```

Compiles CSS/JS with hot-reload during development.

### Terminal 5 — Optional: WebSockets (Laravel Reverb)

```bash
cd kaayos
php artisan reverb:start
```

Only needed for realtime chat/notifications. Broadcasting defaults to `log` in `.env`, so the core features work without it.

---

## 4. Alternative: everything in one terminal

To run the Laravel server + queue worker + Vite together in a single terminal:

```bash
cd kaayos
composer run dev:win        # Windows
# composer run dev          # macOS / Linux (also starts the pail log viewer)
```

The ML microservice (Terminal 1) must still be started separately.

---

## 5. Test accounts

After running `php artisan migrate --seed`, log in with any of these (password is **`password`** for all):

| Role   | Email                        | Name              | Service     |
| ------ | ---------------------------- | ----------------- | ----------- |
| Admin  | `admin@kaayos.com`           | Admin KaAyos      | —           |
| Client | `juan.cruz@example.com`      | Juan Dela Cruz    | —           |
| Client | `maria.santos@example.com`   | Maria Santos      | —           |
| Client | `jayson.villanueva@example.com` | Jayson Villanueva | —        |
| Worker | `divina.lopez@example.com`   | Divina Lopez      | Plumbing    |
| Worker | `cherry.pascual@example.com` | Cherry Gil Pascual| Cleaning    |
| Worker | `ferdinand.ocampo@example.com` | Ferdinand Ocampo | Electrical |

---

## 6. Verify everything is running

| Check                                  | Expected result                          |
| -------------------------------------- | ---------------------------------------- |
| `http://localhost:8000`                | KaAyos landing page loads                |
| `http://127.0.0.1:8001/health`         | ML service reports healthy               |
| Chat with the bot on the homepage      | Guest chatbot replies                    |
| Log in as a worker, upload a document  | Image/file appears (needs storage:link)  |

---

## 7. Troubleshooting

- **"Please run a database migration" / table not found** — run `php artisan migrate --seed`.
- **Uploaded images broken** — you skipped `php artisan storage:link`.
- **Chatbot always errors** — check `CHATBOT_API_KEY` / network; without a key it falls back to scripted replies.
- **Config changes not applied** — `php artisan config:clear` and restart the server.
- **ML endpoints fail** — make sure Terminal 1 is running on the same port as `ML_SERVICE_URL`.