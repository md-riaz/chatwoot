<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 25);
        $accounts = Account::paginate($perPage);

        return response()->json([
            'data' => $accounts->items(),
            'meta' => [
                'current_page' => $accounts->currentPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ],
        ]);
    }

    /**
     * Display a specific account.
     */
    public function show(Account $account): JsonResponse
    {
        return response()->json([
            'id' => $account->id,
            'name' => $account->name,
            'locale' => $account->locale,
            'domain' => $account->domain,
            'support_email' => $account->support_email,
            'features' => $account->features ?? [],
            'limits' => $account->limits ?? [],
            'status' => $account->status->getName(),
            'created_at' => $account->created_at,
        ]);
    }

    /**
     * Create a new account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'locale' => 'string|max:10',
            'domain' => 'nullable|string|unique:accounts,domain',
            'support_email' => 'nullable|email',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
        ]);

        $account = Account::create([
            'name' => $validated['name'],
            'locale' => $validated['locale'] ?? 'en',
            'domain' => $validated['domain'] ?? null,
            'support_email' => $validated['support_email'] ?? null,
            'features' => $validated['features'] ?? [],
            'limits' => $validated['limits'] ?? [],
            'status' => AccountStatus::ACTIVE,  // 0 = Active, 1 = Suspended
        ]);

        return response()->json([
            'id' => $account->id,
            'name' => $account->name,
            'locale' => $account->locale,
        ], 201);
    }

    /**
     * Update an account.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'locale' => 'string|max:10',
            'domain' => 'nullable|string|unique:accounts,domain,' . $account->id,
            'support_email' => 'nullable|email',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'status' => 'string|in:active,suspended',
        ]);

        // Convert status string to enum for database storage
        if (isset($validated['status'])) {
            $validated['status'] = AccountStatus::fromString($validated['status']);
        }

        $account->update(array_filter($validated, fn($value) => $value !== null));

        return response()->json([
            'id' => $account->id,
            'name' => $account->name,
            'locale' => $account->locale,
            'features' => $account->features,
            'limits' => $account->limits,
        ]);
    }

    /**
     * Delete an account.
     */
    public function destroy(Account $account): JsonResponse
    {
        $account->delete();

        return response()->json(null, 204);
    }
}
