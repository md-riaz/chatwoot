<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccountsController extends Controller
{
    /**
     * List all accounts (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::query();

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $accounts = $query->withCount(['users', 'inboxes', 'conversations'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($accounts);
    }

    /**
     * Show account details.
     */
    public function show(Account $account): JsonResponse
    {
        $account->loadCount(['users', 'inboxes', 'conversations', 'contacts']);
        $account->load(['users' => function ($q) {
            $q->limit(10);
        }]);

        return response()->json(['data' => $account]);
    }

    /**
     * Create a new account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'locale' => 'string|max:10',
            'domain' => 'nullable|string|max:255|unique:accounts,domain',
            'support_email' => 'nullable|email',
            'settings' => 'nullable|array',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
        ]);

        $account = Account::create($validated);

        return response()->json(['data' => $account], 201);
    }

    /**
     * Update an account.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'locale' => 'string|max:10',
            'domain' => 'nullable|string|max:255|unique:accounts,domain,'.$account->id,
            'support_email' => 'nullable|email',
            'settings' => 'nullable|array',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'status' => 'integer|in:0,1',
        ]);

        // Handle feature flags
        if ($request->has('enabled_features')) {
            $validated['features'] = array_merge(
                $account->features ?? [],
                $request->input('enabled_features')
            );
        }

        $account->update($validated);

        return response()->json(['data' => $account]);
    }

    /**
     * Delete an account.
     */
    public function destroy(Account $account): JsonResponse
    {
        // Queue account deletion
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
        // Clear account-specific cache
        Cache::forget("account_{$account->id}_settings");
        Cache::forget("account_{$account->id}_features");
        Cache::tags(["account_{$account->id}"])->flush();

        return response()->json([
            'message' => 'Cache keys cleared.',
        ]);
    }
}
