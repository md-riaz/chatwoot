<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Display a listing of users (agents) for an account.
     */
    public function index(Account $account): JsonResource
    {
        $users = $account->users()->paginate();

        return JsonResource::collection($users);
    }

    /**
     * Store a newly created user (agent).
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'string|in:administrator,agent',
            'availability' => 'string|in:online,offline,busy',
            'auto_offline' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Attach user to account with role
        $account->users()->attach($user->id, [
            'role' => $validated['role'] ?? 'agent',
            'availability' => $validated['availability'] ?? 'online',
            'auto_offline' => $validated['auto_offline'] ?? true,
        ]);

        return response()->json(['data' => $user], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(Account $account, User $user): JsonResponse
    {
        abort_unless($account->users()->where('users.id', $user->id)->exists(), 404);

        return response()->json(['data' => $user]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, Account $account, User $user): JsonResponse
    {
        abort_unless($account->users()->where('users.id', $user->id)->exists(), 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'role' => 'string|in:administrator,agent',
            'availability' => 'string|in:online,offline,busy',
            'auto_offline' => 'boolean',
        ]);

        $user->update(array_intersect_key($validated, array_flip(['name', 'email'])));

        if (isset($validated['role']) || isset($validated['availability']) || isset($validated['auto_offline'])) {
            $account->users()->updateExistingPivot($user->id, array_intersect_key(
                $validated,
                array_flip(['role', 'availability', 'auto_offline'])
            ));
        }

        return response()->json(['data' => $user]);
    }

    /**
     * Remove the specified user from the account.
     */
    public function destroy(Account $account, User $user): JsonResponse
    {
        abort_unless($account->users()->where('users.id', $user->id)->exists(), 404);

        $account->users()->detach($user->id);

        return response()->json(null, 204);
    }
}
