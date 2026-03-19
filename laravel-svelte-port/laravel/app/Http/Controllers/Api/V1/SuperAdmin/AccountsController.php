<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Actions\SuperAdmin\CreateAccountAction;
use App\Actions\SuperAdmin\DeleteAccountAction;
use App\Actions\SuperAdmin\GetAccountAction;
use App\Actions\SuperAdmin\ListAccountsAction;
use App\Actions\SuperAdmin\ResetAccountCacheAction;
use App\Actions\SuperAdmin\SeedAccountAction;
use App\Actions\SuperAdmin\UpdateAccountAction;
use App\Data\SuperAdmin\AccountData;
use App\Http\Controllers\Concerns\RendersStandardizedErrors;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\AccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    use RendersStandardizedErrors;

    public function index(Request $request): JsonResponse
    {
        try {
            $accounts = ListAccountsAction::run(
                perPage: (int) $request->input('per_page', 20),
                page: (int) $request->input('page', 1),
                search: $request->input('search'),
                status: $request->input('status'),
                recent: $request->boolean('recent', false),
                markedForDeletion: $request->boolean('marked_for_deletion', false),
            );

            return response()->json([
                'data' => $accounts->items(),
                'links' => [
                    'first' => $accounts->url(1),
                    'last' => $accounts->url($accounts->lastPage()),
                    'prev' => $accounts->previousPageUrl(),
                    'next' => $accounts->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $accounts->currentPage(),
                    'from' => $accounts->firstItem(),
                    'last_page' => $accounts->lastPage(),
                    'path' => $accounts->path(),
                    'per_page' => $accounts->perPage(),
                    'to' => $accounts->lastItem(),
                    'total' => $accounts->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(Account $account): JsonResponse
    {
        try {
            return response()->json(['data' => GetAccountAction::run($account->id)->toArray()]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(AccountRequest $request): JsonResponse
    {
        try {
            $account = CreateAccountAction::run($this->makeAccountData($request));

            return response()->json(['data' => $account->toArray()], 201);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(AccountRequest $request, Account $account): JsonResponse
    {
        try {
            $updatedAccount = UpdateAccountAction::run($account->id, $this->makeAccountData($request, $account));

            return response()->json(['data' => $updatedAccount->toArray()]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Account $account): JsonResponse
    {
        try {
            DeleteAccountAction::run($account);

            return response()->json([
                'message' => 'Account deletion is in progress.',
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function seed(Account $account): JsonResponse
    {
        try {
            SeedAccountAction::run($account);

            return response()->json([
                'message' => 'Account seeding triggered. This may take a few minutes to complete.',
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function resetCache(Account $account): JsonResponse
    {
        try {
            ResetAccountCacheAction::run($account);

            return response()->json([
                'message' => 'Cache keys cleared.',
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    private function makeAccountData(AccountRequest $request, ?Account $account = null): AccountData
    {
        $validated = $request->validated();

        if ($account !== null) {
            $validated = array_merge([
                'name' => $account->name,
                'locale' => $account->locale?->getCode() ?? 'en',
                'domain' => $account->domain,
                'support_email' => $account->support_email,
                'auto_resolve_duration' => $account->auto_resolve_duration,
                'settings' => $account->settings,
                'limits' => $account->limits,
                'custom_attributes' => $account->custom_attributes,
                'internal_attributes' => $account->internal_attributes,
                'selected_feature_flags' => $account->getEnabledFeatures(),
                'status' => $account->status->getName(),
            ], $validated);
        }

        return AccountData::from($validated);
    }
}
