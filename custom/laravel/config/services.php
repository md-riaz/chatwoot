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
        'client_id' => env('SLACK_CLIENT_ID'),
        'client_secret' => env('SLACK_CLIENT_SECRET'),
        'redirect_uri' => env('SLACK_REDIRECT_URI'),
    ],

    'shopify' => [
        'client_id' => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
        'redirect_uri' => env('SHOPIFY_REDIRECT_URI'),
    ],

    'linear' => [
        'client_id' => env('LINEAR_CLIENT_ID'),
        'client_secret' => env('LINEAR_CLIENT_SECRET'),
        'redirect_uri' => env('LINEAR_REDIRECT_URI'),
    ],

    'dyte' => [
        'organization_id' => env('DYTE_ORGANIZATION_ID'),
        'api_key' => env('DYTE_API_KEY'),
    ],

    'tiktok' => [
        'app_id' => env('TIKTOK_APP_ID'),
        'secret' => env('TIKTOK_SECRET'),
        'verify_token' => env('TIKTOK_VERIFY_TOKEN'),
    ],

    'instagram' => [
        'app_id' => env('INSTAGRAM_APP_ID'),
        'app_secret' => env('INSTAGRAM_APP_SECRET'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'phone_number' => env('TWILIO_PHONE_NUMBER'),
    ],

];