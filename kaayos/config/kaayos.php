<?php

return [
    'platform_fee_percent' => env('KAAYOS_PLATFORM_FEE_PERCENT', 10),

    /*
    | Chatbot (AI Assistant) configuration
    | Supported providers: 'openai' or 'gemini'
    */
    'chatbot_provider' => env('CHATBOT_PROVIDER', 'openai'),
    'chatbot_api_key'  => env('CHATBOT_API_KEY', ''),
    'chatbot_model'    => env('CHATBOT_MODEL', 'gpt-4o-mini'),
];
