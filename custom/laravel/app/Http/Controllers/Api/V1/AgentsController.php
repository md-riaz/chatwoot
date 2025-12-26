<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentsController extends Controller
{
    /**
     * Display a listing of agents for an account.
     */
    public function index(Account $account): JsonResource
    {
        $agents = $account->users()
            ->wherePivot('role', '!=', 'administrator')
            ->paginate();

        return JsonResource::collection($agents);
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
            'role' => 'string|in:administrator,agent',
            'availability' => 'string|in:online,offline,busy',
            'auto_offline' => 'boolean',
        ]);

        $account->users()->updateExistingPivot($agent->id, $validated);

        return response()->json(['data' => $agent]);
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
}
