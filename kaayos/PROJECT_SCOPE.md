# Project Scope: KaAyos — Service Marketplace Platform

## 1. Project Overview

KaAyos is a two-sided online service marketplace platform that connects **clients (homeowners)** with **service workers (providers)**. Clients can browse, book, review, and communicate with workers for various home services. Workers can manage their profiles, jobs, earnings, and communications. Administrators oversee the platform through a full management panel.

**Tech Stack:** Laravel 13.8 (PHP 8.3+), Vue 3 SPA, React, Tailwind CSS v4, Vite 8, MySQL, Sanctum API tokens

---

## 2. User Roles

| Role | Description |
|---|---|
| **Guest / Unauthenticated** | Can browse public pages and view worker profiles |
| **Client (Homeowner)** | Registers as a client to book services, message workers, leave reviews |
| **Worker (Service Provider)** | Registers as a worker to receive job bookings, manage earnings, upload portfolio/documents |
| **Admin** | Full platform oversight: users, workers, verifications, services, bookings, disputes, reports |

---

## 3. Implemented Features

### 3.1 Public / Guest Features

| Feature | Implementation |
|---|---|
| Home / Landing Page | Blade view (`/`) |
| Worker Public Profiles | Blade view with worker details (`/workers/{worker}`) |
| Search Page | Blade view (`/search`) |
| Services Index | Blade view (`/services`) |
| About Page | Blade view (`/about`) |
| Contact Page | Blade view (`/contact`) |
| Privacy Policy | Blade view (`/privacy`) |
| Terms of Service | Blade view (`/terms`) |
| Safety Page | Blade view (`/safety`) |

### 3.2 Authentication & User Management

| Feature | Implementation |
|---|---|
| User Registration | POST `/register` with rate limiting (3/hour per IP), role assignment |
| User Login | POST `/login` with rate limiting (5/min per email+IP) |
| Logout | POST `/logout` |
| Email Verification | Laravel MustVerifyEmail with signed URL, resend notification |
| Password Reset (Email Link) | Forgot password → email link → reset form |
| Password Reset (OTP) | Sanctum-protected: `/password-otp/send`, `/password-otp/verify` |
| Email Change with OTP | Sanctum-protected: `/email-otp/send`, `/email-otp/verify` |
| Profile Update | PUT `/api/profile` (name, phone, etc.) |
| Preferences Update | PUT `/api/preferences` |
| Avatar Upload | POST `/api/profile/avatar` |

### 3.3 Client (Homeowner) Features

All routes under `/client/` — requires auth, email verified, no-cache.

| Feature | Route | Implementation |
|---|---|---|
| Client Dashboard | `/client/dashboard` | Blade view with notifications |
| Browse Workers | `/client/workers` | Blade listing view |
| Worker Detail | `/client/workers/{worker}` | Blade detail view |
| My Bookings | `/client/bookings` | Blade view |
| Create Booking | POST `/client/bookings` | Controller action |
| Cancel Booking | PATCH `/client/bookings/{booking}/cancel` | Controller action |
| Submit Review | POST `/client/bookings/{booking}/review` | Controller action |
| Messages | `/client/messages` | Blade view |
| Send Message | POST `/client/messages/send` | Controller action |
| Reviews | `/client/reviews` | Blade view |
| Account / Profile | `/client/account/profile` | Blade view (React component) |

### 3.4 Worker (Service Provider) Features

All routes under `/worker/` — requires auth + email verified + worker role + no-cache.

