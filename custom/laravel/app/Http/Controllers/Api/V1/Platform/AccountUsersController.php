<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountUsersController extends Controller
{
    /**
     * Display a listing of account users.
     */
    public function index(Account $account): JsonResponse
    {
        $users = $account->users()->withPivot('role', 'availability')->get();

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'role' => $user->pivot->role ?? 'agent',
                    'availability' => $user->pivot->availability ?? 'offline',
                ];
            }),
        ]);
    }

    /**
     * Add a user to an account.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:agent,administrator',
        ]);

        // Check if user is already in the account
        if ($account->users()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['error' => 'User already exists in account'], 422);
        }

        $account->users()->attach($validated['user_id'], [
            'role' => $validated['role'] === 'administrator' ? 2 : 1,
        ]);

        $user = User::find($validated['user_id']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $validated['role'],
        ], 201);
    }

    /**
     * Remove a user from an account.
     */
    public function destroy(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $account->users()->detach($validated['user_id']);

        return response()->json(null, 204);
    }
}
