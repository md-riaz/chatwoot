<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Channels\Voice;
use App\Models\Inbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoiceController extends Controller
{
    /**
     * Create a new Voice channel inbox.
     */
    public function create(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|unique:channel_voice,phone_number',
            'provider' => 'required|string|in:twilio',
            'provider_config' => 'required|array',
            'provider_config.account_sid' => 'required|string',
            'provider_config.auth_token' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        $channel = Voice::create([
            'account_id' => $account->id,
            'phone_number' => $validated['phone_number'],
            'provider' => $validated['provider'],
            'provider_config' => $validated['provider_config'],
        ]);

        $inbox = Inbox::create([
            'account_id' => $account->id,
            'name' => $validated['name'],
            'channel_type' => Voice::class,
            'channel_id' => $channel->id,
        ]);

        return response()->json(['data' => $inbox->load('channel')], 201);
    }

    /**
     * Update a Voice channel inbox.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel_type === Voice::class, 404);

        $validated = $request->validate([
            'provider_config' => 'array',
            'name' => 'string|max:255',
        ]);

        if (isset($validated['name'])) {
            $inbox->update(['name' => $validated['name']]);
        }

        if (isset($validated['provider_config'])) {
            $inbox->channel->update(['provider_config' => $validated['provider_config']]);
        }

        return response()->json(['data' => $inbox->fresh()->load('channel')]);
    }

    /**
     * Handle incoming call webhook (Twilio TwiML).
     */
    public function callTwiml(Request $request, string $phone): JsonResponse
    {
        // TODO: Implement TwiML response for incoming calls
        $twiml = '<?xml version="1.0" encoding="UTF-8"?>
        <Response>
            <Say>Thank you for calling. An agent will be with you shortly.</Say>
        </Response>';

        return response($twiml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Handle call status webhook.
     */
    public function status(Request $request, string $phone): JsonResponse
    {
        // TODO: Process call status updates
        return response()->json(['success' => true]);
    }

    /**
     * Handle conference status webhook.
     */
    public function conferenceStatus(Request $request, string $phone): JsonResponse
    {
        // TODO: Process conference status updates
        return response()->json(['success' => true]);
    }
}
