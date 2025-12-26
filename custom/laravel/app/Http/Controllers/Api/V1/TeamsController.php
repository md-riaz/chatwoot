<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamsController extends Controller
{
    /**
     * Display a listing of teams for an account.
     */
    public function index(Account $account): JsonResource
    {
        $teams = Team::where('account_id', $account->id)
            ->withCount('members')
            ->paginate();

        return JsonResource::collection($teams);
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'allow_auto_assign' => 'boolean',
        ]);

        $team = Team::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $team], 201);
    }

    /**
     * Display the specified team.
     */
    public function show(Account $account, Team $team): JsonResponse
    {
        abort_unless($team->account_id === $account->id, 404);

        return response()->json(['data' => $team->load('members')]);
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Account $account, Team $team): JsonResponse
    {
        abort_unless($team->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'allow_auto_assign' => 'boolean',
        ]);

        $team->update($validated);

        return response()->json(['data' => $team]);
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Account $account, Team $team): JsonResponse
    {
        abort_unless($team->account_id === $account->id, 404);

        $team->delete();

        return response()->json(null, 204);
    }

    /**
     * Get team members.
     */
    public function members(Account $account, Team $team): JsonResponse
    {
        abort_unless($team->account_id === $account->id, 404);

        return response()->json(['data' => $team->members]);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Account $account, Team $team): JsonResponse
    {
        abort_unless($team->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $team->members()->attach($validated['user_id']);

        return response()->json(['data' => $team->load('members')]);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Request $request, Account $account, Team $team): JsonResponse
    {
        abort_unless($team->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $team->members()->detach($validated['user_id']);

        return response()->json(null, 204);
    }
}
