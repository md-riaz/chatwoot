<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Actions\SuperAdmin\CreateAccountAction;
use App\Actions\SuperAdmin\GetAccountAction;
use App\Actions\SuperAdmin\ListAccountsAction;
use App\Actions\SuperAdmin\UpdateAccountAction;
use App\Data\SuperAdmin\AccountData;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccountsController extends Controller
{
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
    }

    /**
     * Show account details.
     */
    public function show(Account $account): JsonResponse
    {
        $result = $this->getAccount->handle($account->id);

        return response()->json(['data' => $result->toArray()]);
    }

    /**
     * Create a new account.
     */
    public function store(Request $request): JsonResponse
    {
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
            'status' => 'nullable|string|in:active,suspended',
        ]);

        $data = AccountData::from($validated);
        $result = $this->createAccount->handle($data);

        return response()->json(['data' => $result->toArray()], 201);
    }

    /**
     * Update an account.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
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
            'status' => 'nullable|string|in:active,suspended',
        ]);

        $data = AccountData::from([
            ...$validated,
            'id' => $account->id,
            'name' => $validated['name'] ?? $account->name, // Ensure name is always present
        ]);

        $result = $this->updateAccount->handle($account->id, $data);

        return response()->json(['data' => $result->toArray()]);
    }

    /**
     * Delete an account.
     */
    public function destroy(Account $account): JsonResponse
    {
        $account->delete();

        return response()->json([
            'message' => 'Account deletion is in progress.',
        ]);
    }

    /**
     * Seed account with demo data.
     */
    public function seed(Account $account): JsonResponse
    {
        // Dispatch seed job (to be implemented)
        // SeedAccountJob::dispatch($account);

        return response()->json([
            'message' => 'Account seeding triggered.',
        ]);
    }

    /**
     * Reset account cache.
     */
    public function resetCache(Account $account): JsonResponse
    {
        Cache::forget("account_{$account->id}_settings");
        Cache::forget("account_{$account->id}_features");
        Cache::tags(["account_{$account->id}"])->flush();

        return response()->json([
            'message' => 'Cache keys cleared.',
        ]);
    }
}
