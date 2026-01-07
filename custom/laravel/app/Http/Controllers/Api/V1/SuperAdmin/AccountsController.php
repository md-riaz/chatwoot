<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Actions\SuperAdmin\CreateAccountAction;
use App\Actions\SuperAdmin\GetAccountAction;
use App\Actions\SuperAdmin\ListAccountsAction;
use App\Actions\SuperAdmin\UpdateAccountAction;
use App\Data\SuperAdmin\AccountData;
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

    public function __construct(
        private ListAccountsAction $listAccounts,
        private GetAccountAction $getAccount,
        private CreateAccountAction $createAccount,
        private UpdateAccountAction $updateAccount
    ) {}

    /**
     * List all accounts (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->listAccounts->handle(
                perPage: (int) $request->input('per_page', 20),
                page: (int) $request->input('page', 1),
                search: $request->input('search'),
                status: $request->input('status'),
                recent: $request->boolean('recent', false),
                markedForDeletion: $request->boolean('marked_for_deletion', false)
            );

            return response()->json([
                'data' => $result->data,
                'meta' => $result->meta
            ]);
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
            $result = $this->getAccount->handle($account->id);

            return response()->json(['data' => $result->toArray()]);
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

            $data = AccountData::from($validated);
            $result = $this->createAccount->handle($data);

            return response()->json(['data' => $result->toArray()], 201);
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

            $data = AccountData::from([
                ...$validated,
                'id' => $account->id,
                'name' => $validated['name'] ?? $account->name, // Ensure name is always present
            ]);

            $result = $this->updateAccount->handle($account->id, $data);

            return response()->json(['data' => $result->toArray()]);
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
            // Dispatch seed job (to be implemented)
            // SeedAccountJob::dispatch($account);

            return response()->json([
                'message' => 'Account seeding triggered.',
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
