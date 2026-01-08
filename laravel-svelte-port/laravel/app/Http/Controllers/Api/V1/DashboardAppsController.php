<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\DashboardApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardAppsController extends Controller
{
    /**
     * Display a listing of dashboard apps for an account.
     */
    public function index(Account $account): JsonResource
    {
        $apps = DashboardApp::where('account_id', $account->id)->paginate();

        return JsonResource::collection($apps);
    }

    /**
     * Store a newly created dashboard app.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|array',
            'content.url' => 'required|url',
            'content.type' => 'string|in:frame',
        ]);

        $app = DashboardApp::create([
            ...$validated,
            'account_id' => $account->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['data' => $app], 201);
    }

    /**
     * Display the specified dashboard app.
     */
    public function show(Account $account, DashboardApp $dashboardApp): JsonResponse
    {
        abort_unless($dashboardApp->account_id === $account->id, 404);

        return response()->json(['data' => $dashboardApp]);
    }

    /**
     * Update the specified dashboard app.
     */
    public function update(Request $request, Account $account, DashboardApp $dashboardApp): JsonResponse
    {
        abort_unless($dashboardApp->account_id === $account->id, 404);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'array',
            'content.url' => 'url',
            'content.type' => 'string|in:frame',
        ]);

        $dashboardApp->update($validated);

        return response()->json(['data' => $dashboardApp]);
    }

    /**
     * Remove the specified dashboard app.
     */
    public function destroy(Account $account, DashboardApp $dashboardApp): JsonResponse
    {
        abort_unless($dashboardApp->account_id === $account->id, 404);

        $dashboardApp->delete();

        return response()->json(null, 204);
    }
}
