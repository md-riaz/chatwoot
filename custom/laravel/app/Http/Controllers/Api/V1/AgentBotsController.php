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
     */
    public function index(Account $account): JsonResource
    {
        $bots = AgentBot::where('account_id', $account->id)->paginate();

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
            'outgoing_url' => 'nullable|url',
            'bot_type' => 'string|in:webhook,csml,dialogflow',
            'bot_config' => 'nullable|array',
        ]);

        $bot = AgentBot::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $bot], 201);
    }

    /**
     * Display the specified agent bot.
     */
    public function show(Account $account, AgentBot $agentBot): JsonResponse
    {
        abort_unless($agentBot->account_id === $account->id, 404);

        return response()->json(['data' => $agentBot]);
    }

    /**
     * Update the specified agent bot.
     */
    public function update(Request $request, Account $account, AgentBot $agentBot): JsonResponse
    {
        abort_unless($agentBot->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'outgoing_url' => 'nullable|url',
            'bot_type' => 'string|in:webhook,csml,dialogflow',
            'bot_config' => 'nullable|array',
        ]);

        $agentBot->update($validated);

        return response()->json(['data' => $agentBot]);
    }

    /**
     * Remove the specified agent bot.
     */
    public function destroy(Account $account, AgentBot $agentBot): JsonResponse
    {
        abort_unless($agentBot->account_id === $account->id, 404);

        $agentBot->delete();

        return response()->json(null, 204);
    }
}
