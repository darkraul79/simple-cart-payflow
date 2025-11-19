<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Simple Cart & PayFlow Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file allows you to customize both Cartify and Payflow
    | packages from a single location for convenience.
    |
    */

    'cart' => [
        // Cartify settings (override cartify.php if needed)
        'tax_rate' => env('CARTIFY_TAX_RATE', 0.21),
        'currency' => env('CARTIFY_CURRENCY', 'EUR'),
        'currency_symbol' => env('CARTIFY_CURRENCY_SYMBOL', '€'),
    ],

    'payment' => [
        // Payflow settings (override payflow.php if needed)
        'default_gateway' => env('PAYMENT_GATEWAY_DEFAULT', 'redsys'),
    ],

    'integration' => [
        // Future integration settings between cart and payment
        'auto_clear_cart_on_success' => env('AUTO_CLEAR_CART_ON_SUCCESS', true),
        'store_cart_with_order' => env('STORE_CART_WITH_ORDER', true),
    ],
];
