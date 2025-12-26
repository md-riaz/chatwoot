<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ]);

        // Create the inbox with WhatsApp channel
        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => 'Channel::Whatsapp',
        ]);

        // Create the channel (would be a polymorphic relationship)
        // In a full implementation, this would create a Channels\Whatsapp model

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
        abort_unless($inbox->channel_type === 'Channel::Whatsapp', 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'provider_config' => 'array',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from WhatsApp.
     */
    public function webhook(Request $request): JsonResponse
    {
        // Verify webhook signature
        // Process incoming messages
        // This would typically dispatch a job

        return response()->json(['status' => 'received']);
    }

    /**
     * Verify webhook (for WhatsApp Cloud API).
     */
    public function verifyWebhook(Request $request): mixed
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
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
