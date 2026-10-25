<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard targets
    |--------------------------------------------------------------------------
    | completion_rate_min  => minimum healthy completion rate in %
    | cancellation_rate_max => maximum acceptable cancellation rate %
    | null means "not configured yet".
    */
    'targets' => [
        'completion_rate_min' => 80,
        'cancellation_rate_max' => 20,
        'stuck_booking_hours' => 24,
        'unassigned_booking_hours' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default period shown when no ?period= is given
    |--------------------------------------------------------------------------
    | today | 7d | 30d | this_month | all_time
    */
    'default_period' => 'this_month',

    /*
    |--------------------------------------------------------------------------
    | Allowed periods + the time-bucket unit used by the trend chart
    |--------------------------------------------------------------------------
    | hour for today, day for 7d/30d/this_month, month for all_time
    */
    'periods' => [
        'today' => ['unit' => 'hour'],
        '7d' => ['unit' => 'day'],
        '30d' => ['unit' => 'day'],
        'this_month' => ['unit' => 'day'],
        'all_time' => ['unit' => 'month'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | Dashboard aggregates and sidebar badges are cached briefly. Set to 0 to
    | disable caching (useful while reconciling data).
    */
    'cache_ttl' => 60,

    /*
    |--------------------------------------------------------------------------
    | Needs-action queue
    |--------------------------------------------------------------------------
    | Maximum number of oldest actionable records shown on the dashboard and
    | included in the queue CSV export.
    */
    'action_queue_limit' => 8,

    /*
    |--------------------------------------------------------------------------
    | Category aliases for legacy bookings.service_category values
    |--------------------------------------------------------------------------
    | bookings.service_category is free text. The dashboard maps it to a real
    | category automatically (services.name -> service_categories.name, then an
    | exact category-name match). Anything unmapped is grouped under
    | "Uncategorized". Add explicit overrides here when needed, e.g.:
    | 'Completed Job'        => null,      // force into Uncategorized
    | 'Completed Painting Job' => 'Painting',
    */
    'category_aliases' => [
        // 'Completed Job' => null,
    ],

];
