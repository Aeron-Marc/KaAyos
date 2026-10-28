# KaAyos — Routes

All web routes require authentication via session (Laravel web middleware). API routes use Laravel Sanctum bearer tokens.

---

## Public Web

| Method | URI                        | Description          |
| ------ | -------------------------- | -------------------- |
| GET    | `/`                        | Home page            |
| GET    | `/search`                  | Search workers       |
| GET    | `/services`                | Services listing (redirects to `/#services`) |
| GET    | `/workers/{worker}`        | Worker public profile|
| GET    | `/login`                   | Login page           |
| POST   | `/login`                   | Login action         |
| POST   | `/logout`                  | Logout               |
| GET    | `/register`                | Registration page    |
| POST   | `/register`                | Registration action  |
| GET    | `/forgot-password`         | Forgot password page |
| POST   | `/forgot-password`         | Send reset link      |
| GET    | `/reset-password/{token}`  | Reset password page  |
| POST   | `/reset-password`          | Update password      |
| GET    | `/email/verify`            | Email verification notice |
| GET    | `/email/verify/{id}/{hash}` | Verify email (signed) |
| POST   | `/email/verification-notification` | Resend verification |
| GET    | `/about`                   | About us page        |
| GET    | `/contact`                 | Contact page         |
| POST   | `/contact`                 | Submit contact form  |
| GET    | `/privacy`                 | Privacy policy       |
| GET    | `/terms`                   | Terms of service     |
| GET    | `/safety`                  | Safety guidelines    |
| GET    | `/locale/{locale}`         | Locale/language switch |

## Social Authentication (Google & Facebook)

| Method | URI                                        | Description                   |
|--------|--------------------------------------------|-------------------------------|
| GET    | `/auth/{provider}/redirect`                | Social auth redirect          |
| GET    | `/auth/{provider}/callback`                | Social auth callback          |
| GET    | `/auth/google/register/redirect`           | Google registration redirect  |
| GET    | `/auth/google/register/callback`           | Google registration callback  |
| GET    | `/auth/google/register/form`               | Google registration form      |
| POST   | `/auth/google/register/complete`           | Complete Google registration  |
| GET    | `/auth/facebook/register/redirect`         | Facebook registration redirect|
| GET    | `/auth/facebook/register/callback`         | Facebook registration callback|
| GET    | `/auth/facebook/register/form`             | Facebook registration form    |
| POST   | `/auth/facebook/register/complete`         | Complete Facebook registration|

## Shareable Worker Profile

| Method | URI                        | Description          |
|--------|----------------------------|----------------------|
| GET    | `/worker/{code}`           | Shareable worker profile link (e.g. `/worker/ID-4K7QP`) |

---

## API (Sanctum auth)

| Method | URI                                  | Description                       |
|--------|--------------------------------------|-----------------------------------|
| POST   | `/api/login`                         | API login                         |
| POST   | `/api/register`                      | API registration                  |
| GET    | `/api/user`                          | Current authenticated user        |
| POST   | `/api/logout`                        | API logout                        |
| POST   | `/password-otp/send`                 | Send OTP for password change      |
| POST   | `/password-otp/verify`               | Verify OTP & change password      |
| POST   | `/email-otp/send`                    | Send OTP for email change         |
| POST   | `/email-otp/verify`                  | Verify OTP & change email         |
| PUT    | `/api/profile`                       | Update profile                    |
| PUT    | `/api/preferences`                   | Update preferences                |
| POST   | `/api/profile/avatar`                | Upload avatar                     |
| POST   | `/api/location`                      | Save worker location              |
| POST   | `/api/location/reverse`              | Reverse geocode coordinates       |
| POST   | `/api/chat`                          | AI chatbot (public + authenticated) |
| POST   | `/api/chat/suggest`                  | AI worker suggestions (authenticated) |

---

## Client Web (auth, verified)

