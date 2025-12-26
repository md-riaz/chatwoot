<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokensController extends Controller
{
    /**
     * List all access tokens.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PersonalAccessToken::query();

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('tokenable_id', $request->input('user_id'))
                ->where('tokenable_type', User::class);
        }

        $tokens = $query->with('tokenable')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($tokens);
    }

    /**
     * Show token details.
     */
    public function show(PersonalAccessToken $accessToken): JsonResponse
    {
        $accessToken->load('tokenable');

        return response()->json(['data' => $accessToken]);
    }

    /**
     * Create a new access token for a user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $token = $user->createToken(
            $validated['name'],
            $validated['abilities'] ?? ['*'],
            $validated['expires_at'] ?? null
        );

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'name' => $token->accessToken->name,
                'abilities' => $token->accessToken->abilities,
                'expires_at' => $token->accessToken->expires_at,
            ],
        ], 201);
    }

    /**
     * Revoke a token.
     */
    public function destroy(PersonalAccessToken $accessToken): JsonResponse
    {
        $accessToken->delete();

        return response()->json(['message' => 'Token revoked.']);
    }

    /**
     * Revoke all tokens for a user.
     */
    public function revokeAllForUser(User $user): JsonResponse
    {
        $count = $user->tokens()->count();
        $user->tokens()->delete();

        return response()->json([
            'message' => "Revoked {$count} tokens for user.",
        ]);
    }
}
