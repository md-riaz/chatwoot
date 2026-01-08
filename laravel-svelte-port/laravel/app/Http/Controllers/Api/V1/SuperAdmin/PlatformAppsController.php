<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformAppsController extends Controller
{
    /**
     * List all platform apps.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PlatformApp::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        $apps = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($apps);
    }

    /**
     * Show platform app details.
     */
    public function show(PlatformApp $platformApp): JsonResponse
    {
        $platformApp->load('permissibles');

        return response()->json([
            'data' => [
                'id' => $platformApp->id,
                'name' => $platformApp->name,
                'access_token' => $platformApp->access_token,
                'permissibles' => $platformApp->permissibles,
                'created_at' => $platformApp->created_at,
                'updated_at' => $platformApp->updated_at,
            ],
        ]);
    }

    /**
     * Create a new platform app.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $app = PlatformApp::create($validated);

        return response()->json([
            'data' => [
                'id' => $app->id,
                'name' => $app->name,
                'access_token' => $app->access_token,
                'created_at' => $app->created_at,
            ],
        ], 201);
    }

    /**
     * Update a platform app.
     */
    public function update(Request $request, PlatformApp $platformApp): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
        ]);

        $platformApp->update($validated);

        return response()->json(['data' => $platformApp]);
    }

    /**
     * Delete a platform app.
     */
    public function destroy(PlatformApp $platformApp): JsonResponse
    {
        $platformApp->delete();

        return response()->json(null, 204);
    }

    /**
     * Regenerate access token.
     */
    public function regenerateToken(PlatformApp $platformApp): JsonResponse
    {
        $token = $platformApp->regenerateAccessToken();

        return response()->json([
            'data' => [
                'id' => $platformApp->id,
                'name' => $platformApp->name,
                'access_token' => $token,
            ],
        ]);
    }
}
