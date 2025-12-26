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

class AccountsController extends Controller
{
    public function __construct(
        private AccountRepository $accountRepository
    ) {}

    /**
     * Display a listing of accounts.
     */
    public function index(): AnonymousResourceCollection
    {
        $accounts = Account::withCount('users', 'inboxes')->paginate();

        return AccountResource::collection($accounts);
    }

    /**
     * Store a newly created account.
     */
    public function store(StoreAccountRequest $request): AccountResource
    {
        $account = CreateAccountAction::run(
            AccountData::from($request->validated())
        );

        return new AccountResource($account);
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account): AccountResource
    {
        // $this->authorize('view', $account);
        return new AccountResource($account->load('users', 'inboxes'));
    }

    /**
     * Update the specified account.
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
     * Remove the specified account.
     */
    public function destroy(Account $account): JsonResponse
    {
        // $this->authorize('delete', $account);
        DeleteAccountAction::run($account);

        return response()->json(null, 204);
    }
}
