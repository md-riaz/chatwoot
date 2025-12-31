<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Jobs\Channels\ProcessTelegramWebhookJob;
use App\Models\Account;
use App\Models\Inbox;
use App\Models\Channels\Telegram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Create a Telegram channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'bot_token' => 'required|string',
            'webhook_secret' => 'string|nullable',
            'name' => 'string|max:255',
        ]);

        $channel = Telegram::create([
            'account_id' => $account->id,
            'bot_token' => $validated['bot_token'],
            'bot_name' => $validated['name'] ?? 'Telegram Bot',
            'webhook_secret' => $validated['webhook_secret'] ?? null,
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'] ?? 'Telegram Bot',
            'account_id' => $account->id,
            'channel_type' => Telegram::class,
            'channel_id' => $channel->id,
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
        abort_unless($inbox->channel instanceof Telegram, 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'bot_token' => 'string',
            'webhook_secret' => 'string|nullable',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        if ($inbox->channel) {
            $inbox->channel->update(array_filter([
                'bot_token' => $validated['bot_token'] ?? null,
                'bot_name' => $validated['name'] ?? null,
                'webhook_secret' => $validated['webhook_secret'] ?? null,
            ], fn ($value) => $value !== null));
        }

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from Telegram.
     */
    public function webhook(Request $request, string $inboxId): JsonResponse
    {
        $inbox = Inbox::with('channel')->findOrFail($inboxId);

        if ($inbox->channel && $request->header('X-Telegram-Bot-Api-Secret-Token')) {
            $expected = $inbox->channel->webhook_secret ?? null;
            if ($expected && ! hash_equals($expected, $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
                Log::warning('Telegram webhook rejected: secret mismatch', ['inbox_id' => $inbox->id]);
                return response()->json(['error' => 'invalid_signature'], 403);
            }
        }

        ProcessTelegramWebhookJob::dispatch($inbox->id, $request->all());

        return response()->json(['status' => 'queued']);
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
