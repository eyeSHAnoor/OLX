<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'shopify' => [
        'api_key' => env('SHOPIFY_API_KEY'),
        'client_secret' => env('SHOPIFY_API_SECRET'),
    ],

    'devtech' => [
        'base_url' => env('DEVTECH_BASE_URL'),
        'provider_id' => env('DEVTECH_PROVIDER'),
        'api_key' => env('DEVTECH_API_KEY'),
    ],

    'rapisapi' => [
        'base_url' => env('RAPISAPI_URL', 'https://tecdoc-catalog.p.rapidapi.com/articles/article-number-details'),
        'key' => env('RAPISAPI_KEY'),
        'host' => env('RAPISAPI_HOST'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],


    //    'whatsapp' => [
//        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
//        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
//    ],

];
