<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokensController extends Controller
{
    /**
     * List all access tokens.
     * 
     * Supports filtering by owner_type (User, AgentBot, PlatformApp) and owner_id.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PersonalAccessToken::query();

        // Filter by owner_type (tokenable_type in Sanctum)
        if ($request->has('owner_type')) {
            $ownerType = $request->input('owner_type');
            // Map simple names to full class names
            $typeMapping = [
                'User' => 'App\Models\User',
                'AgentBot' => 'App\Models\AgentBot',
                'PlatformApp' => 'App\Models\PlatformApp',
            ];
            $query->where('tokenable_type', $typeMapping[$ownerType] ?? $ownerType);
        }

        // Filter by owner_id (tokenable_id in Sanctum)
        if ($request->has('owner_id')) {
            $query->where('tokenable_id', $request->input('owner_id'));
        }

        $tokens = $query->with('tokenable')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        // Transform the collection to include owner information in a consistent format
        $tokens->getCollection()->transform(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'owner_type' => class_basename($token->tokenable_type),
                'owner_id' => $token->tokenable_id,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'expires_at' => $token->expires_at?->toISOString(),
                'created_at' => $token->created_at?->toISOString(),
                'updated_at' => $token->updated_at?->toISOString(),
                'owner' => $token->tokenable ? [
                    'id' => $token->tokenable->id,
                    'type' => class_basename($token->tokenable_type),
                    'name' => $token->tokenable->name ?? null,
                    'email' => $token->tokenable->email ?? null,
                ] : null,
            ];
        });

        return response()->json($tokens);
    }

    /**
     * Show token details.
     */
    public function show(PersonalAccessToken $accessToken): JsonResponse
    {
        $accessToken->load('tokenable');

        return response()->json([
            'data' => [
                'id' => $accessToken->id,
                'name' => $accessToken->name,
                'owner_type' => class_basename($accessToken->tokenable_type),
                'owner_id' => $accessToken->tokenable_id,
                'abilities' => $accessToken->abilities,
                'last_used_at' => $accessToken->last_used_at?->toISOString(),
                'expires_at' => $accessToken->expires_at?->toISOString(),
                'created_at' => $accessToken->created_at?->toISOString(),
                'updated_at' => $accessToken->updated_at?->toISOString(),
                'owner' => $accessToken->tokenable ? [
                    'id' => $accessToken->tokenable->id,
                    'type' => class_basename($accessToken->tokenable_type),
                    'name' => $accessToken->tokenable->name ?? null,
                    'email' => $accessToken->tokenable->email ?? null,
                ] : null,
            ],
        ]);
    }

    /**
     * Revoke (delete) a token.
     */
    public function destroy(PersonalAccessToken $accessToken): JsonResponse
    {
        $accessToken->delete();

        return response()->json(['message' => 'Token revoked.']);
    }
}
