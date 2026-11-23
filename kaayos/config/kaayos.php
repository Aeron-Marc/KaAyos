<?php

return [
    // Platform service fee tracked by the admin dashboard. Settlements still happen off-platform.
    'platform_fee_percent'   => env('KAAYOS_PLATFORM_FEE_PERCENT', 10),

    /*
    |--------------------------------------------------------------------------
    | Price guardrails for service pricing
    |--------------------------------------------------------------------------
    |
    | Default minimum and maximum prices applied when a service does not have
    | explicit price_min / price_max values set. These prevent unrealistic
    | pricing such as $0.00 listings or extreme outliers.
    */
    'price_guardrails' => [
        'min' => env('KAAYOS_PRICE_GUARDRAIL_MIN', 50.00),
        'max' => env('KAAYOS_PRICE_GUARDRAIL_MAX', 50000.00),
    ],

    /*
    |--------------------------------------------------------------------------
    | Down payment configuration
    |--------------------------------------------------------------------------
    |
    | Controls the optional down payment / partial payment flow for bookings.
    | The min percent is the recommended minimum down payment as a percentage
    | of the total booking price. Enforcement is opt-in per deployment.
    */
    'down_payment_min_percent' => env('KAAYOS_DOWNPAYMENT_MIN_PERCENT', 50),
    'down_payment_enforced'    => env('KAAYOS_DOWNPAYMENT_ENFORCED', false),
    'booking_expiry_hours'   => env('KAAYOS_BOOKING_EXPIRY_HOURS', 24),
    'max_concurrent_jobs'    => env('KAAYOS_MAX_CONCURRENT_JOBS', 3),
    'no_show_minutes'        => env('KAAYOS_NO_SHOW_MINUTES', 60),
    'default_location'       => env('KAAYOS_DEFAULT_LOCATION', 'Tuy, Batangas'),

    /*
    |--------------------------------------------------------------------------
    | Job Tracking Toggle
    |--------------------------------------------------------------------------
    |
    | When enabled, workers can advance jobs through status stages
    | (New → Accepted → En Route → In Progress → Completed) and clients
    | can see real-time status updates. When disabled, only basic booking
    | flow is available and status transition UI is hidden.
    */
    'job_tracking_enabled' => env('KAAYOS_JOB_TRACKING_ENABLED', true),

    /*
    | Registration rate limiting (attempts per hour)
    |
    | The per-IP ceiling is intentionally generous so that creating a client
    | account cannot block creating a worker account from the same network.
    | The per-email limit stays tight to prevent repeated sign-up abuse for a
    | single address.
    */
    'registration_ip_hourly_limit'    => env('KAAYOS_REGISTRATION_IP_HOURLY_LIMIT', 10),
    'registration_email_hourly_limit' => env('KAAYOS_REGISTRATION_EMAIL_HOURLY_LIMIT', 3),

    /*
    | Chatbot (AI Assistant) configuration
    | Supported providers: 'openai', 'gemini', or 'openrouter'
    */
    'chatbot_provider' => env('CHATBOT_PROVIDER', 'openai'),
    'chatbot_api_key'  => env('CHATBOT_API_KEY', ''),
    'chatbot_model'    => env('CHATBOT_MODEL', 'gpt-4o-mini'),
    'openrouter_site'  => env('OPENROUTER_SITE_URL', ''),
    'openrouter_name'  => env('OPENROUTER_SITE_NAME', 'KaAyos'),

    /*
    | ML Service (Python microservice for worker matching)
    */
    'ml_service_url' => env('ML_SERVICE_URL', 'http://127.0.0.1:8001'),
    'ml_api_key'     => env('ML_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Client Identity Verification
    |--------------------------------------------------------------------------
    |
    | Controls the client identity verification module (government ID + selfie
    | review). Enforcement is opt-in per client via KAAYOS_CLIENT_IDENTITY_ENFORCED
    | and can be deferred with KAAYOS_CLIENT_IDENTITY_ENFORCE_FROM (grace date).
    | No ID numbers, OCR, face templates, or biometrics are collected.
    */
    'client_identity' => [
        'enforced'        => env('KAAYOS_CLIENT_IDENTITY_ENFORCED', true),
        'enforce_from'    => env('KAAYOS_CLIENT_IDENTITY_ENFORCE_FROM'),
        'disk'            => 'identity',
        'max_id_kb'       => env('KAAYOS_CLIENT_IDENTITY_MAX_ID_KB', 5120),
        'max_selfie_kb'   => env('KAAYOS_CLIENT_IDENTITY_MAX_SELFIE_KB', 5120),
        'minimum_age'     => env('KAAYOS_CLIENT_IDENTITY_MIN_AGE', 18),
        'retention_days'  => env('KAAYOS_CLIENT_IDENTITY_RETENTION_DAYS', 365),
        'purge_after_days'=> env('KAAYOS_CLIENT_IDENTITY_PURGE_AFTER_DAYS', 30),
        'notice_version'  => '2026-10-28',
        'id_types'        => [
            'philsys'        => 'PhilSys / National ID',
            'passport'       => 'Passport',
            'drivers_license'=> "Driver's License",
            'umid'           => 'UMID',
            'other'          => 'Other Government ID',
        ],
    ],

];
