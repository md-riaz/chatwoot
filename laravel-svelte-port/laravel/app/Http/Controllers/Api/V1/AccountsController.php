<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Account\CreateAccountAction;
use App\Actions\Account\DeleteAccountAction;
use App\Actions\Account\UpdateAccountAction;
use App\Data\Account\AccountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Resources\Account\AccountResource;
use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Accounts
 * 
 * API endpoints for account management
 */
class AccountsController extends Controller
{
    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    /**
     * Update account active timestamp
     * 
     * Update the last active timestamp for an account.
     * 
     * @urlParam account_id required int The ID of the account. Example: 1
     * 
     * @response 200 scenario="Active timestamp updated"
     * @response 404 scenario="Account not found"
     */
    public function updateActiveAt(Request $request, Account $account): JsonResponse
    {
        $account->active_at = now();
        $account->save();
        return response()->json(['message' => 'Account active_at updated']);
    }

    /**
     * Get account cache keys
     * 
     * Retrieve the cache keys associated with an account.
     * 
     * @urlParam account_id required int The ID of the account. Example: 1
     * 
     * @response 200 scenario="Cache keys retrieved"
     * @response 404 scenario="Account not found"
     */
    public function cacheKeys(Account $account): JsonResponse
    {
        $cacheKeys = $account->getCacheKeys();
        
        return response()->json(['cache_keys' => $cacheKeys]);
    }

    /**
     * List all accounts
     * 
     * Get a paginated list of accounts with user and inbox counts.
     * 
     * @queryParam page int Page number. Example: 1
     * @queryParam per_page int Items per page. Example: 15
     * 
     * @response 200 scenario="Accounts retrieved"
     */
    public function index(): AnonymousResourceCollection
    {
        $accounts = Account::withCount('users', 'inboxes')->paginate();

        return AccountResource::collection($accounts);
    }

    /**
     * Create a new account
     * 
     * Create a new account with the provided details.
     * 
     * @bodyParam name string required The account name. Example: Acme Corporation
     * @bodyParam locale string The account locale. Example: en
     * @bodyParam domain string The account domain. Example: acme.com
     * @bodyParam support_email string The support email. Example: support@acme.com
     * 
     * @response 201 scenario="Account created"
     * @response 422 scenario="Validation error"
     */
    public function store(StoreAccountRequest $request): AccountResource
    {
        $account = CreateAccountAction::run(
            AccountData::from($request->validated())
        );

        return new AccountResource($account);
    }

    /**
     * Get account details
     * 
     * Retrieve detailed information about a specific account.
     * 
     * @urlParam account_id required int The ID of the account. Example: 1
     * 
     * @response 200 scenario="Account retrieved"
     * @response 404 scenario="Account not found"
     */
    public function show(Account $account): AccountResource
    {
        // $this->authorize('view', $account);
        return new AccountResource($account->load('users', 'inboxes'));
    }

    /**
     * Update account
     * 
     * Update an existing account with new details.
     * 
     * @urlParam account_id required int The ID of the account. Example: 1
     * @bodyParam name string The account name. Example: Acme Corporation
     * @bodyParam locale string The account locale. Example: en
     * @bodyParam domain string The account domain. Example: acme.com
     * @bodyParam support_email string The support email. Example: support@acme.com
     * 
     * @response 200 scenario="Account updated"
     * @response 404 scenario="Account not found"
     * @response 422 scenario="Validation error"
     */
    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $updatedAccount = UpdateAccountAction::run(
            $account,
            AccountData::from(array_merge(
                $account->toArray(),
                $request->validated()
            ))
        );

        return new AccountResource($updatedAccount);
    }

    /**
     * Delete account
     * 
     * Permanently delete an account and all associated data.
     * 
     * @urlParam account_id required int The ID of the account. Example: 1
     * 
     * @response 204 scenario="Account deleted"
     * @response 404 scenario="Account not found"
     */
    public function destroy(Account $account): JsonResponse
    {
        // $this->authorize('delete', $account);
        DeleteAccountAction::run($account);

        return response()->json(null, 204);
    }
}
