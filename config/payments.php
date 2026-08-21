<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Payment Gateways
    |--------------------------------------------------------------------------
    | Each gateway must map to its service key used in PaymentFactory.
    | The label is what will appear in the checkout dropdown.
    */

    'gateways' => [
        'stripe' => 'Stripe (Invoice)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Currencies
    |--------------------------------------------------------------------------
    | Real (fiat) currencies customers can pay with. Each key must be a
    | currency code the selected gateway accepts.
    */

    'currencies' => [
        'USD' => 'US Dollar ($)',
        'EUR' => 'Euro (€)',
    ],

];
