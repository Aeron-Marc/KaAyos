# Build Prompt: KaAyos Pricing Standardization

Paste this whole prompt into your AI coding assistant (Claude Code, Cursor, etc.) when you're ready to implement. It assumes access to the KaAyos repo(s): Laravel admin/backend, Express backend, and the Expo/React Native mobile app.

---

## Context

KaAyos is a **display-only** home services marketplace (Tuy, Batangas, in partnership with the local PESO office). It does not process payments, take commissions, or handle money in any way. Prices shown in the app are for reference/quoting only — the actual transaction happens off-platform between client and worker.

Right now pricing is inconsistent: some places let users type in an arbitrary `price`, currency symbols are mixed (`₱` vs `PHP`), and there's leftover platform-fee logic from an earlier design that no longer applies. This task standardizes pricing across all three codebases (Laravel, Express, Expo) around one formula and removes the fee logic entirely.

## Pricing Formula

```
display_price = worker's hourly_rate × estimated_hours + sum(extras.cost)
```

- **Admin** sets `base_price` per service — the minimum floor a worker's rate must meet.
- **Worker** sets `hourly_rate` on their profile (must be ≥ that service's `base_price`).
- **Worker** may optionally set a per-service `custom_price` on `provider_services`, which overrides `hourly_rate` for that specific service (also must be ≥ `base_price`).
- **Extras** are itemized add-ons (materials, etc.) — see "Extras Model" below.
- The final number is **always shown as a breakdown**, never just a total (see "Price Display" below).

## Extras Model (resolved)

Use a hybrid model, not pure free-text and not pure admin-locked:

1. Admin defines a **catalog of standard extras per service** — `service_extras` table: `service_id`, `name`, `suggested_cost`, `is_active`. E.g. for "Pipe Repair": *PVC pipe, 3m — ₱150*.
2. When creating a booking, the client/worker can select from that catalog **and/or** add a **free-text custom extra** (`name` + `cost` they type themselves).
3. Custom (free-text) extras must be visually and structurally distinguished from catalog extras — flag them as `source: 'custom'` vs `source: 'catalog'` in `booking_extras`, and render custom ones in the UI with a label like *"Estimated by [worker], not verified"*.
4. Do not validate custom extra costs against anything — they're explicitly unverified estimates. Do not let custom extras block booking submission.

## Price Display (resolved)

Always render the full breakdown, never a bare total:

```
Labor:  ₱150/hr × 3 hrs        = ₱450
Extras:
  • PVC pipe, 3m (catalog)     = ₱150
  • Extra tubing (custom, est.)= ₱80
Total                          = ₱680
```

This applies everywhere a price appears: booking modal, booking detail/list, worker profile preview. No screen should show only a final number with no way to see how it was derived.

## Earnings/Quote Log (resolved)

Rename the `earnings` table and its concept to **`booking_quotes`** (or `price_log` if that name is already taken elsewhere in the schema — check first). Keep the columns/logic that already exist (populated by both Laravel and Express when a booking is completed), but:
- Drop any remaining platform fee / net calculation logic.
- Update all code references, model names, and any admin UI labels from "earnings" to "quotes" so nothing implies KaAyos handled money.
- This table stays useful for reporting and lines up with existing worker-job matching analytics — don't deprecate it, just rename and clean it.

---

## Schema Audit Findings (from `kaayos_db.sql`)

Read against the actual database dump before implementing — several assumptions in the original plan don't hold:

1. **`bookings` has no link to `services`.** It only has `service_category varchar(255)`, a free-text field, not a foreign key. Checking real rows, most values (*"Faucet Replacement," "Room Repaint," "Completed Job," "General"*) don't match any `services.name` at all — only a few happen to line up exactly. **Do not attempt to backfill `service_id` by string-matching `service_category` against `services.name`.** A fuzzy match risks silently attaching the wrong `base_price` to a historical booking, which would corrupt reporting and validation. Historical rows keep `service_id = NULL`; only new bookings going forward get a real `service_id`, selected from a dropdown at booking time (not typed in).
2. `services.base_price` and `worker_profiles.hourly_rate` **already exist** as nullable `decimal(10,2)` — no new columns, just the NOT NULL conversion below.
3. `provider_services.custom_price` **already exists**, nullable — no schema change, only new validation logic.
4. `bookings.price` **already exists**, nullable — keep the column, just stop treating it as client-writable input.
5. `earnings` **already exists** with real data using columns `gross_amount`, `platform_fee`, `net_amount`, `paid_at` — the rename below has to account for these actual columns, not a generic set.

### 1. Database

- [ ] **Add `bookings.service_id`** → `BIGINT UNSIGNED NULL`, FK to `services.id`, `ON DELETE SET NULL`. Nullable because historical rows can't be safely backfilled (see finding #1). Add an index. Application logic should require this field for all *new* bookings even though the DB allows null, to preserve old data.
- [ ] Keep `bookings.service_category` as-is for now — it's the historical/display label for old rows and for any freeform job note. Don't drop or repurpose it; just stop relying on it for pricing logic. Consider renaming it to `job_description` in a later, separate cleanup once the mobile app and both backends are confirmed to only read `service_id` for pricing — don't bundle a rename into this task, it's a separate migration with its own risk.
- [ ] Before adding constraints, run a backfill check: `UPDATE services SET base_price = 0 WHERE base_price IS NULL;` and `UPDATE worker_profiles SET hourly_rate = 0 WHERE hourly_rate IS NULL;` (sample data looks fully populated, but don't assume — verify on the live DB first)
- [ ] `services.base_price` → `NOT NULL DEFAULT 0`
- [ ] `worker_profiles.hourly_rate` → `NOT NULL DEFAULT 0`
- [ ] Add `bookings.estimated_hours` → `DECIMAL(5,2) NULL`
- [ ] Add `service_extras` table: `id`, `service_id` (FK → `services.id`), `name`, `suggested_cost` (DECIMAL(10,2)), `is_active` (BOOL default true), timestamps
- [ ] Add `booking_extras` table: `id`, `booking_id` (FK → `bookings.id`), `service_extra_id` (FK → `service_extras.id`, nullable — null when custom), `name`, `cost` (DECIMAL(10,2)), `source` (ENUM: `catalog`, `custom`), timestamps
- [ ] Stop treating `bookings.price` as a directly-writable/user-input column — it becomes a cached/computed value, written server-side only, never accepted from client input. If `service_id` is null on old rows, leave existing `price` values untouched (they're historical record, not something to recompute retroactively).
- [ ] Rename `earnings` table → `booking_quotes`. Drop `platform_fee` and `net_amount` columns (no fees exist anymore). Rename `gross_amount` → `total_amount` for clarity now that there's no "gross vs net" distinction. Keep `worker_id`, `booking_id`, `paid_at` as-is — `paid_at` can stay for now as a nullable "job considered settled off-platform" marker if that's how it's currently used; confirm with existing app code before dropping it.
- [ ] Add app-level (not DB-level, since it spans two backends) validation: worker `hourly_rate` and `provider_services.custom_price` must be ≥ the relevant service's `base_price`. Write this as a shared validation rule description both Laravel and Express implement identically.

Write migrations for Laravel. If Express manages its own schema/migrations separately, mirror the same changes there — flag if the two backends currently share one database or maintain separate ones (the dump suggests one shared MariaDB database, but confirm the Express `.env`/connection config before assuming this).

### 2. Laravel Backend (`ClientController::storeBooking` and related)

- [ ] Accept `service_id` (required, FK to `services.id`), `estimated_hours`, `extras[]` (each with either `service_extra_id` or a `name`+`cost` pair for custom) from the client request. `service_category` free text is no longer the source of truth — if the client currently derives it from a text field, replace that flow with a service picker bound to `services`.
- [ ] Resolve the effective rate for the booking as: `provider_services.custom_price` for that `(user_id, service_id)` pair if set, else `worker_profiles.hourly_rate`
- [ ] Server-side, compute price as `rate × hours + sum(extras.cost)` — never trust a client-submitted total
- [ ] Remove `price` from validation rules; remove any code path that lets a raw price be set directly
- [ ] On booking completion, write a `booking_quotes` record with the computed breakdown (not just a total — store enough detail to reconstruct the breakdown later, e.g. rate used, hours, extras subtotal)
- [ ] Remove any remaining platform fee / commission calculation
- [ ] Existing bookings with `service_id = NULL` (all current rows) should continue to display their stored `price` as-is; don't attempt to recompute or block them from being read/updated by unrelated flows (status changes, messages, disputes, etc.) just because pricing data is incomplete

### 3. Express Backend (`server.ts`)

- [ ] Mirror the same computation logic as Laravel exactly — same formula, same rounding behavior (decide and document a rounding rule, e.g. round to nearest ₱1, since floating point on currency is a common bug source)
- [ ] Remove hardcoded platform fee logic around the referenced line (~408) entirely
- [ ] Accept `hours` + `extras` instead of a raw `price` field
- [ ] Match Laravel's validation rules (extract shared rules into a doc/comment both sides reference, so they don't drift apart over time)

### 4. Mobile App (Expo / React Native)

- [ ] `app/modal/booking.tsx`: replace any free-text service/category input with a picker bound to `services` (this now drives `service_id`, which pricing depends on); add hours input; add extras section (catalog checkboxes/list + "add custom extra" with name+cost fields, visually flagged as unverified); remove any free-text price field; show live-updating breakdown as the user fills the form
- [ ] `app/worker/[id].tsx`: show `hourly_rate` prominently; show `custom_price` when set for a given service, and make clear which one applies
- [ ] `app/(tabs)/bookings.tsx`: show the full breakdown per booking, not just a total
- [ ] Standardize currency formatting across every file to `₱` prefix with no space (e.g. `₱450`, not `PHP 450` or `₱ 450`) — grep the whole app for `PHP` and `₱` to catch every instance

### 5. Admin Panel (Laravel Blade)

- [ ] `admin/services/create.blade.php` and `edit.blade.php`: make `base_price` required; relabel field to "Minimum Hourly Rate (₱)"
- [ ] `admin/services/index.blade.php`: show `base_price` as its own visible column
- [ ] Add a `service_extras` admin CRUD view (list/create/edit/delete) under the relevant service — this wasn't in the original plan but is required now that extras have an admin-managed catalog
- [ ] `admin/provider_services/index.blade.php`: add inline edit for `custom_price`, with validation that it can't go below the service's `base_price`
- [ ] Worker profiles admin view/edit: add `hourly_rate` field
- [ ] Rename any "Earnings" nav item, view, or label to "Quotes" and point it at `booking_quotes`

---

## Explicitly Out of Scope

Do not add any of the following, even if it seems like a natural extension:
- Payment or money handling of any kind
- Platform fee or commission deduction
- Admin markup on displayed prices
- Discount or coupon system

## Acceptance Checklist

- [ ] No screen anywhere lets a client or worker directly type a final price — only a selected service, hours, and extras feed the calculation
- [ ] No new booking can be created without a real `service_id` — the old free-text `service_category` flow is gone from the creation path
- [ ] Historical bookings (`service_id = NULL`) still display and function normally everywhere except pricing recalculation
- [ ] Laravel and Express produce identical totals for identical inputs (write a quick test with the same booking payload against both)
- [ ] `base_price` and `hourly_rate` are enforced as required at both the DB and form level
- [ ] `custom_price` / `hourly_rate` below a service's `base_price` is rejected with a clear error, in both backends
- [ ] Every price display in the mobile app shows a breakdown, not a bare total
- [ ] Custom (free-text) extras are visually distinguishable from catalog extras everywhere they appear
- [ ] No `₱`/`PHP` inconsistency remains anywhere in the mobile app (grep confirms)
- [ ] No references to "platform fee," "commission," or "net earnings" remain in either backend
- [ ] `earnings` table/model/UI fully renamed to `booking_quotes` with no dangling references
  


  payment module
  