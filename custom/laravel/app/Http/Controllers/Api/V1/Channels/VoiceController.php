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
            'provider_config.voice_application_sid' => 'string|nullable',
            'provider_config.messaging_service_sid' => 'string|nullable',
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
            'provider_config.account_sid' => 'string',
            'provider_config.auth_token' => 'string',
            'provider_config.voice_application_sid' => 'string|nullable',
            'provider_config.messaging_service_sid' => 'string|nullable',
            'name' => 'string|max:255',
        ]);

        if (isset($validated['name'])) {
            $inbox->update(['name' => $validated['name']]);
        }

        if (isset($validated['provider_config'])) {
            $config = array_merge($inbox->channel->provider_config ?? [], $validated['provider_config']);
            $inbox->channel->update(['provider_config' => $config]);
        }

        return response()->json(['data' => $inbox->fresh()->load('channel')]);
    }

    /**
     * Handle incoming call webhook (Twilio TwiML).
     */
    public function callTwiml(Request $request, string $phone)
    {
        // 1. Find the Voice channel by phone number
        $voice = Voice::where('phone_number', $phone)->firstOrFail();
        $inbox = $voice->inbox;
        // 2. Resolve conversation (inbound/outbound/agent leg)
        $direction = $request->input('Direction', $request->input('CallDirection', 'inbound'));
        $from = $request->input('From');
        $callSid = $request->input('CallSid');
        $conversation = null;
        if (str_starts_with($from, 'client:')) {
            // Agent leg: find by conversation_id or callSid
            $conversationId = $request->input('conversation_id');
            if ($conversationId) {
                $conversation = $inbox->conversations()->where('display_id', $conversationId)->first();
            } else {
                $conversation = $inbox->conversations()->where('additional_attributes->identifier', $callSid)->first();
            }
        } elseif ($direction === 'inbound') {
            // Inbound: resolve conversation via Action
            try {
                $conversation = \App\Actions\Voice\HandleInboundCallAction::run($inbox, $from, $callSid);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Inbound call action failed', ['error' => $e->getMessage()]);
                $conversation = null;
            }
        } elseif (in_array($direction, ['outbound-api', 'outbound-dial'])) {
            // Outbound: sync outbound leg
            $conversation = $inbox->conversations()->where('additional_attributes->identifier', $callSid)->first();
            if ($conversation) {
                (new CallSessionSyncService($conversation, $callSid, $from, $request->input('To'), $direction))->perform();
            }
        }
        // 3. Ensure conference_sid in additional_attributes
        if ($conversation) {
            $attrs = $conversation->additional_attributes ?? [];
            if (empty($attrs['conference_sid'])) {
                $attrs['conference_sid'] = 'conf_' . $conversation->id;
                $conversation->additional_attributes = $attrs;
                $conversation->save();
            }
            $conferenceSid = $attrs['conference_sid'];
        } else {
            $conferenceSid = 'conf_unknown';
        }
        // 4. Generate TwiML for conference
        $isAgent = str_starts_with($from, 'client:');
        $participantLabel = $isAgent ? 'agent' : 'contact';
        $twiml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Response>'
            . '<Dial>'
            . '<Conference'
            . ' startConferenceOnEnter="' . ($isAgent ? 'true' : 'false') . '"'
            . ' endConferenceOnExit="false"'
            . ' statusCallback="' . url("/api/v1/webhooks/voice/conference_status/{$phone}") . '"'
            . ' statusCallbackEvent="start end join leave"'
            . ' statusCallbackMethod="POST"'
            . ' participantLabel="' . $participantLabel . '"'
            . '>' . $conferenceSid . '</Conference>'
            . '</Dial>'
            . '</Response>';
        return response($twiml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Handle call status webhook.
     */

    /**
     * Handle call status webhook (Twilio events).
     */
    public function status(Request $request, string $phone): JsonResponse
    {
        $callSid = $request->input('CallSid');
        $callStatus = $request->input('CallStatus');
        $voice = Voice::where('phone_number', $phone)->first();
        $inbox = $voice ? $voice->inbox : null;
        $conversation = $inbox ? $inbox->conversations()->where('additional_attributes->identifier', $callSid)->first() : null;
        if ($conversation) {
            (new StatusUpdateService($conversation, $callSid, $callStatus, $request->all()))->perform();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Handle conference status webhook.
     */

    /**
     * Handle conference status webhook (Twilio events).
     */
    public function conferenceStatus(Request $request, string $phone): JsonResponse
    {
        $callSid = $request->input('CallSid');
        $event = $request->input('StatusCallbackEvent');
        $conferenceSid = $request->input('ConferenceSid');
        $participantLabel = $request->input('ParticipantLabel');
        $voice = Voice::where('phone_number', $phone)->first();
        $inbox = $voice ? $voice->inbox : null;
        $conversation = $inbox ? $inbox->conversations()->where('additional_attributes->conference_sid', $conferenceSid)->first() : null;
        if ($conversation) {
            (new ConferenceManager($conversation, $event, $callSid, $participantLabel))->process();
        }
        return response()->json(['success' => true]);
    }
}
