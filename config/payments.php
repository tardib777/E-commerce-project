<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Payment Gateways
    |--------------------------------------------------------------------------
    | Each gateway must map to its service key used in PaymentFactory.
    | The label is what will appear on the checkout page.
    */

    'gateways' => [
        'stripe' => 'Credit / Debit Card (Visa, Mastercard)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Currencies
    |--------------------------------------------------------------------------
    | Product prices are stored in USD, so checkout charges in USD only.
    | (Offering another currency without converting the amount would
    | charge the same number in that currency.)
    */

    'currencies' => [
        'USD' => 'US Dollar ($)',
    ],

];
