<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AgentBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentBotsController extends Controller
{
    /**
     * Display a listing of agent bots for an account.
     * Includes global bots (account_id = null) and account-specific bots.
     */
    public function index(Account $account): JsonResource
    {
        $bots = AgentBot::accessibleTo($account)->paginate();

        return JsonResource::collection($bots);
    }

    /**
     * Store a newly created agent bot.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'outgoing_url' => 'nullable|url|max:2048',
            'bot_type' => 'nullable|string|in:webhook,csml,dialogflow',
            'bot_config' => 'nullable|array',
            'avatar_url' => 'nullable|url',
        ]);

        $bot = AgentBot::create(array_merge($validated, ['account_id' => $account->id]));

        return response()->json(['data' => $bot], 201);
    }

    /**
     * Display the specified agent bot.
     * Allows access to global bots (account_id = null).
     */
    public function show(Account $account, AgentBot $agentBot): JsonResponse
    {
        abort_unless($agentBot->account_id === $account->id || $agentBot->account_id === null, 404);

        return response()->json(['data' => $agentBot]);
    }

    /**
     * Update the specified agent bot.
     * Only account-specific bots can be updated.
     */
    public function update(Request $request, Account $account, AgentBot $agentBot): JsonResponse
    {
        abort_unless($agentBot->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'outgoing_url' => 'nullable|url|max:2048',
            'bot_type' => 'nullable|string|in:webhook,csml,dialogflow',
            'bot_config' => 'nullable|array',
            'avatar_url' => 'nullable|url',
        ]);

        $agentBot->update($validated);

        return response()->json(['data' => $agentBot]);
    }

    /**
     * Remove the specified agent bot.
     * Only account-specific bots can be deleted.
     */
    public function destroy(Account $account, AgentBot $agentBot): JsonResponse
    {
        abort_unless($agentBot->account_id === $account->id, 404);

        $agentBot->delete();

        return response()->json(null, 204);
    }

    /**
     * Delete the agent bot's avatar.
     */
    public function avatar(Account $account, AgentBot $agentBot): JsonResponse
    {
        abort_unless($agentBot->account_id === $account->id, 404);

        $agentBot->update(['avatar_url' => null]);

        return response()->json(['data' => $agentBot]);
    }

    /**
     * Reset the access token for an agent bot.
     */
    public function resetAccessToken(Account $account, AgentBot $agentBot): JsonResponse
    {
        // TODO: Implement actual token reset logic
        $agentBot->access_token = bin2hex(random_bytes(32));
        $agentBot->save();
        return response()->json(['message' => 'Access token reset', 'access_token' => $agentBot->access_token]);
    }
}
