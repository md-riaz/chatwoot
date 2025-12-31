<?php

namespace App\Services\Channels\Facebook;

use App\Services\Channels\BaseSendOnChannelService;
use App\Models\Message;
use GuzzleHttp\Client;

class SendOnFacebookService extends BaseSendOnChannelService
{
    protected function channelClass(): string
    {
        return \App\Models\Channels\FacebookPage::class;
    }

    protected function performReply(): void
    {
        $channel = $this->channel();
        $pageToken = $channel->page_access_token;

        $baseUrl = env('FACEBOOK_GRAPH_URL', 'https://graph.facebook.com');
        $version = env('FACEBOOK_GRAPH_VERSION', 'v15.0');

        try {
            if (! empty($this->message->content)) {
                $this->sendMessageToFacebook($this->fbTextMessagePayload(), $pageToken, $baseUrl, $version);
            }

            if ($this->message->attachments->isNotEmpty()) {
                foreach ($this->message->attachments as $attachment) {
                    $this->sendMessageToFacebook($this->fbAttachmentPayload($attachment), $pageToken, $baseUrl, $version);
                }
            }
        } catch (\Exception $e) {
            report($e);
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => $e->getMessage()]);
        }
    }

    protected function sendMessageToFacebook(array $payload, string $pageToken, string $baseUrl, string $version): ?array
    {
        $url = "{$baseUrl}/{$version}/me/messages?access_token={$pageToken}";
        $resp = \App\Services\Http\RetryableHttpClient::post($url, ['json' => $payload], 3);
        $body = json_decode((string) $resp->getBody(), true);

        if (isset($body['error'])) {
            // Sensitive errors that indicate deauthorization
            $errMsg = $body['error']['message'] ?? '';
            if (str_contains($errMsg, 'The session has been invalidated') || str_contains($errMsg, 'Error validating access token')) {
                if (method_exists($this->channel(), 'authorizationError')) {
                    $this->channel()->authorizationError();
                }
            }

            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => json_encode($body['error'])]);
            return null;
        }

        if (isset($body['message_id'])) {
            $this->message->update(['source_id' => $body['message_id'], 'status' => Message::STATUS_SENT]);
        } elseif (isset($body['recipient_id'])) {
            $this->message->update(['source_id' => $body['recipient_id'], 'status' => Message::STATUS_SENT]);
        } else {
            $this->message->update(['status' => Message::STATUS_SENT]);
        }

        $this->message->sendUpdateEvent();

        return $body;
    }

    protected function fbTextMessagePayload(): array
    {
        $recipientId = $this->message->conversation->contact_inbox->source_id;

        return [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $this->message->content ?? $this->message->outgoing_content],
            'messaging_type' => 'MESSAGE_TAG',
            'tag' => 'ACCOUNT_UPDATE',
        ];
    }

    protected function fbAttachmentPayload($attachment): array
    {
        $recipientId = $this->message->conversation->contact_inbox->source_id;

        return [
            'recipient' => ['id' => $recipientId],
            'message' => [
                'attachment' => [
                    'type' => in_array($attachment->file_type, ['image', 'audio', 'video', 'file']) ? $attachment->file_type : 'file',
                    'payload' => ['url' => $attachment->download_url],
                ],
            ],
            'messaging_type' => 'MESSAGE_TAG',
            'tag' => 'ACCOUNT_UPDATE',
        ];
    }
}
