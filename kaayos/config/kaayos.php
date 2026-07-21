<?php

return [
    'platform_fee_percent'   => env('KAAYOS_PLATFORM_FEE_PERCENT', 10),
    'booking_expiry_hours'   => env('KAAYOS_BOOKING_EXPIRY_HOURS', 24),
    'max_concurrent_jobs'    => env('KAAYOS_MAX_CONCURRENT_JOBS', 3),
    'no_show_minutes'        => env('KAAYOS_NO_SHOW_MINUTES', 60),
    'default_location'       => env('KAAYOS_DEFAULT_LOCATION', 'Tuy, Batangas'),

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
    'ml_service_url' => env('ML_SERVICE_URL', 'http://127.0.0.1:8000'),
];
