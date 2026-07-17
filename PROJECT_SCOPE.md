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
- **Realtime Chat** — Client-worker messaging via Laravel Reverb
- **Realtime Notifications** — Booking updates, messages, verification status
- **Dispute Resolution** — Admin-mediated dispute handling
- **Earnings Tracking** — Worker earnings report with export
- **Profile Management** — Avatar, contact info, barangay, preferences
- **Password & Email Change** — OTP-verified via email

### Booking Statuses

`pending` → `accepted` | `declined` → `in_progress` → `completed` | `cancelled`

### Platform Fee

Configurable percentage (default: 10%) deducted from worker earnings.

### Accessibility

- **Client Dashboard** — React SPA
- **Worker Dashboard** — Blade + vanilla JS
- **Admin Dashboard** — Blade
- **Marketing Pages** — Vue 3 (home, about, contact, privacy, terms, safety)

---

## Out of Scope

- Mobile apps (native iOS/Android)
- Payment gateway integration (cash-on-service only)
- Third-party OAuth/social login
- Multi-language i18n beyond Filipino/English
- Public API for external developers
- Automated worker scheduling/assignment
- Geolocation-based worker matching beyond barangay filter
- Subscription or membership tiers

---

## Security & Compliance

- Laravel Sanctum token-based API auth
- Session-based web auth
- Rate limiting on OTP endpoints
- 30-day cooldown on email changes
- Worker document verification before activation
- Avatar upload validation (max 2MB, image types only)
