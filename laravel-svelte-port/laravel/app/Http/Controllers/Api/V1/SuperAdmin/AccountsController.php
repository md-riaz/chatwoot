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
            'selected_feature_flags' => $account->selected_feature_flags ?? [],
            'all_features' => $account->all_features ?? [],
            'features' => $account->features ?? [],
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
}
