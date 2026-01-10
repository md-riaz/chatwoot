<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Actions\SuperAdmin\Traits\FormatsAccountData;
use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RendersStandardizedErrors;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AccountsController extends Controller
{
    use RendersStandardizedErrors, FormatsAccountData;

    /**
     * Transform account data to match Rails format.
     * With simplified naming from features.yml, no mapping needed!
     */
    private function transformAccount($account): array
    {
        // Get enabled features - returns feature names directly
        $enabledFeatures = $account->getEnabledFeatures();
        
        // Get all available features from Laravel config
        $allAvailableFeatures = config('features.features', []);
        
        // Create allFeatures object with feature metadata for frontend
        $allFeatures = [];
        foreach ($allAvailableFeatures as $feature) {
            $allFeatures[$feature['name']] = [
                'available' => true,
                'display_name' => $feature['display_name'] ?? ucwords(str_replace('_', ' ', $feature['name'])),
                'enabled' => $feature['enabled'] ?? false,
                'premium' => $feature['premium'] ?? false,
                'help_url' => $feature['help_url'] ?? null,
            ];
        }
        
        $selectedFeatureFlags = $enabledFeatures;
        
        return [
            'id' => $account->id,
            'name' => $account->name,
            'locale' => $account->locale_code ?? 'en',
            'domain' => $account->domain,
            'support_email' => $account->support_email,
            'auto_resolve_duration' => $account->auto_resolve_duration,
            'status' => $account->status->getName(),
            'users_count' => $account->users_count ?? 0,
            'inboxes_count' => $account->inboxes_count ?? 0,
            'conversations_count' => $account->conversations_count ?? 0,
            'contacts_count' => $account->contacts_count ?? 0,
            'selected_feature_flags' => $selectedFeatureFlags,
            'all_features' => $allFeatures,
            'features' => $enabledFeatures, // Same as selected_feature_flags now
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

            // Status filter - convert string to enum for database comparison
            if ($request->has('status')) {
                $statusEnum = AccountStatus::fromString($request->input('status'));
                $query->where('status', $statusEnum);
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
                'enabled_features' => 'nullable|array', // Rails-compatible format
                'status' => 'nullable|string|in:active,suspended',
            ]);

            // Convert status string to enum for database storage
            if (isset($validated['status'])) {
                $validated['status'] = AccountStatus::fromString($validated['status']);
            }

            // Handle Rails-style feature flag processing (enabled_features format)
            if ($request->has('enabled_features')) {
                // Extract feature names from enabled_features keys (remove 'feature_' prefix)
                $enabledFeatureKeys = array_keys($request->input('enabled_features', []));
                $featureNames = array_map(function($key) {
                    return str_replace('feature_', '', $key);
                }, $enabledFeatureKeys);
                $validated['selected_feature_flags'] = $featureNames;
            }
            // Handle legacy format (selectedFeatureFlags array)
            elseif ($request->has('selected_feature_flags')) {
                // Already in correct format
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

            // Convert status string to enum for database storage
            if (isset($validated['status'])) {
                $validated['status'] = AccountStatus::fromString($validated['status']);
            }

            // Handle feature flag updates - expect object format for proper API transformation
            if ($request->has('selected_feature_flags')) {
                $selectedFeatureFlags = $request->input('selected_feature_flags');
                
                \Log::info('Feature flag update request', [
                    'account_id' => $account->id,
                    'raw_input' => $selectedFeatureFlags,
                    'is_array' => is_array($selectedFeatureFlags),
                    'array_keys' => is_array($selectedFeatureFlags) ? array_keys($selectedFeatureFlags) : 'not_array'
                ]);
                
                // Extract keys from the object (API transformer converts camelCase keys to snake_case)
                $validated['selected_feature_flags'] = is_array($selectedFeatureFlags) ? array_keys($selectedFeatureFlags) : [];
                
                \Log::info('Processed feature flags', [
                    'processed_features' => $validated['selected_feature_flags']
                ]);
            }

            // Handle limits processing like Rails: permitted_params[:limits].to_h.compact
            if (isset($validated['limits']) && is_array($validated['limits'])) {
                $validated['limits'] = array_filter($validated['limits'], function($value) {
                    return $value !== null && $value !== '';
                });
            }

            // Remove feature flag related fields from validated data to prevent conflicts
            $featureFlagFields = ['selected_feature_flags', 'enabled_features', 'features'];
            $accountData = collect($validated)->except($featureFlagFields)->toArray();
            
            // Update account data first (without feature flags)
            $account->update($accountData);
            
            // Handle feature flag updates from frontend AFTER other updates
            if ($request->has('selected_feature_flags')) {
                $this->updateAccountFeatureFlags($account, $validated['selected_feature_flags']);
            }

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
        // Enterprise features that use custom_attributes instead of bit flags
        $enterpriseFeatures = [
            'saml', 'sla', 'custom_roles', 'audit_logs',
            'advanced_search', 'companies'
        ];
        
        // Separate bit flag features from enterprise features
        $bitFlagFeatures = [];
        $selectedEnterpriseFeatures = [];
        
        foreach ($selectedFeatures as $feature) {
            if (in_array($feature, $enterpriseFeatures)) {
                $selectedEnterpriseFeatures[] = $feature;
            } else {
                $bitFlagFeatures[] = $feature;
            }
        }
        
        // Reset all bit flags to 0
        $account->feature_flags = 0;
        
        // Enable selected bit flag features using Account's feature map
        $flagMap = $account->getFeatureFlagMap();
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
