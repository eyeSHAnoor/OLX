<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Mode
    |--------------------------------------------------------------------------
    |
    | This value determines whether you are in sandbox (testing) or production (live) mode.
    | Accepted values: 'sandbox' or 'production'
    |
    */
    'paymentmode' => env('JAZZCASH_PAYMENTMODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Credentials
    |--------------------------------------------------------------------------
    |
    | These values are provided by JazzCash when you register as a merchant.
    |
    */
    'merchant_id' => env('JAZZCASH_MERCHANT_ID', ''),
    'password' => env('JAZZCASH_PASSWORD', ''),
    'hash_key' => env('JAZZCASH_HASHKEY', ''), // Also called integrity salt
    'mpin' => env('JAZZCASH_MPIN', ''),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | The endpoints for JazzCash API in different environments.
    |
    */
    'production_url' => env('JAZZCASH_PRODUCTION_URL', 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/'),
    'sandbox_url' => env('JAZZCASH_SANDBOX_URL', 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/'),

    /*
    |--------------------------------------------------------------------------
    | Return URL
    |--------------------------------------------------------------------------
    |
    | The URL where JazzCash will redirect the customer after payment.
    |
    */
    'return_url' => env('JAZZCASH_RETURNURL', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency Code
    |--------------------------------------------------------------------------
    |
    | Default currency code for transactions (PKR for Pakistan Rupee).
    |
    */
    'currency_code' => 'PKR',

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | Default language for payment page (EN for English, UR for Urdu).
    |
    */
    'language' => 'EN',
];