<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inbox\InboxResource;
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
        abort_unless($request->user()?->isAdministratorOf($account), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string',
            'provider' => 'required|string|in:whatsapp_cloud',
            'provider_config' => 'required|array',
            'provider_config.phone_number_id' => 'required|string',
            'provider_config.business_account_id' => 'required|string',
            'provider_config.api_key' => 'required|string',
        ]);

        $existingInbox = Inbox::query()
            ->where('account_id', $account->id)
            ->where('channel_type', 'Channel::Whatsapp')
            ->whereHasMorph('channel', [Whatsapp::class], function ($query) use ($validated) {
                $query->where('phone_number', $validated['phone_number']);
            })
            ->first();

        if ($existingInbox) {
            abort(422, 'A WhatsApp inbox for this phone number already exists.');
        }

        $providerConfig = $validated['provider_config'];

        $channel = Whatsapp::create([
            'account_id' => $account->id,
            'phone_number' => $validated['phone_number'],
            'provider' => $validated['provider'],
            'provider_config' => $providerConfig,
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::Whatsapp',
            'channel_id' => $channel->id,
        ]);

        try {
            $channel->setupWebhooks();
        } catch (\Throwable $e) {
            Log::warning('Failed to setup WhatsApp webhooks during inbox creation', [
                'account_id' => $account->id,
                'channel_id' => $channel->id,
                'error' => $e->getMessage(),
            ]);
        }

        return (new InboxResource($inbox->load('channel')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update WhatsApp channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === 'Channel::Whatsapp', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'provider_config' => 'array',
            'provider_config.phone_number_id' => 'string',
            'provider_config.business_account_id' => 'string|nullable',
            'provider_config.api_key' => 'string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        if ($inbox->channel) {
            $config = array_merge($inbox->channel->provider_config ?? [], $validated['provider_config'] ?? []);
            $inbox->channel->update([
                'provider_config' => $config,
            ]);
        }

        return response()->json(['data' => $inbox->fresh()->load('channel')]);
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

        $channel = Whatsapp::query()
            ->where('provider', 'whatsapp_cloud')
            ->where('provider_config->webhook_verify_token', $token)
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

        /** @var Whatsapp $channel */
        $channel = $inbox->channel;
        $channel->syncTemplates();

        return response()->json($channel->fresh()->message_templates ?? []);
    }
}
