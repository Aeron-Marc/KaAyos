# KaAyos — Routes

All web routes require authentication via session (Laravel web middleware). API routes use Laravel Sanctum bearer tokens.

---

## Public Web

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

---

## API (Sanctum auth)

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

---

## Client Web (auth, verified)

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
| POST   | `/client/bookings/{booking}/report`     | Report a worker       |
| GET    | `/client/messages`                | Messages page             |
| GET    | `/client/messages/poll/{conv}`    | Poll messages             |
| POST   | `/client/messages/send`           | Send a message            |
| POST   | `/client/messages/{conv}/read`    | Mark messages read        |
| GET    | `/client/reviews`                 | My reviews                |
| GET    | `/client/account/profile`         | Account settings          |

---

## Worker Web (auth, verified, worker)

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

---

## Admin Web (auth, verified, admin)

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

---

## Rate Limiting

| Endpoint              | Limit              |
| --------------------- | ------------------ |
| Login                 | 5/min per email+IP |
| Registration          | 3/hr per IP        |
| Email OTP Send        | 3/hr per user      |
| Email OTP Verify      | 5/hr per user      |
| Client Booking Create | 10/min             |
| Worker Report         | 3/min per user      |
| Message Polling       | 30/min             |