| Feature | Route | Implementation |
|---|---|---|
| Worker Dashboard | `/worker/dashboard` | Blade view with notifications |
| Dashboard Data (API) | GET `/worker/dashboard/data` | JSON stats endpoint |
| My Jobs | `/worker/jobs` | Blade view |
| Update Job Status | PATCH `/worker/jobs/{booking}/status` | Status transitions |
| Schedule | `/worker/schedule` | Blade view |
| Messages | `/worker/messages` | Blade view |
| Send Message | POST `/worker/messages/send` | Controller action |
| Earnings | `/worker/earnings` | Blade view |
| Export Earnings | GET `/worker/earnings/export` | Export action |
| Profile | `/worker/profile` | Blade view |
| Update Profile | PUT `/worker/profile` | Controller action |
| Upload Avatar | POST `/worker/profile/avatar` | Controller action |
| Upload Portfolio | POST `/worker/profile/portfolio` | Controller action |
| Delete Portfolio | DELETE `/worker/profile/portfolio/{id}` | Controller action |
| Upload Document | POST `/worker/profile/document` | Worker ID/document upload |
| My Documents | `/worker/documents` | Blade view |
| Update Location | PUT `/worker/location` | Location tracking |

### 3.5 Admin Panel Features

All routes under `/admin/` — requires auth + email verified + admin role + no-cache.

| Feature | Route | Implementation |
|---|---|---|
| Admin Dashboard | `/admin/dashboard` | Blade view with overview |
| List Users | `/admin/users` | Blade view |
| View User | `/admin/users/{user}` | Blade detail view |
| Suspend User | POST `/admin/users/{user}/suspend` | Controller action |
| Reactivate User | POST `/admin/users/{user}/reactivate` | Controller action |
| List Workers | `/admin/workers` | Blade view |
| Verifications Queue | `/admin/verification` | Pending document verifications |
| View Verification | `/admin/verification/{verification}` | Detail view |
| Approve Document | POST `/admin/verification/{verification}/approve` | Controller action |
| Reject Document | POST `/admin/verification/{verification}/reject` | Controller action |
| List Service Categories | `/admin/service-categories` | Blade view |
| Create Service Category | GET+POST `/admin/service-categories/create` | Blade form |
| Edit Service Category | GET+PUT `/admin/service-categories/{id}/edit` | Blade form |
| Delete Service Category | DELETE `/admin/service-categories/{id}` | Controller action |
| List Services | `/admin/services` | Blade view |
| Create Service | GET+POST `/admin/services/create` | Blade form |
| Edit Service | GET+PUT `/admin/services/{id}/edit` | Blade form |
| Delete Service | DELETE `/admin/services/{id}` | Controller action |
| Provider Services | `/admin/provider-services` | Blade index |
| List Bookings | `/admin/bookings` | Blade view |
| View Booking | `/admin/bookings/{booking}` | Blade detail view |
| List Disputes | `/admin/disputes` | Blade view |
| View Dispute | `/admin/disputes/{dispute}` | Blade detail view |
| Resolve Dispute | PUT `/admin/disputes/{dispute}` | Controller action |
| Reports | `/admin/reports` | Blade view |
| Export Reports | GET `/admin/reports/export` | Export action |

### 3.6 Vue 3 SPA (Single Page Application)

Mounted at the main `#app` div. Nine page components under `resources/js/components/pages/`:

| Component | Purpose |
|---|---|
| `HomeownerDashboard.vue` | Client's interactive dashboard |
| `Login.vue` | SPA login form |
| `Register.vue` | SPA registration form |
| `WorkerDetail.vue` | Worker profile with booking flow |
| `AIChat.vue` | AI-powered chat assistant |
| `MapScreen.vue` | Map-based worker search |
| `BookingsScreen.vue` | Interactive booking management |
| `ProviderDashboard.vue` | Worker's interactive dashboard |
| `ProviderProfile.vue` | Worker's profile management |

Shared composable: `useAppData.js`

UI component library (under `resources/js/components/ui/`): `Badge.vue`, `Button.vue`, `Card.vue`, `Input.vue`

---

## 4. Data Model (Database Schema)

30 migration files producing the following tables:

