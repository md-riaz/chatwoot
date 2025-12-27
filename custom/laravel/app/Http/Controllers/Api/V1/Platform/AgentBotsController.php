<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Controller;
use App\Models\AgentBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentBotsController extends Controller
{
    /**
     * Display a listing of global agent bots.
     */
    public function index(): JsonResponse
    {
        $agentBots = AgentBot::whereNull('account_id')->get();

        return response()->json([
            'data' => $agentBots->map(function ($bot) {
                return $this->formatAgentBot($bot);
            }),
        ]);
    }

    /**
     * Display a specific agent bot.
     */
    public function show(AgentBot $agentBot): JsonResponse
    {
        return response()->json($this->formatAgentBot($agentBot));
    }

    /**
     * Create a new global agent bot.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'outgoing_url' => 'nullable|url',
            'bot_type' => 'string|in:webhook,csml,dialogflow',
            'bot_config' => 'nullable|array',
        ]);

        $agentBot = AgentBot::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'outgoing_url' => $validated['outgoing_url'] ?? null,
            'bot_type' => $validated['bot_type'] ?? 'webhook',
            'bot_config' => $validated['bot_config'] ?? [],
            'account_id' => null, // Global bot
        ]);

        return response()->json($this->formatAgentBot($agentBot), 201);
    }

    /**
     * Update an agent bot.
     */
    public function update(Request $request, AgentBot $agentBot): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'outgoing_url' => 'nullable|url',
            'bot_type' => 'string|in:webhook,csml,dialogflow',
            'bot_config' => 'nullable|array',
        ]);

        $agentBot->update(array_filter($validated, fn($value) => $value !== null));

        return response()->json($this->formatAgentBot($agentBot));
    }

    /**
     * Delete an agent bot.
     */
    public function destroy(AgentBot $agentBot): JsonResponse
    {
        $agentBot->delete();

        return response()->json(null, 204);
    }

    /**
     * Delete agent bot avatar.
     */
    public function avatar(AgentBot $agentBot): JsonResponse
    {
        $agentBot->update(['avatar_url' => null]);

        return response()->json(null, 204);
    }

    /**
     * Format agent bot for response.
     */
    private function formatAgentBot(AgentBot $agentBot): array
    {
        return [
            'id' => $agentBot->id,
            'name' => $agentBot->name,
            'description' => $agentBot->description,
            'avatar_url' => $agentBot->avatar_url,
            'outgoing_url' => $agentBot->outgoing_url,
            'bot_type' => $agentBot->bot_type,
            'bot_config' => $agentBot->bot_config,
            'access_token' => $agentBot->access_token,
            'created_at' => $agentBot->created_at,
        ];
    }
}
