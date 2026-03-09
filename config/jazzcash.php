<?php

return [
    'merchant_id' => env('JAZZCASH_MERCHANT_ID', ''),
    'password' => env('JAZZCASH_PASSWORD', ''),
    'integerity_salt' => env('JAZZCASH_INTEGERITY_SALT', ''),
    
    // Don't use route() helper here, use a string or env
    'return_url' => env('JAZZCASH_RETURN_URL', '/jazzcash/callback'),
    'ipn_url' => env('JAZZCASH_IPN_URL', '/jazzcash/ipn'),
    
    'environment' => env('JAZZCASH_ENVIRONMENT', 'sandbox'),
    
    'endpoints' => [
        'sandbox' => 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/',
        'production' => 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/'
    ],
    
    'currency' => 'PKR',
    'language' => 'EN',
];