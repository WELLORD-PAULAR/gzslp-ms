<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | The gateway is resolved through the PaymentGateway contract, so a real
    | provider can be added without changing the checkout controller or the
    | payment records. "dummy" approves every charge locally and is the only
    | driver shipped with this build.
    |
    */

    'gateway' => env('PAYMENT_GATEWAY', 'dummy') ?: 'dummy',

    'currency' => env('PAYMENT_CURRENCY', 'PHP') ?: 'PHP',

    'methods' => [
        'card' => 'Card (demo)',
    ],

];