| Method | URI                                            | Description               |
| ------ | ---------------------------------------------- | ------------------------- |
| GET    | `/client/dashboard`                            | Client dashboard          |
| GET    | `/client/dashboard/notifications`              | Dashboard notifications   |
| GET    | `/client/workers`                              | Browse workers            |
| GET    | `/client/workers/{worker}`                     | Worker detail/profile     |
| GET    | `/client/workers/{worker}/check-schedule`| Check schedule conflict       |
| GET    | `/client/bookings`                       | Manage bookings             |
| POST   | `/client/bookings`                       | Create a booking            |
| GET    | `/client/bookings/{booking}/track`       | Track live booking location |
| POST   | `/client/bookings/{booking}/cancel`      | Cancel a booking            |
| POST   | `/client/bookings/{booking}/review`      | Submit review               |
| POST   | `/client/bookings/{booking}/report`      | Report a worker             |
| POST   | `/client/bookings/{booking}/reschedule`  | Request reschedule          |
| POST   | `/client/bookings/{booking}/reschedule-respond`| Respond to reschedule   |
| POST   | `/client/bookings/{booking}/respond-scope-amendment`| Respond to scope amendment|
| POST   | `/client/bookings/{booking}/respond-team-suggestion`| Respond to team suggestion|
| POST   | `/client/bookings/{booking}/mark-complete` | Mark job complete         |
| POST   | `/client/bookings/{booking}/confirm-complete`| Confirm job completion    |
| GET    | `/client/messages`                       | Messages page               |
| GET    | `/client/messages/start`                 | Start a conversation        |
| GET    | `/client/messages/poll/{conversation}`   | Poll messages               |
| POST   | `/client/messages/send`                  | Send a message              |
| POST   | `/client/messages/{conversation}/read`   | Mark messages read          |
| GET    | `/client/reviews`                        | My reviews                  |
| GET    | `/client/suggestions`                    | Worker suggestions          |
| GET    | `/client/account/profile`                | Account settings            |
| GET    | `/client/account/addresses`              | List saved addresses        |
| POST   | `/client/account/addresses`              | Create address              |
| PUT    | `/client/account/addresses/{address}`    | Update address              |
| DELETE | `/client/account/addresses/{address}`    | Delete address              |
| PATCH  | `/client/account/addresses/{address}/default`| Set default address      |
| GET    | `/client/account/identity`               | Identity verification page  |
| POST   | `/client/account/identity`               | Submit identity verification|
| GET    | `/client/testimonials`                   | My testimonials             |
| GET    | `/client/testimonials/create`            | Submit testimonial form     |
| POST   | `/client/testimonials`                   | Submit testimonial          |

---

## Worker Web (auth, verified, worker)

| Method | URI                                                 | Description              |
| ------ | --------------------------------------------------- | ------------------------ |
| GET    | `/worker/dashboard`                                 | Worker dashboard         |
| GET    | `/worker/dashboard/notifications`                   | Dashboard notifications  |
| GET    | `/worker/dashboard/data`                            | Dashboard JSON data      |
| GET    | `/worker/complete-profile`                          | Complete profile page    |
| POST   | `/worker/complete-profile`                          | Save complete profile    |
| GET    | `/worker/jobs`                                      | Job listings             |
| GET    | `/worker/jobs/{booking}/details`                    | Job details              |
| PATCH  | `/worker/jobs/{booking}/status`                     | Update job status        |
| POST   | `/worker/jobs/{booking}/photo`                      | Upload job photo         |
| POST   | `/worker/jobs/{booking}/cancel`                     | Cancel a job             |
| POST   | `/worker/jobs/{booking}/decline`                    | Decline a new job        |
| POST   | `/worker/jobs/{booking}/reschedule`                 | Request reschedule       |
| POST   | `/worker/jobs/{booking}/reschedule-respond`         | Respond to reschedule    |
| POST   | `/worker/jobs/{booking}/confirm-complete`           | Confirm job completion   |
| POST   | `/worker/jobs/{booking}/start-timer`                | Start job timer          |
| POST   | `/worker/jobs/{booking}/end-timer`                  | End job timer            |
| POST   | `/worker/jobs/{booking}/request-scope-amendment`    | Request scope amendment  |
| POST   | `/worker/jobs/{booking}/suggest-team`               | Suggest team/peers       |
| POST   | `/worker/jobs/{booking}/respond-peer-invite`        | Respond to peer invite   |
| POST   | `/worker/jobs/{booking}/location-ping`              | Send live location ping  |
| GET    | `/worker/jobs/{booking}/details`                    | Job details              |
| GET    | `/worker/schedule`                                  | Schedule calendar        |
| GET    | `/worker/calendar/data`                             | Calendar JSON data       |
| GET    | `/worker/calendar/route`                            | Daily route view         |
| GET    | `/worker/calendar/optimize-route`                   | Optimized route (ML)     |
| GET    | `/worker/messages`                                  | Messages                 |
| GET    | `/worker/messages/start`                            | Start a conversation     |
| GET    | `/worker/messages/poll/{conversation}`               | Poll messages            |
| POST   | `/worker/messages/send`                             | Send a message           |
| POST   | `/worker/messages/{conversation}/read`               | Mark messages read       |
| GET    | `/worker/earnings`                                  | Earnings report          |
| GET    | `/worker/earnings/export`                           | Export earnings          |
| GET    | `/worker/profile`                                   | Profile page             |
| PUT    | `/worker/profile`                                   | Update profile           |
| POST   | `/worker/profile/avatar`                            | Upload avatar            |
| POST   | `/worker/profile/portfolio`                         | Upload portfolio image   |
| DELETE | `/worker/profile/portfolio/{id}`                    | Delete portfolio image   |
| POST   | `/worker/profile/document`                          | Upload verification doc  |
| GET    | `/worker/documents`                                 | Documents page           |
| PUT    | `/worker/location`                                  | Update current location  |
| GET    | `/worker/services`                                  | My services list         |
| POST   | `/worker/services`                                  | Add a service            |
| PUT    | `/worker/services/{service}`                        | Update service price     |
| PATCH  | `/worker/services/{service}/toggle`                 | Toggle service availability|
| DELETE | `/worker/services/{service}`                        | Remove a service         |
| GET    | `/worker/testimonials`                             | My testimonials          |
| GET    | `/worker/testimonials/create`                      | Submit testimonial form  |
| POST   | `/worker/testimonials`                             | Submit testimonial       |

