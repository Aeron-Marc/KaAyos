<?php

return [
    'platform_fee_percent' => env('KAAYOS_PLATFORM_FEE_PERCENT', 10),
    'booking_expiry_hours' => env('KAAYOS_BOOKING_EXPIRY_HOURS', 24),
    'max_concurrent_jobs'  => env('KAAYOS_MAX_CONCURRENT_JOBS', 3),
];
