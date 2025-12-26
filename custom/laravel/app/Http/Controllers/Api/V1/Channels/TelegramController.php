<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    /**
     * Create a Telegram channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'bot_token' => 'required|string',
            'name' => 'string|max:255',
        ]);

        // Create the inbox with Telegram channel
        $inbox = Inbox::create([
            'name' => $validated['name'] ?? 'Telegram Bot',
            'account_id' => $account->id,
            'channel_type' => 'Channel::Telegram',
        ]);

        // Set webhook URL with Telegram
        $webhookUrl = config('app.url') . '/webhooks/telegram/' . $inbox->id;
        // Call Telegram setWebhook API

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update Telegram channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Telegram', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'bot_token' => 'string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from Telegram.
     */
    public function webhook(Request $request, string $inboxId): JsonResponse
    {
        $inbox = Inbox::findOrFail($inboxId);
        
        // Process Telegram update
        // Dispatch job for message processing

        return response()->json(['status' => 'ok']);
    }

    /**
     * Get bot info from Telegram.
     */
    public function getBotInfo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bot_token' => 'required|string',
        ]);

        // Call Telegram getMe API
        $botInfo = [];

        return response()->json(['data' => $botInfo]);
    }
}
