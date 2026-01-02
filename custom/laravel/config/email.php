<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the email system including
    | branding, domains, and template settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Brand Configuration
    |--------------------------------------------------------------------------
    |
    | These values control the branding in email templates. They can be
    | overridden by global configuration settings in the database.
    |
    */

    'brand' => [
        'name' => env('EMAIL_BRAND_NAME', env('APP_NAME', 'Chatwoot')),
        'url' => env('EMAIL_BRAND_URL', env('APP_URL', 'https://chatwoot.com')),
        'logo_url' => env('EMAIL_BRAND_LOGO_URL', null),
        'support_email' => env('EMAIL_SUPPORT_EMAIL', 'support@' . parse_url(env('APP_URL', 'https://chatwoot.com'), PHP_URL_HOST)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Domains
    |--------------------------------------------------------------------------
    |
    | Configuration for email-related domains used in the system.
    |
    */

    'domains' => [
        'portal' => env('EMAIL_PORTAL_DOMAIN', 'portals.' . strtolower(env('APP_NAME', 'chatwoot')) . '.com'),
        'reply' => env('EMAIL_REPLY_DOMAIN', parse_url(env('APP_URL', 'https://chatwoot.com'), PHP_URL_HOST)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for email template processing and rendering.
    |
    */

    'templates' => [
        'cache_ttl' => env('EMAIL_TEMPLATE_CACHE_TTL', 3600), // 1 hour
        'liquid_enabled' => env('EMAIL_LIQUID_ENABLED', true),
        'fallback_locale' => env('EMAIL_FALLBACK_LOCALE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bounce Handling
    |--------------------------------------------------------------------------
    |
    | Configuration for email bounce handling and contact management.
    |
    */

    'bounce' => [
        'max_soft_bounces' => env('EMAIL_MAX_SOFT_BOUNCES', 5),
        'soft_bounce_reset_days' => env('EMAIL_SOFT_BOUNCE_RESET_DAYS', 30),
        'auto_disable_on_hard_bounce' => env('EMAIL_AUTO_DISABLE_HARD_BOUNCE', true),
        'auto_disable_on_complaint' => env('EMAIL_AUTO_DISABLE_COMPLAINT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Threading Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for email threading and conversation management.
    |
    */

    'threading' => [
        'message_id_domain' => env('EMAIL_MESSAGE_ID_DOMAIN', parse_url(env('APP_URL', 'https://chatwoot.com'), PHP_URL_HOST)),
        'max_references' => env('EMAIL_MAX_REFERENCES', 10),
        'include_conversation_id' => env('EMAIL_INCLUDE_CONVERSATION_ID', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for email notifications.
    |
    */

    'notifications' => [
        'include_brand_in_subject' => env('EMAIL_INCLUDE_BRAND_IN_SUBJECT', true),
        'include_conversation_summary' => env('EMAIL_INCLUDE_CONVERSATION_SUMMARY', true),
        'max_message_preview_length' => env('EMAIL_MAX_MESSAGE_PREVIEW_LENGTH', 200),
    ],

];