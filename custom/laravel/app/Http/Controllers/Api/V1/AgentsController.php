<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentsController extends Controller
{
    /**
     * Display a listing of agents for an account.
     */
    public function index(Account $account): JsonResource
    {
        $agents = $account->users()->paginate();

        return JsonResource::collection($agents);
    }

    /**
     * Store a newly created agent (add user to account).
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email',
            'role' => 'nullable|string|in:administrator,agent',
            'availability' => 'nullable|integer|in:0,1,2',
        ]);

        // Find or create user
        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => $validated['name'] ?? explode('@', $validated['email'])[0],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        // Check if user is already in account
        if ($account->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'User already exists in account'], 422);
        }

        // Add user to account
        $account->users()->attach($user->id, [
            'role' => $validated['role'] === 'administrator' ? 2 : 1,
            'availability' => $validated['availability'] ?? 1,
        ]);

        return response()->json(['data' => $user->load('accounts')], 201);
    }

    /**
     * Display the specified agent.
     */
    public function show(Account $account, User $agent): JsonResponse
    {
        abort_unless($account->users()->where('users.id', $agent->id)->exists(), 404);

        return response()->json(['data' => $agent]);
    }

    /**
     * Update the specified agent.
     */
    public function update(Request $request, Account $account, User $agent): JsonResponse
    {
        abort_unless($account->users()->where('users.id', $agent->id)->exists(), 404);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:administrator,agent',
            'availability' => 'nullable|integer|in:0,1,2',
        ]);

        // Update user name if provided
        if (isset($validated['name'])) {
            $agent->update(['name' => $validated['name']]);
        }

        // Update pivot table attributes
        $pivotData = [];
        if (isset($validated['role'])) {
            $pivotData['role'] = $validated['role'] === 'administrator' ? 2 : 1;
        }
        if (isset($validated['availability'])) {
            $pivotData['availability'] = $validated['availability'];
        }

        if (! empty($pivotData)) {
            $account->users()->updateExistingPivot($agent->id, $pivotData);
        }

        return response()->json(['data' => $agent->fresh()]);
    }

    /**
     * Remove the specified agent from the account.
     */
    public function destroy(Account $account, User $agent): JsonResponse
    {
        abort_unless($account->users()->where('users.id', $agent->id)->exists(), 404);

        $account->users()->detach($agent->id);

        return response()->json(null, 204);
    }

    /**
     * Bulk create agents.
     */
    public function bulkCreate(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'email',
        ]);

        foreach ($validated['emails'] as $email) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                ]);
            }

            if (! $account->users()->where('users.id', $user->id)->exists()) {
                $account->users()->attach($user->id, ['role' => 1]);
            }
        }

        return response()->json(null, 200);
    }
}
