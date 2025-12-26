<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LineController extends Controller
{
    /**
     * Create a LINE channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel_id' => 'required|string',
            'channel_secret' => 'required|string',
            'channel_token' => 'required|string',
        ]);

        // Create the inbox with LINE channel
        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::Line',
        ]);

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update LINE channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Line', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'channel_token' => 'string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from LINE.
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify LINE signature
        // Process events (messages, follow, unfollow, etc.)
        // Create or update conversations

        return response()->json(['status' => 'ok']);
    }
}
