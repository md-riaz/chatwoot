<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Data\Channels\InboundMessageData;
use App\Models\Channels\Line as LineChannel;
use App\Models\Account;
use App\Models\Inbox;
use App\Services\Channels\InboundMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $channel = LineChannel::create([
            'account_id' => $account->id,
            'line_channel_id' => $validated['channel_id'],
            'line_channel_secret' => $validated['channel_secret'],
            'line_channel_token' => $validated['channel_token'],
        ]);

        $inbox = Inbox::create([
            'name' => $validated['name'],
            'account_id' => $account->id,
            'channel_type' => LineChannel::class,
            'channel_id' => $channel->id,
        ]);

        return response()->json(['data' => $inbox], 201);
    }

    /**
     * Update LINE channel settings.
     */
    public function update(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);
        abort_unless($inbox->channel instanceof LineChannel, 400);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'channel_token' => 'string',
        ]);

        $inbox->update(['name' => $validated['name'] ?? $inbox->name]);

        if ($inbox->channel) {
            $inbox->channel->update(array_filter([
                'line_channel_token' => $validated['channel_token'] ?? null,
            ], fn ($value) => $value !== null));
        }

        return response()->json(['data' => $inbox]);
    }

    /**
     * Receive webhook from LINE.
     */
    public function webhook(Request $request, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->channel instanceof LineChannel, 400);

        $payload = $request->getContent();

        if (! $this->validSignature($request, $inbox->channel)) {
            Log::warning('LINE webhook rejected', ['reason' => 'invalid_signature']);
            return response()->json(['error' => 'invalid_signature'], 403);
        }

        $body = json_decode($payload, true);
        foreach ($body['events'] ?? [] as $event) {
            if (($event['type'] ?? null) !== 'message') {
                continue;
            }

            $message = $event['message'] ?? [];
            $contactId = $event['source']['userId'] ?? null;
            $text = $message['text'] ?? null;
            $externalId = $message['id'] ?? null;

            if (! $contactId || ! $text) {
                continue;
            }

            app(InboundMessageService::class)->ingest(new InboundMessageData(
                account_id: $inbox->account_id,
                inbox_id: $inbox->id,
                contact_identifier: 'line:' . $contactId,
                contact_source: 'line',
                contact_name: null,
                contact_email: null,
                contact_phone: null,
                provider_contact_id: $contactId,
                content: $text,
                content_type: \App\Models\Message::CONTENT_TEXT,
                external_source_id: (string) $externalId,
                attachments: [],
                metadata: ['raw' => $event]
            ));
        }

        return response()->json(['status' => 'queued']);
    }

    private function validSignature(Request $request, LineChannel $channel): bool
    {
        $signature = $request->header('X-Line-Signature');
        if (! $signature || ! $channel->line_channel_secret) {
            return false;
        }

        $hash = base64_encode(hash_hmac('sha256', $request->getContent(), $channel->line_channel_secret, true));

        return hash_equals($hash, $signature);
    }
}
