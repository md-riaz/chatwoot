<?php

namespace App\Services\Channels\Instagram;

use App\Services\Channels\BaseSendOnChannelService;
use App\Models\Message;
use GuzzleHttp\Client;

class SendOnInstagramService extends BaseSendOnChannelService
{
    protected function channelClass(): string
    {
        return \App\Models\Channels\Instagram::class;
    }

    protected function performReply(): void
    {
        $channel = $this->channel();
        $accessToken = $channel->access_token;
        $instagramId = $channel->instagram_id ?: 'me';

        $baseUrl = env('FACEBOOK_GRAPH_URL', 'https://graph.facebook.com');
        $version = env('FACEBOOK_GRAPH_VERSION', 'v15.0');

        $url = "{$baseUrl}/{$version}/{$instagramId}/messages?access_token={$accessToken}";
        $payload = $this->buildPayload();

        try {
            $client = new Client(['timeout' => 15]);
            $resp = $client->post($url, ['json' => $payload]);
            $body = json_decode((string) $resp->getBody(), true);

            if (isset($body['error'])) {
                $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => json_encode($body['error'])]);
                return;
            }

            $this->message->update(['status' => Message::STATUS_SENT]);
            $this->message->sendUpdateEvent();
        } catch (\Exception $e) {
            report($e);
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => $e->getMessage()]);
        }
    }

    protected function buildPayload(): array
    {
        $recipient = ['id' => $this->message->conversation->contact_inbox->source_id];

        $message = ['text' => $this->message->content ?? $this->message->outgoing_content];

        return ['recipient' => $recipient, 'message' => $message];
    }
}