---

## Admin Web (auth, verified, admin)

| Method | URI                                              | Description                   |
| ------ | ------------------------------------------------ | ----------------------------- |
| GET    | `/admin/dashboard`                               | Admin dashboard               |
| GET    | `/admin/dashboard/action-queue/export`           | Export action queue           |

## Users

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/users`                                   | User management list          |
| GET    | `/admin/users/{user}`                            | User detail                   |
| POST   | `/admin/users/{user}/suspend`                    | Suspend a user                |
| POST   | `/admin/users/{user}/reactivate`                 | Reactivate a user             |

## Workers

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/workers`                                 | Worker management with filters|

## Worker Verifications

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/verification`                            | Worker document verifications |
| GET    | `/admin/verification/{verification}`             | Verification detail           |
| POST   | `/admin/verification/{verification}/approve`     | Approve verification          |
| POST   | `/admin/verification/{verification}/reject`      | Reject verification           |

## Client Identity Verifications

| Method | URI                                                                | Description                        |
|--------|--------------------------------------------------------------------|------------------------------------|
| GET    | `/admin/client-verifications`                                      | Client identity verifications list |
| GET    | `/admin/client-verifications/{verification}`                       | Verification detail                |
| POST   | `/admin/client-verifications/{verification}/approve`               | Approve verification               |
| POST   | `/admin/client-verifications/{verification}/reject`                | Reject verification                |
| POST   | `/admin/client-verifications/{verification}/request-resubmission`  | Request resubmission               |
| GET    | `/admin/client-verifications/{verification}/document/{document}`   | Download verification document     |

## Service Categories

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/service-categories`                      | Service categories            |
| GET    | `/admin/service-categories/create`               | Create category page          |
| POST   | `/admin/service-categories`                      | Create category               |
| GET    | `/admin/service-categories/{id}/edit`            | Edit category page            |
| PUT    | `/admin/service-categories/{id}`                 | Update category               |
| DELETE | `/admin/service-categories/{id}`                 | Delete category               |

## Services

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/services`                                | Manage services               |
| GET    | `/admin/services/create`                         | Create service page           |
| POST   | `/admin/services`                                | Create service                |
| GET    | `/admin/services/{id}/edit`                      | Edit service page             |
| PUT    | `/admin/services/{id}`                           | Update service                |
| DELETE | `/admin/services/{id}`                           | Delete service                |
| GET    | `/admin/services/{service}/extras`               | Service extras list           |
| POST   | `/admin/services/{service}/extras`               | Create service extra          |
| PUT    | `/admin/services/{service}/extras/{extra}`       | Update service extra          |
| DELETE | `/admin/services/{service}/extras/{extra}`       | Delete service extra          |

## Provider Services

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/provider-services`                       | Provider service assignments  |

## Bookings

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/bookings`                                | View all bookings             |
| GET    | `/admin/bookings/{booking}`                      | Booking detail                |
| POST   | `/admin/bookings/{booking}/cancel`               | Cancel a booking              |

## Disputes

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/disputes`                                | Dispute management            |
| GET    | `/admin/disputes/{dispute}`                      | Dispute detail                |
| PUT    | `/admin/disputes/{dispute}`                      | Update dispute                |

## Reports

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/reports`                                 | Reports & analytics           |
| GET    | `/admin/reports/export`                          | Export reports (CSV/XLSX)     |
| GET    | `/admin/reports/print`                           | Print report preview          |

## Testimonials

| Method | URI                                              | Description                   |
|--------|--------------------------------------------------|-------------------------------|
| GET    | `/admin/testimonials`                            | Testimonial management        |
| GET    | `/admin/testimonials/{testimonial}`              | Testimonial detail            |

---

## Rate Limiting

| Endpoint              | Limit              |
| --------------------- | ------------------ |
| Login                 | 5/min per email+IP |
| Registration          | 3/hr per IP        |
| Password OTP Send     | 3/hr per user      |
| Password OTP Verify   | 5/hr per user      |
| Email OTP Send        | 3/hr per user      |
| Email OTP Verify      | 5/hr per user      |
| Client Booking Create | 10/min             |
| Worker Report         | 3/min per user      |
| Message Polling       | 30/min             |
| AI Chatbot            | 20/min per IP      |
