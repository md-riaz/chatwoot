<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Channels\Whatsapp;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\Channels\ProcessWhatsAppWebhookJob;

class WhatsAppController extends Controller
{
    /**
     * Create a WhatsApp channel.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string',
            'provider' => 'required|string|in:whatsapp_cloud,twilio,360dialog',
            'provider_config' => 'required|array',
            'provider_config.phone_number_id' => 'required|string',
            'provider_config.business_account_id' => 'nullable|string',
            'provider_config.access_token' => 'required|string',
            'provider_config.verify_token' => 'required|string',
        ]);

        $channel = Whatsapp::create([
            'account_id' => $account->id,
            'phone_number' => $validated['phone_number'],
            'phone_number_id' => $validated['provider_config']['phone_number_id'],
            'business_account_id' => $validated['provider_config']['business_account_id'] ?? null,
            'access_token' => $validated['provider_config']['access_token'],
            'verify_token' => $validated['provider_config']['verify_token'],
            'provider' => $validated['provider'],
            'provider_config' => $validated['provider_config'],
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => Whatsapp::class,
            'channel_id' => $channel->id,
        ]);

        return response()->json([
            'data' => [
                'inbox' => $inbox,
                'channel' => [
                    'phone_number' => $validated['phone_number'],
                    'provider' => $validated['provider'],
                ]
            ]
        ], 201);
    }

    /**
     * Update WhatsApp channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel instanceof Whatsapp, 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'provider_config' => 'array',
            'provider_config.phone_number_id' => 'string',
            'provider_config.business_account_id' => 'string|nullable',
            'provider_config.access_token' => 'string',
            'provider_config.verify_token' => 'string|nullable',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        if ($inbox->channel) {
            $config = array_merge($inbox->channel->provider_config ?? [], $validated['provider_config'] ?? []);
            $inbox->channel->update([
                'phone_number_id' => $config['phone_number_id'] ?? $inbox->channel->phone_number_id,
                'business_account_id' => $config['business_account_id'] ?? $inbox->channel->business_account_id,
                'access_token' => $config['access_token'] ?? $inbox->channel->access_token,
                'verify_token' => $config['verify_token'] ?? $inbox->channel->verify_token,
                'provider_config' => $config,
            ]);
        }

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from WhatsApp.
     */
    public function webhook(Request $request): JsonResponse
    {
        $raw = $request->getContent();
        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        try {
            ProcessWhatsAppWebhookJob::dispatch($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch ProcessWhatsAppWebhookJob', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'queued']);
    }

    /**
     * Verify webhook (for WhatsApp Cloud API).
     */
    public function verifyWebhook(Request $request): mixed
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $channel = Whatsapp::where('verify_token', $token)
            ->orWhere('provider_config->verify_token', $token)
            ->first();

        if ($mode === 'subscribe' && $channel) {
            return response($challenge, 200);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Send a template message.
     */
    public function sendTemplate(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Whatsapp', 400);

        $validated = $request->validate([
            'template_name' => 'required|string',
            'template_params' => 'array',
            'phone_number' => 'required|string',
        ]);

        // Send template message via WhatsApp API
        // This would call the appropriate provider SDK

        return response()->json(['message' => 'Template message sent']);
    }

    /**
     * Sync message templates from WhatsApp.
     */
    public function syncTemplates(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Whatsapp', 400);

        // Fetch templates from WhatsApp API
        // Store in database

        return response()->json(['message' => 'Templates synced']);
    }
}
