# KaAyos — Project Scope

A home services marketplace platform connecting clients with verified workers in **Tuy, Batangas**.

---

## In Scope

### User Roles & Features

| Role   | Capabilities |
|--------|-------------|
| Admin  | Manage users, workers, verifications, service categories, services, bookings, disputes, and reports |
| Client | Browse/search workers, book services, message workers, leave reviews, manage account |
| Worker | View/update job status, manage schedule, track earnings, upload documents & portfolio, manage profile |

### Core Flows

- **Registration & Onboarding** — Account creation, email verification, role selection
- **Worker Verification** — Upload government ID, NBI/police clearance, barangay clearance, proof of competency (TESDA/portfolio) → admin approval
- **Booking Lifecycle** — Client books → worker accepts/declines → job in progress → completed/cancelled → review
- **Auto-Cancellation** — Scheduled command cancels stale bookings: expired new bookings (24h), worker no-show (60 min), stale en-route (2h), stuck in-progress (12h)
- **Worker Reporting** — Clients can report workers after completed bookings; stored as disputes of type `worker_report`
- **Realtime Chat** — Client-worker messaging via Laravel Reverb
- **Realtime Notifications** — Booking updates, messages, verification status
- **Dispute Resolution** — Admin-mediated dispute handling for booking disputes and worker reports
- **Earnings Tracking** — Worker earnings report with export
- **Profile Management** — Avatar, contact info, barangay, preferences
- **Password & Email Change** — OTP-verified via email

### Landing Page Features

- **Worker Map View** — Interactive Leaflet map with markers for each worker's location across Tuy's 22 barangays
- **AI Chatbot** — Floating assistant for guest worker search and recommendations
- **Worker Search & Filter** — Filter by service category and barangay location
- **Worker Profiles** — Public profiles with ratings, reviews, skills, and portfolio

### Admin Reports

- **8 report types** — Bookings, revenue, users, worker performance, verifications, disputes, service popularity, reviews
- **Export** — CSV and XLSX with letterhead and KPI summaries
- **Print** — Browser-native print preview with A4 landscape layout

### Booking Statuses

`new` → `accepted` → `en_route` → `in_progress` → `completed` | `cancelled`

### Platform Fee

Configurable percentage (default: 10%) deducted from worker earnings.

### Accessibility

- **Client Dashboard** — Blade + vanilla JS
- **Worker Dashboard** — Blade + vanilla JS
- **Admin Dashboard** — Blade
- **Marketing Pages** — Blade + vanilla JS (home, about, contact, privacy, terms, safety)

---

## Out of Scope

- Mobile apps (native iOS/Android)
- Payment gateway integration (cash-on-service only)
- Third-party OAuth/social login
- Multi-language i18n beyond Filipino/English
- Public API for external developers
- Automated worker scheduling/assignment
- Subscription or membership tiers

---

## Security & Compliance

- Laravel Sanctum token-based API auth
- Session-based web auth
- Rate limiting on OTP endpoints
- 30-day cooldown on email changes
- Worker document verification before activation
- Avatar upload validation (max 2MB, image types only)
