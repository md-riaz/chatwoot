<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the search functionality.
    | You can configure search behavior, performance settings, and feature flags.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Search Settings
    |--------------------------------------------------------------------------
    */
    'default_per_page' => env('SEARCH_DEFAULT_PER_PAGE', 15),
    'max_per_page' => env('SEARCH_MAX_PER_PAGE', 100),
    
    /*
    |--------------------------------------------------------------------------
    | Search Performance Settings
    |--------------------------------------------------------------------------
    */
    'time_window_months' => env('SEARCH_TIME_WINDOW_MONTHS', 3),
    'cache_ttl' => env('SEARCH_CACHE_TTL', 300), // 5 minutes
    
    /*
    |--------------------------------------------------------------------------
    | Search Feature Flags
    |--------------------------------------------------------------------------
    */
    'enable_gin_search' => env('SEARCH_ENABLE_GIN', true),
    'enable_advanced_search' => env('SEARCH_ENABLE_ADVANCED', false),
    'enable_search_cache' => env('SEARCH_ENABLE_CACHE', true),
    
    /*
    |--------------------------------------------------------------------------
    | Full-Text Search Settings
    |--------------------------------------------------------------------------
    */
    'fulltext' => [
        'postgresql' => [
            'language' => env('SEARCH_POSTGRES_LANGUAGE', 'english'),
            'use_gin_index' => env('SEARCH_USE_GIN_INDEX', true),
        ],
        'mysql' => [
            'mode' => env('SEARCH_MYSQL_MODE', 'BOOLEAN'),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Search Result Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'messages' => env('SEARCH_LIMIT_MESSAGES', 50),
        'conversations' => env('SEARCH_LIMIT_CONVERSATIONS', 25),
        'contacts' => env('SEARCH_LIMIT_CONTACTS', 25),
        'articles' => env('SEARCH_LIMIT_ARTICLES', 20),
    ],
];