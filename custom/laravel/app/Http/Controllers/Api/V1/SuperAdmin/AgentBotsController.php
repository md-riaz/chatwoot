<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AgentBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentBotsController extends Controller
{
    /**
     * List all global agent bots.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AgentBot::whereNull('account_id');

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->input('search').'%');
        }

        $bots = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($bots);
    }

    /**
     * Show agent bot details.
     */
    public function show(AgentBot $agentBot): JsonResponse
    {
        return response()->json(['data' => $agentBot]);
    }

    /**
     * Create a global agent bot.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'outgoing_url' => 'nullable|url',
            'bot_type' => 'required|string|in:webhook,csml,dialogflow',
            'bot_config' => 'nullable|array',
        ]);

        // Global bot has no account_id
        $validated['account_id'] = null;

        $bot = AgentBot::create($validated);

        return response()->json(['data' => $bot], 201);
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

        $agentBot->update($validated);

        return response()->json(['data' => $agentBot]);
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
    public function destroyAvatar(AgentBot $agentBot): JsonResponse
    {
        $agentBot->update(['avatar_url' => null]);

        return response()->json(['message' => 'Avatar deleted.']);
    }
}
