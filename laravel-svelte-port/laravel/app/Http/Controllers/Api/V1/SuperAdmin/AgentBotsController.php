<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AgentBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentBotsController extends Controller
{
    /**
     * List all agent bots (both global and account-specific).
     */
    public function index(Request $request): JsonResponse
    {
        $query = AgentBot::with('account:id,name');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('description', 'like', '%'.$search.'%')
                  ->orWhereHas('account', function ($accountQuery) use ($search) {
                      $accountQuery->where('name', 'like', '%'.$search.'%');
                  });
            });
        }

        $bots = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        // Transform the data to include account information
        $bots->getCollection()->transform(function ($bot) {
            return [
                'id' => $bot->id,
                'name' => $bot->name,
                'description' => $bot->description,
                'outgoing_url' => $bot->outgoing_url,
                'bot_type' => $bot->bot_type,
                'avatar_url' => $bot->getAvatarUrl(),
                'account_id' => $bot->account_id ?? 0, // Return 0 for global bots
                'account' => $bot->account ? [
                    'id' => $bot->account->id,
                    'name' => $bot->account->name,
                ] : null,
                'created_at' => $bot->created_at?->toISOString(),
                'updated_at' => $bot->updated_at?->toISOString(),
            ];
        });

        return response()->json($bots);
    }

    /**
     * Show agent bot details.
     */
    public function show(AgentBot $agentBot): JsonResponse
    {
        $agentBot->load('account:id,name');
        
        return response()->json([
            'data' => [
                'id' => $agentBot->id,
                'name' => $agentBot->name,
                'description' => $agentBot->description,
                'outgoing_url' => $agentBot->outgoing_url,
                'bot_type' => $agentBot->bot_type,
                'avatar_url' => $agentBot->getAvatarUrl(),
                'account_id' => $agentBot->account_id ?? 0, // Return 0 for global bots
                'account' => $agentBot->account ? [
                    'id' => $agentBot->account->id,
                    'name' => $agentBot->account->name,
                ] : null,
                'created_at' => $agentBot->created_at?->toISOString(),
                'updated_at' => $agentBot->updated_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Create an agent bot.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'outgoing_url' => 'nullable|url',
            'account_id' => 'nullable|integer',
            'bot_type' => 'nullable|integer|in:0',
            'bot_config' => 'nullable|array',
        ]);

        // Handle account_id: 0 means global bot (null), >0 means specific account
        if (isset($validated['account_id'])) {
            if ($validated['account_id'] === 0) {
                $validated['account_id'] = null; // Global bot
            } else {
                // Validate that the account exists
                $request->validate([
                    'account_id' => 'exists:accounts,id',
                ]);
            }
        }

        // Default bot_type to webhook (0) if not provided
        $validated['bot_type'] = $validated['bot_type'] ?? AgentBot::TYPE_WEBHOOK;

        $bot = AgentBot::create($validated);
        $bot->load('account:id,name');

        return response()->json([
            'data' => [
                'id' => $bot->id,
                'name' => $bot->name,
                'description' => $bot->description,
                'outgoing_url' => $bot->outgoing_url,
                'bot_type' => $bot->bot_type,
                'avatar_url' => $bot->getAvatarUrl(),
                'account_id' => $bot->account_id ?? 0, // Return 0 for global bots
                'account' => $bot->account ? [
                    'id' => $bot->account->id,
                    'name' => $bot->account->name,
                ] : null,
                'created_at' => $bot->created_at?->toISOString(),
                'updated_at' => $bot->updated_at?->toISOString(),
            ]
        ], 201);
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
            'account_id' => 'nullable|integer',
            'bot_type' => 'integer|in:0',
            'bot_config' => 'nullable|array',
        ]);

        // Handle account_id: 0 means global bot (null), >0 means specific account
        if (isset($validated['account_id'])) {
            if ($validated['account_id'] === 0) {
                $validated['account_id'] = null; // Global bot
            } else {
                // Validate that the account exists
                $request->validate([
                    'account_id' => 'exists:accounts,id',
                ]);
            }
        }

        $agentBot->update($validated);
        $agentBot->load('account:id,name');

        return response()->json([
            'data' => [
                'id' => $agentBot->id,
                'name' => $agentBot->name,
                'description' => $agentBot->description,
                'outgoing_url' => $agentBot->outgoing_url,
                'bot_type' => $agentBot->bot_type,
                'avatar_url' => $agentBot->getAvatarUrl(),
                'account_id' => $agentBot->account_id ?? 0, // Return 0 for global bots
                'account' => $agentBot->account ? [
                    'id' => $agentBot->account->id,
                    'name' => $agentBot->account->name,
                ] : null,
                'created_at' => $agentBot->created_at?->toISOString(),
                'updated_at' => $agentBot->updated_at?->toISOString(),
            ]
        ]);
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
     * Upload agent bot avatar.
     */
    public function uploadAvatar(Request $request, AgentBot $agentBot): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:15360', // 15MB max
        ]);

        try {
            if ($request->hasFile('avatar')) {
                $media = $agentBot->uploadAvatar($request->file('avatar'));
            }

            $agentBot->load('account:id,name');

            return response()->json([
                'data' => [
                    'id' => $agentBot->id,
                    'name' => $agentBot->name,
                    'description' => $agentBot->description,
                    'outgoing_url' => $agentBot->outgoing_url,
                    'bot_type' => $agentBot->bot_type,
                    'avatar_url' => $agentBot->getAvatarUrl(),
                    'account_id' => $agentBot->account_id ?? 0, // Return 0 for global bots
                    'account' => $agentBot->account ? [
                        'id' => $agentBot->account->id,
                        'name' => $agentBot->account->name,
                    ] : null,
                    'created_at' => $agentBot->created_at?->toISOString(),
                    'updated_at' => $agentBot->updated_at?->toISOString(),
                ],
                'message' => 'Avatar uploaded successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete agent bot avatar.
     */
    public function destroyAvatar(AgentBot $agentBot): JsonResponse
    {
        $agentBot->deleteAvatar();

        return response()->json(['message' => 'Avatar deleted.']);
    }
}
