<?php
/**
 * NOWPayment Setting & API Credentials
 * Created by Raza Mehdi <srmk@outlook.com>.
 */

return [
    "live"=> [
        'api_key' => env('NOWPAYMENT_KEY'),
        'public_key' =>env('NOWPAYMENT_PUBLIC_KEY'),
        'IPN_key' => env('NOWPAYMENT_IPN_KEY')
    ],
    "sandbox"=> [
        'api_key' => env('NOWPAYMENT_SANDBOX_KEY'),
        'public_key' =>env('NOWPAYMENT_SANDBOX_PUBLIC_KEY'),
        'IPN_key' => env('NOWPAYMENT_SANDBOX_IPN_KEY'),
        'email' => env('NOWPAYMENT_SANDBOX_EMAIL'),
        'password' => env('NOWPAYMENT_SANDBOX_PASSWORD'),
    ]
];