| Table | Key Fields | Purpose |
|---|---|---|
| `users` | role (admin/worker/client), name, email, phone, avatar, pending_email, suspended_at | Core user accounts with role-based access |
| `worker_profiles` | user_id, hourly_rate, skills, average_rating, government_id_verified, latitude, longitude, availability | Extended worker information |
| `work_portfolios` | worker_id, title, description, image_path | Worker portfolio/gallery |
| `worker_documents` | worker_id, document_type, file_path, status (pending/approved/rejected), rejection_reason | Government ID / verification documents |
| `bookings` | client_id, worker_id, service_id, status (pending/confirmed/in_progress/completed/cancelled/disputed), scheduled_at, total_price | Job/service bookings |
| `service_categories` | name, slug, description, is_active, sort_order | High-level service groupings |
| `services` | category_id, name, slug, description, base_price, is_active | Individual service offerings |
| `provider_services` | worker_id, service_id, price, is_active | Pivot linking workers to services they offer |
| `disputes` | booking_id, raised_by, reason, status, admin_note | Booking dispute resolution |
| `earnings` | worker_id, booking_id, amount, platform_fee, net_amount, status | Worker earnings tracking |
| `messages` | booking_id, sender_id, receiver_id, message, is_read | Chat between clients and workers |
| `reviews` | booking_id, reviewer_id, reviewee_id, rating, comment | Ratings and reviews on completed bookings |
| `notifications` | type, notifiable_id, data, read_at | In-app notifications |
| `password_otp_tokens` | user_id, token, type, expires_at | Password change / email change OTP storage |
| `personal_access_tokens` | tokenable_type, tokenable_id, name, token, abilities, expires_at | Sanctum personal access tokens |
| Standard Laravel tables | cache, cache_locks, jobs, job_batches, failed_jobs, sessions | Framework infrastructure |

---

## 5. Notifications & Emails

### Mailables (app/Mail)

| Class | Template | Purpose |
|---|---|---|
| `PasswordOtpMail.php` | `email/password-otp.blade.php` | Password reset OTP email |
| `EmailChangeOtpMail.php` | `email/email-change-otp.blade.php` | Email change verification OTP |
| `EmailChangedNotification.php` | `email/email-changed.blade.php` | Confirmation of email change |

### Notifications (app/Notifications)

| Class | Purpose |
|---|---|
| `ForgotPasswordNotification.php` | Password reset link via Laravel notification |
| `VerificationApproved.php` | Notify worker that document verification was approved |
| `VerificationRejected.php` | Notify worker that document verification was rejected (with reason) |
| `DisputeResolved.php` | Notify involved parties of dispute resolution |

---

## 6. Middleware

| Middleware | Purpose | Applied To |
|---|---|---|
| `CheckAdminRole` | Ensures user has `admin` role | All `/admin/*` routes |
| `CheckWorkerRole` | Ensures user has `worker` role | All `/worker/*` routes |
| `PreventBackHistory` | Sets no-cache headers to prevent browser back after logout | All authenticated routes |
| `SetLocale` | Localization (multilingual support scaffold) | All routes |
| `throttle:login` | Rate limit: 5 attempts/min | Login POST |
| `throttle:register` | Rate limit: 3 registrations/hour | Register POST |
| `throttle:email-otp-send` | Rate limit: 3 OTP sent/hour | Email OTP send |
| `throttle:email-otp-verify` | Rate limit: 5 verify attempts/hour | Email OTP verify |

---

## 7. Frontend Architecture

### Rendering Strategy (Hybrid)

| Approach | Used For |
|---|---|
| **Blade (Server-rendered)** | Public pages, authentication pages, admin panel, client area, worker area |
| **Vue 3 SPA** | Interactive dashboard experiences, AI chat, map, booking flow, provider profiles |
| **React** | Client account/profile page (`resources/js/client/account.jsx`) |

### SPA Details

- **Framework:** Vue 3 (Composition API, `<script setup>`)
- **Router:** Vue Router 4 with `createWebHistory()`
- **Styling:** Tailwind CSS v4 (zero-config via `@tailwindcss/vite` plugin)
- **Build:** Vite 8 with Vue + React + Tailwind plugins
- **Entry Point:** `resources/js/app.js` → mounts to `<div id="app">`
- **Shell:** `resources/views/app.blade.php`
- **Fonts:** Bunny Fonts (Instrument Sans, 400/500/600 weights)

