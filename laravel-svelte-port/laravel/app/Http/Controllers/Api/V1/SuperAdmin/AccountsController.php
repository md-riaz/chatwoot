<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RendersStandardizedErrors;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AccountsController extends Controller
{
    use RendersStandardizedErrors;

    /**
     * Transform account data to match Rails format
     */
    private function transformAccount($account): array
    {
        // Get enabled features from the account's feature flags
        $enabledFeatures = $account->getEnabledFeatures();
        
        // Get all available features from the Feature enum directly
        $allAvailableFeatures = collect(\App\Enums\Feature::cases())->map(function ($feature) {
            $metadata = $feature->metadata();
            $metadata['name'] = $feature->value;
            return $metadata;
        });
        
        // Create allFeatures object with all features and their availability
        $allFeatures = [];
        foreach ($allAvailableFeatures as $feature) {
            $allFeatures[$feature['name']] = true; // All features are available
        }
        
        // Map internal feature names (from getEnabledFeatures) to frontend expected names
        // getEnabledFeatures() returns base names like 'email', 'messenger', 'macros', etc.
        // We need to map these to the frontend names the Svelte UI expects
        $featureNameMap = [
            // Communication channels - map internal to frontend names
            'email' => 'email_integration',
            'whatsapp' => 'whatsapp_integration',
            'messenger' => 'facebook_integration',
            'instagram' => 'instagram_integration',
            'sms' => 'twitter_integration', // sms bit reused for twitter
            'liveChat' => 'website_widget',
            
            // Product features - these match 1:1
            'macros' => 'macros',
            'labels' => 'labels',
            'teams' => 'team_management',
            'campaigns' => 'campaigns',
            'webhooks' => 'webhooks',
            'cannedResponses' => 'canned_responses',
            'automationRules' => 'automation_rules',
            'customAttributes' => 'contact_management',
            'assignment_v2' => 'conversation_assignment',
            'reports' => 'conversation_search', // reports bit reused
            'helpCenter' => 'mobile_app', // helpCenter bit reused
            
            // Integrations
            'linear' => 'linear_integration',
            'slack' => 'slack_integration',
            'shopify' => 'shopify_integration',
            
            // Premium features
            'custom_branding' => 'custom_branding',
            'disable_branding' => 'disable_branding',
            'agent_capacity' => 'agent_capacity',
            'advanced_reporting' => 'advanced_reporting',
            'inbox_assistant' => 'openai_integration',
            
            // Enterprise features (from custom_attributes) - these match 1:1
            'custom_roles' => 'custom_roles',
            'sla_policies' => 'sla_policies',
            'audit_logs' => 'audit_logs',
            'saml' => 'saml',
        ];
        
        // Additional synthetic features that map to multiple internal features
        // These need to be added if any of their component features are enabled
        $syntheticFeatures = [
            'api_access' => 'webhooks',
            'csat_surveys' => 'reports',
            'file_attachments' => 'liveChat',
            'conversation_notes' => 'customAttributes',
            'agent_availability' => 'teams',
            'conversation_status' => 'automationRules',
            'real_time_notifications' => 'webhooks',
        ];
        
        // Convert enabled features to frontend format
        $selectedFeatureFlags = [];
        foreach ($enabledFeatures as $feature) {
            if (isset($featureNameMap[$feature])) {
                $selectedFeatureFlags[] = $featureNameMap[$feature];
            } else {
                // If no mapping exists, pass through as-is (for enterprise features)
                $selectedFeatureFlags[] = $feature;
            }
        }
        
        // Add synthetic features based on their underlying feature bits
        foreach ($syntheticFeatures as $syntheticName => $baseName) {
            if (in_array($baseName, $enabledFeatures) && !in_array($syntheticName, $selectedFeatureFlags)) {
                $selectedFeatureFlags[] = $syntheticName;
            }
        }
        
        return [
            'id' => $account->id,
            'name' => $account->name,
            'locale' => $account->locale_code ?? 'en',
            'domain' => $account->domain,
            'support_email' => $account->support_email,
            'auto_resolve_duration' => $account->auto_resolve_duration,
            'status' => $account->status ?? 'active',
            'users_count' => $account->users_count ?? 0,
            'inboxes_count' => $account->inboxes_count ?? 0,
            'conversations_count' => $account->conversations_count ?? 0,
            'contacts_count' => $account->contacts_count ?? 0,
            'selected_feature_flags' => $selectedFeatureFlags,
            'all_features' => $allFeatures,
            'features' => $enabledFeatures, // Keep original Laravel format too
            'settings' => $account->settings ?? [],
            'limits' => $account->limits ?? [],
            'custom_attributes' => $account->custom_attributes ?? [],
            'internal_attributes' => $account->internal_attributes ?? [],
            'created_at' => $account->created_at?->toISOString(),
            'updated_at' => $account->updated_at?->toISOString(),
        ];
    }

    /**
     * List all accounts (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Account::query();

            // Search filter
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%")
                        ->orWhere('support_email', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            // Recent filter (last 30 days)
            if ($request->boolean('recent', false)) {
                $query->where('created_at', '>=', now()->subDays(30));
            }

            // Marked for deletion filter
            if ($request->boolean('marked_for_deletion', false)) {
                $query->whereNotNull('deleted_at');
            }

            // Add counts
            $query->withCount(['users', 'inboxes', 'conversations', 'contacts']);

            $accounts = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 20));

            // Transform accounts to match Rails format while keeping Laravel pagination
            $accounts->getCollection()->transform(function ($account) {
                return $this->transformAccount($account);
            });

            return response()->json($accounts);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Show account details.
     */
    public function show(Account $account): JsonResponse
    {
        try {
            $account->loadCount(['users', 'inboxes', 'conversations', 'contacts']);
            
            return response()->json(['data' => $this->transformAccount($account)]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a new account.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'locale' => 'nullable|string|max:10',
                'domain' => 'nullable|string|max:255|unique:accounts,domain',
                'support_email' => 'nullable|email',
                'auto_resolve_duration' => 'nullable|integer',
                'settings' => 'nullable|array',
                'limits' => 'nullable|array',
                'custom_attributes' => 'nullable|array',
                'internal_attributes' => 'nullable|array',
                'features' => 'nullable|array',
                'manually_managed_features' => 'nullable|array',
                'selected_feature_flags' => 'nullable|array',
                'enabled_features' => 'nullable|array',
                'status' => 'nullable|string|in:active,suspended',
            ]);

            // Handle Rails-style feature flag processing
            if ($request->has('enabled_features')) {
                $validated['selected_feature_flags'] = array_keys($request->input('enabled_features', []));
            }
            
            // Handle feature flag updates from frontend
            if ($request->has('selected_feature_flags')) {
                $this->updateAccountFeatureFlags($account ?? new Account(), $request->input('selected_feature_flags', []));
            }

            // Handle limits processing like Rails: permitted_params[:limits].to_h.compact
            if (isset($validated['limits']) && is_array($validated['limits'])) {
                $validated['limits'] = array_filter($validated['limits'], function($value) {
                    return $value !== null && $value !== '';
                });
            }

            $account = Account::create($validated);
            $account->loadCount(['users', 'inboxes', 'conversations', 'contacts']);

            return response()->json(['data' => $this->transformAccount($account)], 201);
        } catch (ValidationException $e) {
            return $this->renderValidationErrors($e);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update an account.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'locale' => 'nullable|string|max:10',
                'domain' => 'nullable|string|max:255|unique:accounts,domain,' . $account->id,
                'support_email' => 'nullable|email',
                'auto_resolve_duration' => 'nullable|integer',
                'settings' => 'nullable|array',
                'limits' => 'nullable|array',
                'custom_attributes' => 'nullable|array',
                'internal_attributes' => 'nullable|array',
                'features' => 'nullable|array',
                'manually_managed_features' => 'nullable|array',
                'selected_feature_flags' => 'nullable|array',
                'enabled_features' => 'nullable|array',
                'status' => 'nullable|string|in:active,suspended',
            ]);

            // Handle Rails-style feature flag processing
            if ($request->has('enabled_features')) {
                $validated['selected_feature_flags'] = array_keys($request->input('enabled_features', []));
            }
            
            // Handle feature flag updates from frontend
            if ($request->has('selected_feature_flags')) {
                $this->updateAccountFeatureFlags($account, $request->input('selected_feature_flags', []));
            }

            // Handle limits processing like Rails: permitted_params[:limits].to_h.compact
            if (isset($validated['limits']) && is_array($validated['limits'])) {
                $validated['limits'] = array_filter($validated['limits'], function($value) {
                    return $value !== null && $value !== '';
                });
            }

            $account->update($validated);
            $account->loadCount(['users', 'inboxes', 'conversations', 'contacts']);

            return response()->json(['data' => $this->transformAccount($account)]);
        } catch (ValidationException $e) {
            return $this->renderValidationErrors($e);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete an account.
     */
    public function destroy(Account $account): JsonResponse
    {
        try {
            $account->delete();

            return response()->json([
                'message' => 'Account deletion is in progress.',
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Seed account with demo data.
     */
    public function seed(Account $account): JsonResponse
    {
        try {
            \App\Jobs\SeedAccountJob::dispatch($account);

            return response()->json([
                'message' => 'Account seeding triggered. This may take a few minutes to complete.',
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Reset account cache.
     */
    public function resetCache(Account $account): JsonResponse
    {
        try {
            Cache::forget("account_{$account->id}_settings");
            Cache::forget("account_{$account->id}_features");
            Cache::tags(["account_{$account->id}"])->flush();

            return response()->json([
                'message' => 'Cache keys cleared.',
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update account feature flags based on frontend selection.
     * 
     * This method batches all feature flag operations and saves once at the end
     * to avoid race conditions from multiple saves.
     */
    private function updateAccountFeatureFlags(Account $account, array $selectedFeatures): void
    {
        // Map frontend feature names (snake_case from API transformation) to internal feature names
        $featureNameMap = [
            // Communication channels
            'website_widget' => 'website_widget',
            'email_integration' => 'email_integration',
            'whatsapp_integration' => 'whatsapp_integration',
            'facebook_integration' => 'facebook_integration',
            'instagram_integration' => 'instagram_integration',
            'twitter_integration' => 'twitter_integration',
            
            // Product features
            'macros' => 'macros',
            'labels' => 'labels',
            'team_management' => 'team_management',
            'campaigns' => 'campaigns',
            'webhooks' => 'webhooks',
            'canned_responses' => 'canned_responses',
            'automation_rules' => 'automation_rules',
            'contact_management' => 'contact_management',
            'conversation_assignment' => 'conversation_assignment',
            'conversation_search' => 'conversation_search',
            'file_attachments' => 'file_attachments',
            'conversation_notes' => 'conversation_notes',
            'agent_availability' => 'agent_availability',
            'conversation_status' => 'conversation_status',
            'real_time_notifications' => 'real_time_notifications',
            
            // Integrations
            'linear_integration' => 'linear_integration',
            'slack_integration' => 'slack_integration',
            'shopify_integration' => 'shopify_integration',
            'api_access' => 'api_access',
            'mobile_app' => 'mobile_app',
            
            // Premium features
            'custom_roles' => 'custom_roles',
            'sla_policies' => 'sla_policies',
            'audit_logs' => 'audit_logs',
            'advanced_reporting' => 'advanced_reporting',
            'openai_integration' => 'openai_integration',
            'csat_surveys' => 'csat_surveys',
            'custom_branding' => 'custom_branding',
            'disable_branding' => 'disable_branding',
            'agent_capacity' => 'agent_capacity',
            'saml' => 'saml',
        ];
        
        // Bit flag mappings (must match Account model)
        $flagMap = [
            // Core communication channels (bits 1-8)
            'email' => 1,
            'sms' => 2,
            'messenger' => 4,
            'telegram' => 8,
            'whatsapp' => 16,
            'tiktok' => 32,
            'instagram' => 64,
            'line' => 128,
            
            // Product features (bits 9-16)
            'macros' => 256,
            'labels' => 512,
            'teams' => 1024,
            'reports' => 2048,
            'campaigns' => 4096,
            'webhooks' => 8192,
            'google' => 16384,
            'microsoft' => 32768,
            
            // Integrations (bits 17-24)
            'linear' => 65536,
            'slack' => 131072,
            'shopify' => 262144,
            'cannedResponses' => 524288,
            'helpCenter' => 1048576,
            'automationRules' => 2097152,
            'customAttributes' => 4194304,
            'liveChat' => 8388608,
            
            // Enterprise features (bits 25-32)
            'assignment_v2' => 16777216,
            'inbox_assistant' => 33554432,
            'advanced_reporting' => 67108864,
            'crm_integration' => 134217728,
            'notion_integration' => 268435456,
            'custom_branding' => 536870912,
            'disable_branding' => 1073741824,
            'agent_capacity' => 2147483648,
            
            // Feature name mappings (must match Account model)
            'email_integration' => 1,
            'channel_email' => 1,
            'website_widget' => 8388608,
            'channel_website' => 8388608,
            'api_access' => 8192,
            'team_management' => 1024,
            'automation_rules' => 2097152,
            'csat_surveys' => 2048,
            'whatsapp_integration' => 16,
            'facebook_integration' => 4,
            'channel_facebook' => 4,
            'instagram_integration' => 64,
            'channel_instagram' => 64,
            'twitter_integration' => 2,
            'channel_twitter' => 2,
            'canned_responses' => 524288,
            'contact_management' => 4194304,
            'conversation_assignment' => 16777216,
            'conversation_search' => 2048,
            'file_attachments' => 8388608,
            'conversation_notes' => 4194304,
            'agent_availability' => 1024,
            'conversation_status' => 2097152,
            'real_time_notifications' => 8192,
            'mobile_app' => 8388608,
            'slack_integration' => 131072,
            'linear_integration' => 65536,
            'shopify_integration' => 262144,
            'openai_integration' => 33554432,
        ];
        
        // Enterprise features that use custom_attributes instead of bit flags
        $enterpriseFeatures = [
            'saml', 'sla_policies', 'custom_roles', 'audit_logs',
            'channel_voice', 'advanced_search', 'companies'
        ];
        
        // Map selected features to internal names
        $mappedFeatures = [];
        foreach ($selectedFeatures as $frontendFeature) {
            if (isset($featureNameMap[$frontendFeature])) {
                $mappedFeatures[] = $featureNameMap[$frontendFeature];
            }
        }
        
        // Separate bit flag features from enterprise features
        $bitFlagFeatures = [];
        $selectedEnterpriseFeatures = [];
        
        foreach ($mappedFeatures as $feature) {
            if (in_array($feature, $enterpriseFeatures)) {
                $selectedEnterpriseFeatures[] = $feature;
            } else {
                $bitFlagFeatures[] = $feature;
            }
        }
        
        // Reset all bit flags to 0
        $account->feature_flags = 0;
        
        // Enable selected bit flag features
        foreach ($bitFlagFeatures as $feature) {
            if (isset($flagMap[$feature])) {
                $account->feature_flags |= $flagMap[$feature];
            }
        }
        
        // Update enterprise features in custom_attributes
        $customAttributes = $account->custom_attributes ?? [];
        $customAttributes['enabled_enterprise_features'] = $selectedEnterpriseFeatures;
        $account->custom_attributes = $customAttributes;
        
        // Save once with all changes
        $account->save();
    }
}
