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
        'mais_entregas_api_url' => env('MAIS_ENTREGAS_API_URL'),
        'internal_access_token' => env('INTERNAL_ACCESS_TOKEN')
    ],

    # Default keys are from homolog
    'iza_intermittent' => [
        'api_url' => env('IZA_API_URL', 'https://intermittent-web-api.hml.iza.com.vc/api'),
        'user_key' => env('IZA_USER_KEY', '2369a043-3644-4e8e-b6df-0073a1e433a0'),
        'user_secret' => env('IZA_USER_SECRET', 'laf1pNYI4okp5ogFvQq2XUHRmvWNkg0WYG0R5/Xutnzh+Ddyrc63bmMYEDLof4AP')
    ]
];
