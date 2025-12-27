<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AgentCapacityPolicy;
use App\Models\InboxCapacityLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentCapacityPoliciesController extends Controller
{
    /**
     * Display a listing of agent capacity policies for an account.
     */
    public function index(Account $account): JsonResource
    {
        $policies = AgentCapacityPolicy::where('account_id', $account->id)
            ->with('inboxCapacityLimits')
            ->paginate();

        return JsonResource::collection($policies);
    }

    /**
     * Store a newly created agent capacity policy.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exclusion_rules' => 'nullable|array',
        ]);

        $policy = AgentCapacityPolicy::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $policy], 201);
    }

    /**
     * Display the specified agent capacity policy.
     */
    public function show(Account $account, AgentCapacityPolicy $agentCapacityPolicy): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);

        return response()->json(['data' => $agentCapacityPolicy->load('inboxCapacityLimits')]);
    }

    /**
     * Update the specified agent capacity policy.
     */
    public function update(Request $request, Account $account, AgentCapacityPolicy $agentCapacityPolicy): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'exclusion_rules' => 'nullable|array',
        ]);

        $agentCapacityPolicy->update($validated);

        return response()->json(['data' => $agentCapacityPolicy]);
    }

    /**
     * Remove the specified agent capacity policy.
     */
    public function destroy(Account $account, AgentCapacityPolicy $agentCapacityPolicy): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);

        $agentCapacityPolicy->delete();

        return response()->json(null, 204);
    }

    /**
     * Get users assigned to this capacity policy.
     */
    public function users(Account $account, AgentCapacityPolicy $agentCapacityPolicy): JsonResource
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);

        return JsonResource::collection($agentCapacityPolicy->users);
    }

    /**
     * Assign a user to this capacity policy.
     */
    public function addUser(Request $request, Account $account, AgentCapacityPolicy $agentCapacityPolicy): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Update the account_user to assign this capacity policy
        $account->accountUsers()
            ->where('user_id', $validated['user_id'])
            ->update(['agent_capacity_policy_id' => $agentCapacityPolicy->id]);

        return response()->json(['message' => 'User assigned to capacity policy'], 201);
    }

    /**
     * Remove a user from this capacity policy.
     */
    public function removeUser(Request $request, Account $account, AgentCapacityPolicy $agentCapacityPolicy): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $account->accountUsers()
            ->where('user_id', $validated['user_id'])
            ->where('agent_capacity_policy_id', $agentCapacityPolicy->id)
            ->update(['agent_capacity_policy_id' => null]);

        return response()->json(null, 204);
    }

    /**
     * Add an inbox capacity limit.
     */
    public function addInboxLimit(Request $request, Account $account, AgentCapacityPolicy $agentCapacityPolicy): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);

        $validated = $request->validate([
            'inbox_id' => 'required|exists:inboxes,id',
            'conversation_limit' => 'required|integer|min:1',
        ]);

        $limit = InboxCapacityLimit::updateOrCreate(
            [
                'agent_capacity_policy_id' => $agentCapacityPolicy->id,
                'inbox_id' => $validated['inbox_id'],
            ],
            [
                'conversation_limit' => $validated['conversation_limit'],
            ]
        );

        return response()->json(['data' => $limit], 201);
    }

    /**
     * Update an inbox capacity limit.
     */
    public function updateInboxLimit(Request $request, Account $account, AgentCapacityPolicy $agentCapacityPolicy, InboxCapacityLimit $inboxLimit): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);
        abort_unless($inboxLimit->agent_capacity_policy_id === $agentCapacityPolicy->id, 404);

        $validated = $request->validate([
            'conversation_limit' => 'required|integer|min:1',
        ]);

        $inboxLimit->update($validated);

        return response()->json(['data' => $inboxLimit]);
    }

    /**
     * Remove an inbox capacity limit.
     */
    public function removeInboxLimit(Account $account, AgentCapacityPolicy $agentCapacityPolicy, InboxCapacityLimit $inboxLimit): JsonResponse
    {
        abort_unless($agentCapacityPolicy->account_id === $account->id, 404);
        abort_unless($inboxLimit->agent_capacity_policy_id === $agentCapacityPolicy->id, 404);

        $inboxLimit->delete();

        return response()->json(null, 204);
    }
}
