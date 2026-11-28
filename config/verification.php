<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Account Verification Codes
    |--------------------------------------------------------------------------
    |
    | Six-digit, single-use codes delivered by email or SMS. A new request
    | invalidates any earlier unused code for the same channel.
    |
    */

    'code_ttl_minutes' => env('VERIFICATION_CODE_TTL_MINUTES', 10),

    'resend_seconds' => env('VERIFICATION_RESEND_SECONDS', 60),

    'max_attempts' => env('VERIFICATION_MAX_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | Delivery Gateway
    |--------------------------------------------------------------------------
    |
    | "http" posts each code to VERIFICATION_API_URL with the API key in the
    | Authorization and X-Api-Key headers. Use "log" for local development.
    |
    */

    'gateway' => env('VERIFICATION_GATEWAY', 'http') ?: 'http',

    'api_url' => env('VERIFICATION_API_URL'),

    'api_key' => env('VERIFICATION_API_KEY'),

    'from_email' => env('VERIFICATION_FROM_EMAIL'),

    'from_phone' => env('VERIFICATION_FROM_PHONE'),

    'timeout_seconds' => env('VERIFICATION_TIMEOUT_SECONDS', 10),

    'log_channel' => env('VERIFICATION_LOG_CHANNEL', 'single') ?: 'single',

];