### UI Components (resources/js/components/ui/)

`Badge.vue`, `Button.vue`, `Card.vue`, `Input.vue` — built with CVA, clsx, and tailwind-merge

---

## 8. Infrastructure & Tooling

| Tool | Purpose | Configuration |
|---|---|---|
| **PHPUnit 12.5** | Testing | In-memory SQLite, array cache, sync queue, null broadcast |
| **Laravel Pint 1.27** | PHP code style | Default Laravel rules (no custom config) |
| **EditorConfig** | Editor consistency | 4-space indent, LF line endings, UTF-8 |
| **Postman** | API testing/ docs | Collections in `/postman/` directory |
| **Laravel Pail** | Log tailing | Part of `composer dev` |
| **NPM Scripts** | Asset management | `npm run dev`, `npm run build` |
| **Composer Scripts** | Dev workflow | `composer setup`, `composer dev`, `composer test` |

### Development Commands

| Command | What It Does |
|---|---|
| `composer setup` | Full project setup (install, .env, key, migrate, npm install, npm build) |
| `composer dev` | Concurrently runs: php artisan serve + queue:listen + pail + npm run dev |
| `composer test` | Config clear + phpunit test run |

---

## 9. Testing Status

- **Unit Tests:** Only Laravel default example test (`tests/Unit/ExampleTest.php`)
- **Feature Tests:** Only Laravel default example test (`tests/Feature/ExampleTest.php`)
- **Coverage:** No project-specific tests implemented
- **Test Infrastructure:** PHPUnit configured with in-memory SQLite, ready to expand

---

## 10. Known Gaps / Not Yet Implemented

| Area | Status |
|---|---|
| **Payment Integration** | Not implemented; no payment gateway connected |
| **Real-time Notifications** | Not implemented; no WebSockets / Pusher / Laravel Reverb |
| **Queue Monitoring** | Not implemented; Laravel Horizon not configured |
| **Redis / Caching** | Configured in `config/database.php` but not active (using database driver for cache/session/queue) |
| **JavaScript Linting** | Not configured; no ESLint, Prettier, or similar |
| **CI/CD Pipeline** | Not configured |
| **Production Tests** | Not written; only example tests exist |
| **API Documentation** | Postman collections exist but no formal OpenAPI/Swagger spec |
| **Job Scheduling** | No scheduled tasks configured in `routes/console.php` |
| **Error Monitoring** | Pail for local dev only; no Sentry, Flare, or similar |

---

## 11. Project Structure (Key Directories)

```
kaayos/
├── app/
│   ├── Http/
│   │   ├── Controllers/Admin/   (10 controllers)
│   │   ├── Controllers/Api/     (2 controllers)
│   │   ├── Controllers/Auth/    (5 controllers)
│   │   ├── Controllers/Client/  (2 controllers)
│   │   ├── Controllers/Worker/  (2 controllers)
│   │   ├── Controllers/Workers/ (1 controller)
│   │   └── Middleware/          (4 middleware)
│   ├── Mail/                    (3 mailables)
│   ├── Models/                  (13 models)
│   └── Notifications/          (4 notifications)
├── config/                      (12 config files)
├── database/
│   ├── migrations/              (30 migrations)
│   └── kaayos_db.sql            (database dump)
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── pages/           (9 Vue SPA pages)
│   │   │   └── ui/              (4 UI components)
│   │   ├── client/              (React component)
│   │   └── composables/         (1 composable)
│   └── views/
│       ├── admin/               (admin Blade views)
│       ├── client/              (client Blade views)
│       ├── worker/              (worker Blade views)
│       └── ...                  (auth, layouts, email, pages)
├── routes/
│   └── web.php                  (193 lines, all routes)
└── tests/                       (example tests only)
---

*Document generated: July 2026*
