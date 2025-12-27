<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AssignmentPolicy;
use App\Models\InboxAssignmentPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentPoliciesController extends Controller
{
    /**
     * Display a listing of assignment policies for an account.
     */
    public function index(Account $account): JsonResource
    {
        $policies = AssignmentPolicy::where('account_id', $account->id)
            ->with('inboxes')
            ->paginate();

        return JsonResource::collection($policies);
    }

    /**
     * Store a newly created assignment policy.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:assignment_policies,name,NULL,id,account_id,' . $account->id,
            'description' => 'nullable|string',
            'assignment_order' => 'integer|in:0,1',
            'conversation_priority' => 'integer|in:0,1',
            'fair_distribution_limit' => 'integer|min:1',
            'fair_distribution_window' => 'integer|min:1',
            'enabled' => 'boolean',
        ]);

        $policy = AssignmentPolicy::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $policy], 201);
    }

    /**
     * Display the specified assignment policy.
     */
    public function show(Account $account, AssignmentPolicy $assignmentPolicy): JsonResponse
    {
        abort_unless($assignmentPolicy->account_id === $account->id, 404);

        return response()->json(['data' => $assignmentPolicy->load('inboxes')]);
    }

    /**
     * Update the specified assignment policy.
     */
    public function update(Request $request, Account $account, AssignmentPolicy $assignmentPolicy): JsonResponse
    {
        abort_unless($assignmentPolicy->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255|unique:assignment_policies,name,' . $assignmentPolicy->id . ',id,account_id,' . $account->id,
            'description' => 'nullable|string',
            'assignment_order' => 'integer|in:0,1',
            'conversation_priority' => 'integer|in:0,1',
            'fair_distribution_limit' => 'integer|min:1',
            'fair_distribution_window' => 'integer|min:1',
            'enabled' => 'boolean',
        ]);

        $assignmentPolicy->update($validated);

        return response()->json(['data' => $assignmentPolicy]);
    }

    /**
     * Remove the specified assignment policy.
     */
    public function destroy(Account $account, AssignmentPolicy $assignmentPolicy): JsonResponse
    {
        abort_unless($assignmentPolicy->account_id === $account->id, 404);

        $assignmentPolicy->delete();

        return response()->json(null, 204);
    }

    /**
     * Get inboxes associated with this assignment policy.
     */
    public function inboxes(Account $account, AssignmentPolicy $assignmentPolicy): JsonResource
    {
        abort_unless($assignmentPolicy->account_id === $account->id, 404);

        return JsonResource::collection($assignmentPolicy->inboxes);
    }

    /**
     * Associate an inbox with this assignment policy.
     */
    public function addInbox(Request $request, Account $account, AssignmentPolicy $assignmentPolicy): JsonResponse
    {
        abort_unless($assignmentPolicy->account_id === $account->id, 404);

        $validated = $request->validate([
            'inbox_id' => 'required|exists:inboxes,id',
        ]);

        // Remove existing assignment policy for this inbox
        InboxAssignmentPolicy::where('inbox_id', $validated['inbox_id'])->delete();

        InboxAssignmentPolicy::create([
            'inbox_id' => $validated['inbox_id'],
            'assignment_policy_id' => $assignmentPolicy->id,
        ]);

        return response()->json(['message' => 'Inbox associated successfully'], 201);
    }

    /**
     * Remove an inbox from this assignment policy.
     */
    public function removeInbox(Request $request, Account $account, AssignmentPolicy $assignmentPolicy): JsonResponse
    {
        abort_unless($assignmentPolicy->account_id === $account->id, 404);

        $validated = $request->validate([
            'inbox_id' => 'required|exists:inboxes,id',
        ]);

        InboxAssignmentPolicy::where('inbox_id', $validated['inbox_id'])
            ->where('assignment_policy_id', $assignmentPolicy->id)
            ->delete();

        return response()->json(null, 204);
    }
}
