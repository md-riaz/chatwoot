<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\Channels\ProcessSmsWebhookJob;

class SmsController extends Controller
{
    /**
     * Create an SMS channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string',
            'provider' => 'required|string|in:twilio,bandwidth',
            'provider_config' => 'required|array',
            'provider_config.account_sid' => 'required|string',
            'provider_config.auth_token' => 'required|string',
        ]);

        // Create the inbox with SMS channel
        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::Sms',
        ]);

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update SMS channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Sms', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'provider_config' => 'array',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from SMS provider (Twilio).
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            ProcessSmsWebhookJob::dispatch($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch ProcessSmsWebhookJob', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'queued']);
    }

    /**
     * Get available phone numbers from provider.
     */
    public function availableNumbers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_sid' => 'required|string',
            'auth_token' => 'required|string',
            'country' => 'string|size:2',
        ]);

        // Fetch available numbers from Twilio
        $numbers = [];

        return response()->json(['data' => $numbers]);
    }
}
