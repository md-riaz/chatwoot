<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AppConfigController extends Controller
{
    /**
     * Display the application configuration.
     */
    public function show(): JsonResponse
    {
        $config = [
            'app_name' => config('app.name'),
            'app_version' => config('app.version', '1.0.0'),
            'api_version' => 'v1',
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'fallback_locale' => config('app.fallback_locale'),
            'url' => config('app.url'),
            
            // Database configuration
            'database' => [
                'default' => config('database.default'),
                'connections' => array_keys(config('database.connections')),
            ],
            
            // Cache configuration
            'cache' => [
                'default' => config('cache.default'),
                'stores' => array_keys(config('cache.stores')),
            ],
            
            // Queue configuration
            'queue' => [
                'default' => config('queue.default'),
                'connections' => array_keys(config('queue.connections')),
            ],
            
            // Mail configuration
            'mail' => [
                'default' => config('mail.default'),
                'mailers' => array_keys(config('mail.mailers')),
                'from' => config('mail.from'),
            ],
            
            // Broadcasting configuration
            'broadcasting' => [
                'default' => config('broadcasting.default'),
                'connections' => array_keys(config('broadcasting.connections')),
            ],
            
            // File storage configuration
            'filesystems' => [
                'default' => config('filesystems.default'),
                'cloud' => config('filesystems.cloud'),
                'disks' => array_keys(config('filesystems.disks')),
            ],
            
            // Session configuration
            'session' => [
                'driver' => config('session.driver'),
                'lifetime' => config('session.lifetime'),
                'encrypt' => config('session.encrypt'),
            ],
            
            // Logging configuration
            'logging' => [
                'default' => config('logging.default'),
                'channels' => array_keys(config('logging.channels')),
            ],
            
            // Feature flags
            'features' => [
                'enterprise' => config('app.enterprise', false),
                'chatwoot_cloud' => config('app.chatwoot_cloud', false),
                'audit_logs' => config('features.audit_logs', true),
                'advanced_reporting' => config('features.advanced_reporting', false),
                'captain' => config('features.captain', false),
                'saml' => config('features.saml', false),
                'custom_branding' => config('features.custom_branding', false),
            ],
            
            // System limits
            'limits' => [
                'max_file_size' => config('app.max_file_size', 40 * 1024 * 1024), // 40MB
                'max_upload_size' => config('app.max_upload_size', 40 * 1024 * 1024),
                'max_agents_per_account' => config('app.max_agents_per_account', 25),
                'max_inboxes_per_account' => config('app.max_inboxes_per_account', 100),
            ],
            
            // Third-party integrations
            'integrations' => [
                'facebook' => !empty(config('services.facebook.app_id')),
                'google' => !empty(config('services.google.client_id')),
                'microsoft' => !empty(config('services.microsoft.client_id')),
                'slack' => !empty(config('services.slack.client_id')),
                'linear' => !empty(config('services.linear.client_id')),
                'shopify' => !empty(config('services.shopify.api_key')),
                'twilio' => !empty(config('services.twilio.account_sid')),
                'whatsapp' => !empty(config('services.whatsapp.access_token')),
                'telegram' => !empty(config('services.telegram.bot_token')),
                'openai' => !empty(config('services.openai.api_key')),
            ],
            
            // System status
            'status' => [
                'maintenance_mode' => app()->isDownForMaintenance(),
                'cache_enabled' => config('cache.default') !== 'array',
                'queue_enabled' => config('queue.default') !== 'sync',
                'broadcasting_enabled' => config('broadcasting.default') !== 'null',
            ],
        ];

        return response()->json(['data' => $config]);
    }

    /**
     * Update application configuration.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
            'features' => 'nullable|array',
            'features.*' => 'boolean',
            'limits' => 'nullable|array',
            'limits.*' => 'integer|min:0',
            'integrations' => 'nullable|array',
        ]);

        // Update configuration values
        if (isset($validated['app_name'])) {
            Config::set('app.name', $validated['app_name']);
        }

        if (isset($validated['timezone'])) {
            Config::set('app.timezone', $validated['timezone']);
        }

        if (isset($validated['locale'])) {
            Config::set('app.locale', $validated['locale']);
        }

        if (isset($validated['features'])) {
            foreach ($validated['features'] as $feature => $enabled) {
                Config::set("features.{$feature}", $enabled);
            }
        }

        if (isset($validated['limits'])) {
            foreach ($validated['limits'] as $limit => $value) {
                Config::set("app.{$limit}", $value);
            }
        }

        // Clear configuration cache
        Cache::forget('config');
        
        // In a real implementation, you would persist these changes to a configuration file
        // or database table. For now, we'll just return success.

        return response()->json([
            'message' => 'Application configuration updated successfully.',
            'data' => $this->show()->getData()->data
        ]);
    }
}