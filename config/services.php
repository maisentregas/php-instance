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

    'raia_drogasil' => [
        'internal_access_token' => env('INTERNAL_ACCESS_TOKEN')
    ],

    # Default keys are from homolog
    'iza_intermittent' => [
        'api_url' => env('IZA_API_URL', 'https://intermittent-web-api.hml.iza.com.vc/api'),
        'user_key' => env('IZA_USER_KEY', 'be8984e0-2c12-4b7f-bbf8-b2ed0e86292c'),
        'user_secret' => env('IZA_USER_SECRET', 'ngmfVFqixx4T6a9H9yq02fjuaepeH+gKdxtzzDRij1xSwJFCgWHKkzJfxXRQ7b7P')
    ]
];
