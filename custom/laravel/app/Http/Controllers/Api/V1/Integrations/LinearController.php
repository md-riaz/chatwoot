<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinearController extends Controller
{
    /**
     * Get Linear integration settings.
     */
    public function show(Account $account): JsonResponse
    {
        $integration = null; // Would fetch from integrations table

        return response()->json(['data' => $integration]);
    }

    /**
     * Create/Connect Linear integration.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string', // OAuth code from Linear
            'redirect_uri' => 'required|url',
        ]);

        // Exchange code for access token
        // Store integration settings

        return response()->json(['message' => 'Linear connected successfully'], 201);
    }

    /**
     * Update Linear integration settings.
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'string',
            'enabled' => 'boolean',
        ]);

        // Update Linear integration settings

        return response()->json(['message' => 'Linear settings updated']);
    }

    /**
     * Disconnect Linear integration.
     */
    public function destroy(Account $account): JsonResponse
    {
        // Remove Linear integration

        return response()->json(null, 204);
    }

    /**
     * Get Linear teams.
     */
    public function teams(Account $account): JsonResponse
    {
        // Fetch teams from Linear API
        $teams = [];

        return response()->json(['data' => $teams]);
    }

    /**
     * Get Linear projects.
     */
    public function projects(Account $account, Request $request): JsonResponse
    {
        $teamId = $request->get('team_id');

        // Fetch projects from Linear API
        $projects = [];

        return response()->json(['data' => $projects]);
    }

    /**
     * Create Linear issue from conversation.
     */
    public function createIssue(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'team_id' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|between:0,4',
        ]);

        // Create issue in Linear
        $issue = [
            'id' => 'issue_id',
            'url' => 'https://linear.app/team/issue/XXX',
        ];

        return response()->json(['data' => $issue], 201);
    }

    /**
     * Link existing Linear issue to conversation.
     */
    public function linkIssue(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'issue_id' => 'required|string',
        ]);

        // Link Linear issue to conversation

        return response()->json(['message' => 'Issue linked successfully']);
    }

    /**
     * Unlink Linear issue from conversation.
     */
    public function unlinkIssue(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'issue_id' => 'required|string',
        ]);

        // Unlink Linear issue from conversation

        return response()->json(['message' => 'Issue unlinked successfully']);
    }
}
