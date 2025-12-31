<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Channels\TwilioSms;
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
            'provider_config.messaging_service_sid' => 'nullable|string',
        ]);

        $channel = TwilioSms::create([
            'account_id' => $account->id,
            'phone_number' => $validated['phone_number'],
            'messaging_service_sid' => $validated['provider_config']['messaging_service_sid'] ?? null,
            'account_sid' => $validated['provider_config']['account_sid'],
            'auth_token' => $validated['provider_config']['auth_token'],
            'medium' => TwilioSms::MEDIUM_SMS,
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => TwilioSms::class,
            'channel_id' => $channel->id,
        ]);

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update SMS channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel instanceof TwilioSms, 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'provider_config' => 'array',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        if (isset($validated['provider_config'])) {
            $inbox->channel->update(array_filter([
                'phone_number' => $validated['provider_config']['phone_number'] ?? null,
                'messaging_service_sid' => $validated['provider_config']['messaging_service_sid'] ?? null,
                'account_sid' => $validated['provider_config']['account_sid'] ?? null,
                'auth_token' => $validated['provider_config']['auth_token'] ?? null,
            ], fn ($value) => $value !== null));
        }

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from SMS provider (Twilio).
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        $to = $payload['To'] ?? $payload['to'] ?? null;
        $inbox = null;
        if ($to) {
            $inbox = Inbox::whereHasMorph('channel', [TwilioSms::class], function ($q) use ($to) {
                $q->where('phone_number', (string) $to);
            })->first();
        }

        if (! $inbox) {
            Log::warning('SMS webhook: no inbox found for phone number', ['to' => $to]);
            return response()->json(['error' => 'inbox_not_found'], 404);
        }

        if ($inbox && ! $this->validateTwilioSignature($request, $inbox->channel)) {
            Log::warning('SMS webhook rejected due to invalid signature', ['to' => $to]);
            return response()->json(['error' => 'invalid_signature'], 403);
        }

        try {
            ProcessSmsWebhookJob::dispatch($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch ProcessSmsWebhookJob', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'queued']);
    }

    private function validateTwilioSignature(Request $request, ?TwilioSms $channel): bool
    {
        if (! $channel?->auth_token) {
            return true;
        }

        $signature = $request->header('X-Twilio-Signature');
        if (! $signature) {
            return false;
        }

        $url = $request->fullUrl();
        $params = $request->post();
        ksort($params);
        $data = $url;
        foreach ($params as $key => $value) {
            $data .= $key . $value;
        }

        $computed = base64_encode(hash_hmac('sha1', $data, $channel->auth_token, true));

        return hash_equals($computed, $signature);
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
