<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\InstallationConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display settings for a specific category.
     */
    public function category(string $category): JsonResponse
    {
        $cacheKey = "super_admin_settings_category_{$category}";
        
        $settings = Cache::remember($cacheKey, 300, function () use ($category) {
            $configGroups = InstallationConfig::getConfigGroups();
            
            // Check if category exists
            if (!isset($configGroups[$category])) {
                return [];
            }
            
            $configNames = $configGroups[$category];
            
            // Get settings for this category
            $categorySettings = InstallationConfig::whereIn('name', $configNames)->get();
            
            return $categorySettings->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'name' => $setting->name,
                    'value' => $setting->getTypeCastedValue(),
                    'locked' => $setting->locked,
                    'display_title' => $setting->display_title,
                    'description' => $setting->description,
                    'type' => $setting->type,
                    'options' => $setting->options,
                ];
            })->toArray();
        });

        return response()->json([
            'data' => $settings,
            'category' => $category,
            'count' => count($settings)
        ]);
    }

    /**
     * Display all installation settings.
     */
    public function index(): JsonResponse
    {
        $settings = Cache::remember('super_admin_settings', 300, function () {
            return InstallationConfig::all()->keyBy('name');
        });

        return response()->json(['data' => $settings]);
    }

    /**
     * Display settings grouped by category.
     */
    public function show(): JsonResponse
    {
        $settings = Cache::remember('super_admin_settings_grouped', 300, function () {
            $allSettings = InstallationConfig::all();
            
            // Use the predefined config groups from the model
            $configGroups = InstallationConfig::getConfigGroups();
            $grouped = [];
            
            // Initialize all groups
            foreach ($configGroups as $groupName => $configNames) {
                $grouped[$groupName] = [];
            }
            
            // Add a general group for settings that don't fit other categories
            $grouped['general'] = [];
            
            foreach ($allSettings as $setting) {
                $assigned = false;
                
                // Check which group this setting belongs to
                foreach ($configGroups as $groupName => $configNames) {
                    if (in_array($setting->name, $configNames)) {
                        $grouped[$groupName][] = [
                            'id' => $setting->id,
                            'name' => $setting->name,
                            'value' => $setting->getTypeCastedValue(),
                            'locked' => $setting->locked,
                            'display_title' => $setting->display_title,
                            'description' => $setting->description,
                            'type' => $setting->type,
                            'options' => $setting->options,
                        ];
                        $assigned = true;
                        break;
                    }
                }
                
                // If not assigned to any specific group, put in general
                if (!$assigned) {
                    $grouped['general'][] = [
                        'id' => $setting->id,
                        'name' => $setting->name,
                        'value' => $setting->getTypeCastedValue(),
                        'locked' => $setting->locked,
                        'display_title' => $setting->display_title,
                        'description' => $setting->description,
                        'type' => $setting->type,
                        'options' => $setting->options,
                    ];
                }
            }
            
            // Remove empty groups
            return array_filter($grouped, function($group) {
                return !empty($group);
            });
        });

        return response()->json(['data' => $settings]);
    }

    /**
     * Update multiple settings at once.
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $settings = $request->input('settings');
        $updated = [];
        $errors = [];

        foreach ($settings as $key => $value) {
            try {
                // Validate setting key format
                if (!$this->isValidSettingKey($key)) {
                    $errors[$key] = 'Invalid setting key format';
                    continue;
                }

                // Check if setting is locked
                $existingSetting = InstallationConfig::where('name', $key)->first();
                if ($existingSetting && $existingSetting->locked && !$request->input('force', false)) {
                    $errors[$key] = 'Setting is locked and cannot be modified';
                    continue;
                }

                // Update or create setting
                $setting = InstallationConfig::updateOrCreate(
                    ['name' => $key],
                    [
                        'serialized_value' => $this->serializeValue($value),
                        'locked' => $existingSetting ? $existingSetting->locked : false,
                    ]
                );

                $updated[$key] = $setting;
            } catch (\Exception $e) {
                $errors[$key] = 'Failed to update: ' . $e->getMessage();
            }
        }

        // Clear settings cache (including all category caches)
        $this->clearAllSettingsCache();

        $response = [
            'message' => 'Settings update completed',
            'updated' => count($updated),
            'errors' => count($errors),
        ];

        if (!empty($updated)) {
            $response['updated_settings'] = array_keys($updated);
        }

        if (!empty($errors)) {
            $response['error_details'] = $errors;
        }

        return response()->json($response, !empty($errors) ? 207 : 200);
    }

    /**
     * Create a new setting.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:installation_configs,name',
            'value' => 'required',
            'locked' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        if (!$this->isValidSettingKey($request->input('name'))) {
            return response()->json([
                'error' => 'Invalid setting key format',
            ], 422);
        }

        $setting = InstallationConfig::create([
            'name' => $request->input('name'),
            'serialized_value' => $this->serializeValue($request->input('value')),
            'locked' => $request->input('locked', false),
        ]);

        // Clear settings cache (including all category caches)
        $this->clearAllSettingsCache();

        return response()->json(['data' => $setting], 201);
    }

    /**
     * Delete a setting.
     */
    public function destroy(Request $request, string $name): JsonResponse
    {
        $setting = InstallationConfig::where('name', $name)->first();

        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        if ($setting->locked && !$request->input('force', false)) {
            return response()->json([
                'error' => 'Setting is locked and cannot be deleted',
            ], 403);
        }

        $setting->delete();

        // Clear settings cache (including all category caches)
        $this->clearAllSettingsCache();

        return response()->json(['message' => 'Setting deleted successfully']);
    }

    /**
     * Get available setting categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('super_admin_setting_categories', 600, function () {
            $settings = InstallationConfig::all();
            
            $categories = $settings->groupBy(function ($setting) {
                $parts = explode('_', $setting->name, 2);
                return $parts[0] ?? 'general';
            })->keys();

            return $categories->map(function ($category) {
                return [
                    'name' => $category,
                    'label' => ucfirst(str_replace('_', ' ', $category)),
                    'count' => InstallationConfig::where('name', 'like', $category . '_%')->count(),
                ];
            });
        });

        return response()->json(['data' => $categories]);
    }

    /**
     * Reset settings to default values.
     */
    public function reset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'array',
            'settings.*' => 'string',
            'confirm' => 'required|boolean|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $settingsToReset = $request->input('settings', []);
        $resetCount = 0;

        if (empty($settingsToReset)) {
            // Reset all non-locked settings
            $settings = InstallationConfig::where('locked', false)->get();
        } else {
            // Reset specific settings
            $settings = InstallationConfig::whereIn('name', $settingsToReset)
                ->where('locked', false)
                ->get();
        }

        foreach ($settings as $setting) {
            $defaultValue = $this->getDefaultValue($setting->name);
            if ($defaultValue !== null) {
                $setting->update(['serialized_value' => $this->serializeValue($defaultValue)]);
                $resetCount++;
            }
        }

        // Clear settings cache (including all category caches)
        $this->clearAllSettingsCache();

        return response()->json([
            'message' => "Reset {$resetCount} settings to default values",
            'reset_count' => $resetCount,
        ]);
    }

    /**
     * Refresh settings cache and reload from database.
     */
    public function refresh(): JsonResponse
    {
        // Clear all settings-related caches
        $this->clearAllSettingsCache();
        
        // Clear any other configuration caches
        Cache::forget('config');
        
        // Force reload settings from database
        $settings = InstallationConfig::all()->keyBy('name');
        
        // Warm up the cache with fresh data
        Cache::put('super_admin_settings', $settings, 300);
        
        return response()->json([
            'message' => 'Settings cache refreshed successfully',
            'settings_count' => $settings->count(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Validate setting key format.
     */
    private function isValidSettingKey(string $key): bool
    {
        // Allow alphanumeric, underscores, and dots
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_.]*$/', $key);
    }

    /**
     * Serialize value for storage.
     */
    private function serializeValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        return (string) $value;
    }

    /**
     * Get default value for a setting.
     */
    private function getDefaultValue(string $key): mixed
    {
        // Define default values for common settings
        $defaults = [
            'app_name' => config('app.name', 'ClearLine'),
            'app_url' => config('app.url', 'http://localhost'),
            'mail_from_address' => config('mail.from.address', 'noreply@example.com'),
            'mail_from_name' => config('mail.from.name', 'ClearLine'),
            'storage_driver' => config('filesystems.default', 'local'),
            'queue_driver' => config('queue.default', 'sync'),
            'cache_driver' => config('cache.default', 'file'),
            'session_driver' => config('session.driver', 'file'),
            'broadcast_driver' => config('broadcasting.default', 'null'),
        ];

        return $defaults[$key] ?? null;
    }

    /**
     * Clear all settings-related caches including category-specific caches.
     */
    private function clearAllSettingsCache(): void
    {
        Cache::forget('super_admin_settings');
        Cache::forget('super_admin_settings_grouped');
        Cache::forget('super_admin_setting_categories');
        
        // Clear all category caches
        $configGroups = InstallationConfig::getConfigGroups();
        foreach (array_keys($configGroups) as $categoryName) {
            Cache::forget("super_admin_settings_category_{$categoryName}");
        }
    }
}