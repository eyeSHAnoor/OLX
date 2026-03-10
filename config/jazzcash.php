<?php

return [
    'merchant_id' => env('JAZZCASH_MERCHANT_ID', ''),
    'password' => env('JAZZCASH_PASSWORD', ''),
    'integerity_salt' => env('JAZZCASH_INTEGERITY_SALT', ''),
    
    'return_url' => env('JAZZCASH_RETURN_URL', '/jazzcash/callback'),
    'ipn_url' => env('JAZZCASH_IPN_URL', '/jazzcash/ipn'),
    
    'environment' => env('JAZZCASH_ENVIRONMENT', 'sandbox'),
    
    'endpoints' => [
        'sandbox' => 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/Payment/DoMWalletTransaction',
        'production' => 'https://payments.jazzcash.com.pk/ApplicationAPI/API/Payment/DoMWalletTransaction',
    ],
    
    'currency' => 'PKR',
    'language' => 'EN',
];