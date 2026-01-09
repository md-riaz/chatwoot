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
        
        // Map Laravel feature names to frontend expected names
        // Note: getEnabledFeatures() returns snake_case enum values, 
        // but we want to send snake_case to frontend (which will transform to camelCase)
        $featureNameMap = [
            // Communication channels (already snake_case from enum)
            'email_integration' => 'email_integration',
            'whatsapp_integration' => 'whatsapp_integration',
            'facebook_integration' => 'facebook_integration',
            'instagram_integration' => 'instagram_integration',
            'twitter_integration' => 'twitter_integration',
            'website_widget' => 'website_widget',
            
            // Product features (already snake_case from enum)
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
            
            // Integrations (already snake_case from enum)
            'linear_integration' => 'linear_integration',
            'slack_integration' => 'slack_integration',
            'shopify_integration' => 'shopify_integration',
            'api_access' => 'api_access',
            'mobile_app' => 'mobile_app',
            
            // Premium features (already snake_case from enum)
            'custom_roles' => 'custom_roles',
            'sla_policies' => 'sla_policies',
            'audit_logs' => 'audit_logs',
            'advanced_reporting' => 'advanced_reporting',
            'openai_integration' => 'openai_integration',
            'csat_surveys' => 'csat_surveys',
        ];
        
        // Convert enabled features to frontend format
        $selectedFeatureFlags = [];
        foreach ($enabledFeatures as $feature) {
            if (isset($featureNameMap[$feature])) {
                $selectedFeatureFlags[] = $featureNameMap[$feature];
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
     */
    private function updateAccountFeatureFlags(Account $account, array $selectedFeatures): void
    {
        // Map frontend feature names (camelCase) back to Laravel enum values (snake_case)
        // Note: Frontend sends camelCase, but API client transforms to snake_case before reaching here
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
        ];
        
        // Get current enabled features and disable all
        $currentFeatures = $account->getEnabledFeatures();
        foreach ($currentFeatures as $feature) {
            $account->disableFeature($feature);
        }
        
        // Enable selected features
        foreach ($selectedFeatures as $frontendFeature) {
            if (isset($featureNameMap[$frontendFeature])) {
                $enumValue = $featureNameMap[$frontendFeature];
                $account->enableFeature($enumValue);
            }
        }
        
        // Save the account
        $account->save();
    }
}
