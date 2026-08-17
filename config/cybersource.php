<?php
/**
 * Visa card acceptance via CyberSource Secure Acceptance Hosted Checkout.
 * CyberSource is Visa's payment-acceptance platform on developer.visa.com.
 *
 * Sign up at https://developer.visa.com (CyberSource / Visa Acceptance
 * Solutions) to get sandbox credentials, then set them in .env:
 *   CYBERSOURCE_MODE=sandbox
 *   CYBERSOURCE_ACCESS_KEY=...
 *   CYBERSOURCE_PROFILE_ID=...
 *   CYBERSOURCE_SECRET_KEY=...
 */

return [
    'mode' => env('CYBERSOURCE_MODE', 'sandbox'), // sandbox|production

    'access_key' => env('CYBERSOURCE_ACCESS_KEY'),
    'profile_id' => env('CYBERSOURCE_PROFILE_ID'),
    'secret_key' => env('CYBERSOURCE_SECRET_KEY'),

    'endpoints' => [
        'sandbox'    => 'https://testsecureacceptance.cybersource.com/pay',
        'production' => 'https://secureacceptance.cybersource.com/pay',
    ],
];
