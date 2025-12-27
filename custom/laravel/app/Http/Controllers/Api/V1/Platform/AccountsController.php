<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
            'status' => $account->status,
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
            'status' => 1,
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
            'status' => 'integer|in:0,1',
        ]);

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
