<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * Display a specific user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'display_name' => $user->display_name,
            'custom_attributes' => $user->custom_attributes ?? [],
            'accounts' => $user->accounts->map(function ($account) use ($user) {
                $pivot = $account->pivot;
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'role' => $pivot->role ?? 'agent',
                ];
            }),
            'confirmed' => $user->email_verified_at !== null,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'custom_attributes' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'] ?? Str::random(16)),
            'custom_attributes' => $validated['custom_attributes'] ?? [],
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'custom_attributes' => $user->custom_attributes,
            'confirmed' => false,
            'created_at' => $user->created_at,
        ], 201);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'custom_attributes' => 'nullable|array',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Merge custom attributes
        if (isset($validated['custom_attributes'])) {
            $validated['custom_attributes'] = array_merge(
                $user->custom_attributes ?? [],
                $validated['custom_attributes']
            );
        }

        $user->update(array_filter($validated));

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'custom_attributes' => $user->custom_attributes,
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Get SSO login link for user.
     */
    public function login(User $user): JsonResponse
    {
        // Generate a temporary login token
        $token = $user->createToken('platform-sso-' . time())->plainTextToken;

        return response()->json([
            'url' => config('app.frontend_url') . '/auth/sso?' . http_build_query(['token' => $token]),
        ]);
    }

    /**
     * Generate an access token for user.
     */
    public function token(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
        ]);

        $token = $user->createToken($validated['name'] ?? 'platform-api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ], 201);
    }
}
