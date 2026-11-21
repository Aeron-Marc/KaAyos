# Admin Dashboard Requirements

## Purpose

The admin dashboard is a decision-support surface for platform operators. It answers four questions, in order:

1. What needs action now?
2. Is platform performance healthy for the selected period?
3. Where is demand coming from?
4. Which specific records explain the result?

The dashboard is not a decorative analytics page. Every visible element must support one of those decisions or provide necessary context for them.

## Audience and decisions

| Audience | Decision |
| --- | --- |
| Admin / operations | Which disputes, verifications, stuck bookings, and unassigned bookings must be handled first? |
| Admin / operations | Are completion and cancellation rates inside target for the selected period? |
| Admin / finance | How much platform service fees, total transaction value, and platform revenue were generated? |
| Admin / operations | Is current workload growing or clearing? |
| Admin / operations | Which booking, dispute, or verification record explains a number? |

## Reporting scopes

The dashboard deliberately uses two scopes. They must be labelled so the page never presents contradictory numbers.

### Current scope: Needs action now

- Unresolved disputes: disputes with status `open` or `under_review`.
- Pending verifications: distinct workers with at least one `pending` worker document.
- Stuck bookings: bookings in `accepted`, `en_route`, or `in_progress` whose last real status transition is older than the configured threshold.
- Unassigned bookings: bookings still in `new` older than the configured threshold.

These are live workload counts. They are **not** filtered by the performance period. The page states this explicitly.

### Performance scope: selected period

- Booking counts, status splits, top categories, and the demand trend use bookings **created** in the period.
- Completion, cancellation, and decline rates use **terminal outcomes** in the period. The denominator is the sum of completed, cancelled, and declined outcomes, so the three rates reconcile to 100%.
- Completion counts use `completed_at`.
- Cancellation counts use `cancelled_at`.
- Decline counts use `declined_at`.
- Platform service fees use `price` on completed bookings multiplied by the configured `kaayos.platform_fee_percent`, counted by `completed_at`.
- Tips use `tip_amount` on completed bookings, counted by `completed_at`.
- Total transaction value is completed booking value plus tips.
- Platform revenue is equal to platform service fees.
- Individual worker service prices and earnings are intentionally excluded from the dashboard.
- Current active workload and awaiting acceptance are current stocks and are presented in the supporting measures, not as period performance.

## Formulas

```text
completion_rate = completed outcomes / (completed + cancelled + declined outcomes) * 100
cancellation_rate = cancelled outcomes / (completed + cancelled + declined outcomes) * 100
decline_rate = declined outcomes / (completed + cancelled + declined outcomes) * 100
platform_service_fees = SUM(price of completed bookings) * platform_fee_percent / 100
total_transaction_value = SUM(price of completed bookings) + SUM(tips of completed bookings)
platform_revenue = platform_service_fees
```

Rates are rounded to the nearest whole percentage point for display. Rates must not be calculated against bookings created in the period; that would mix two date bases.

## Targets

Configured in `config/dashboard.php`:

- Minimum completion rate: `80%`.
- Maximum cancellation rate: `20%`.
- Stuck booking age: `24 hours`.
- Unassigned booking age: `12 hours`.
- Needs-action target: zero open items.

Targets are shown as text next to the number. Colour is supplementary, never the only signal.

## Dashboard hierarchy

### Level 1: Needs action now

- Largest visual priority on the page.
- Overall actionable-item count and target of zero.
- Four action types with live counts and oldest age.
- A prioritised queue of the oldest items across all action types.
- Queue rows link directly to the record or filtered list.
- Queue sorting and CSV export apply to the rows currently shown.
- Empty state confirms that there is nothing waiting.

### Level 2: Performance

- Six primary KPIs only: completion rate, cancellation rate, bookings created, platform service fees, total transaction value, platform revenue.
- Financial KPIs must not expose individual worker prices or earnings.
- Each KPI shows its value, period scope, target where applicable, and previous-period comparison.
- Completion and cancellation drill-downs use terminal-outcome dates, not creation dates.
- Trend chart shows bookings created (count) and completed value (currency) with explicit units.
- Status distribution uses a comparison bar rather than a large donut, because there are eight statuses.
- Status rows link to bookings filtered by creation date.

### Level 3: Supporting measures and records

- Supporting measures provide context without competing with Level 1 and Level 2.
- Recent bookings provide the latest records in the selected period.
- Top categories show demand mix and are not treated as a performance target.
- Tables scroll horizontally inside their card on narrow screens; the page itself is allowed to scroll vertically.

## Drill-down rules

| Element | Destination | Date basis |
| --- | --- | --- |
| Unassigned / stuck booking | Booking detail | Current |
| Dispute | Dispute detail | Current |
| Pending verification | Verification detail | Current |
| Completion KPI | Completed bookings | Outcome date |
| Cancellation KPI | Cancelled bookings | Outcome date |
| Platform service fees / total transaction value / platform revenue KPI | Completed bookings | Outcome date |
| Bookings created KPI | Booking list | Creation date |
| Status row | Booking list filtered by status | Creation date |
| Recent booking | Booking detail | Creation date |

The booking list accepts `date_basis=outcome` to filter terminal outcomes by their actual terminal timestamp. This keeps dashboard drill-downs reconciled.

## Report view

The existing `/admin/reports` page is the detailed report view. It already supports report types, date ranges, previews, CSV export, and Excel export. The dashboard links to it with the selected period instead of duplicating a report engine.

## Limitations

- The dashboard reflects the application database, not external payment settlement.
- Platform service fees are derived from the configured percentage of completed booking value; they are not a stored per-booking charge.
- Total transaction value is completed booking value plus tips; it is not a payout or profit figure.
- Platform revenue equals platform service fees; it is not a separate settlement ledger.
- Individual worker prices and earnings are not exposed on the dashboard, worker list, worker detail, provider-services page, or worker-performance report.
- Free-text booking categories are normalised through `BookingCategoryResolver`; some legacy values may fall into `Uncategorized`.
- Current workload is a live snapshot and can change between page loads.
- The needs-action queue shows the oldest configured number of items, not every open record; links and export state the displayed count.
- Browser validation uses seeded or synthetic records where production data is unavailable.

## Acceptance criteria

- [ ] Admin can identify the highest-priority current item without scanning every card.
- [ ] Current-scope and period-scope numbers are clearly separated.
- [ ] Completion and cancellation KPI drill-downs return counts on the same date basis.
- [ ] Target status is visible as text, not only colour.
- [ ] All five status colours remain consistent between badges, bars, and charts.
- [ ] Dashboard financial cards show only platform service fees, total transaction value, and platform revenue; no individual worker price or earnings column is visible.
- [ ] Queue sorting and CSV export use the same visible rows.
- [ ] Empty states are useful and do not invent data.
- [ ] Dashboard is usable at 1366x768 with normal vertical scrolling.
- [ ] Tables scroll internally on mobile; the page does not overflow horizontally.
