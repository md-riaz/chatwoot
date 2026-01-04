<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the translation functionality.
    | You can configure translation providers and their settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Translation Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default translation provider that will be used
    | by the translation service. You may set this to any of the providers
    | defined in the "providers" array below.
    |
    */
    'default' => env('TRANSLATION_PROVIDER', 'libre'),

    /*
    |--------------------------------------------------------------------------
    | Translation Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the translation providers for your application.
    | Each provider can have its own configuration options.
    |
    */
    'providers' => [
        'libre' => [
            'url' => env('LIBRE_TRANSLATE_URL', 'https://libretranslate.com/translate'),
            'api_key' => env('LIBRE_TRANSLATE_API_KEY'),
            'timeout' => env('LIBRE_TRANSLATE_TIMEOUT', 10),
        ],

        'google' => [
            'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
            'project_id' => env('GOOGLE_TRANSLATE_PROJECT_ID'),
        ],

        'deepl' => [
            'api_key' => env('DEEPL_API_KEY'),
            'url' => env('DEEPL_API_URL', 'https://api-free.deepl.com'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('TRANSLATION_CACHE_ENABLED', true),
        'ttl' => env('TRANSLATION_CACHE_TTL', 3600), // 1 hour
    ],
];