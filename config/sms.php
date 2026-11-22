<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Gateway
    |--------------------------------------------------------------------------
    |
    | Local/demo driver writes outgoing messages to the configured log channel.
    | Swap the binding in SmsServiceProvider when a real provider is chosen.
    |
    */

    'gateway' => env('SMS_GATEWAY', 'log') ?: 'log',

    'log_channel' => env('SMS_LOG_CHANNEL', 'single') ?: 'single',

];
