<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Create an API channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'webhook_url' => 'nullable|url',
        ]);

        // Create the inbox with API channel
        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::Api',
        ]);

        // Generate API credentials
        $apiKey = bin2hex(random_bytes(32));

        return response()->json([
            'data' => [
                'inbox' => $inbox,
                'api_key' => $apiKey,
            ]
        ], 201);
    }

    /**
     * Update API channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Api', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'webhook_url' => 'nullable|url',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Regenerate API key.
     */
    public function regenerateKey(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Api', 400);

        $apiKey = bin2hex(random_bytes(32));
        
        // Store new API key in channel

        return response()->json(['data' => ['api_key' => $apiKey]]);
    }
}
