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

];
