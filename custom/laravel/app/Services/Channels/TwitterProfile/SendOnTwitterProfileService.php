<?php

namespace App\Services\Channels\TwitterProfile;

use App\Services\Channels\BaseSendOnChannelService;
use App\Services\Http\OAuth1Client;
use App\Models\Message;
use GuzzleHttp\Client;

class SendOnTwitterProfileService extends BaseSendOnChannelService
{
    protected function channelClass(): string
    {
        return \App\Models\Channels\TwitterProfile::class;
    }

    protected function performReply(): void
    {
        $channel = $this->channel();
        $token = $channel->twitter_access_token;
        $tokenSecret = $channel->twitter_access_token_secret;

        $consumerKey = env('TWITTER_CONSUMER_KEY');
        $consumerSecret = env('TWITTER_CONSUMER_SECRET');

        if (empty($consumerKey) || empty($consumerSecret) || empty($token) || empty($tokenSecret)) {
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => 'Twitter credentials missing']);
            return;
        }

        $conversationType = $this->conversation()->additional_attributes['type'] ?? null;

        try {
            if ($conversationType === 'tweet') {
                $this->sendTweetReply($consumerKey, $consumerSecret, $token, $tokenSecret);
            } else {
                $this->sendDirectMessage($consumerKey, $consumerSecret, $token, $tokenSecret);
            }
        } catch (\Exception $e) {
            report($e);
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => $e->getMessage()]);
        }
    }

    protected function sendDirectMessage($consumerKey, $consumerSecret, $token, $tokenSecret): void
    {
        $recipientId = $this->conversation()->contact_inbox->source_id;
        $text = $this->message->content ?? $this->message->outgoing_content;

        $url = 'https://api.twitter.com/1.1/direct_messages/events/new.json';

        $event = [
            'type' => 'message_create',
            'message_create' => [
                'target' => ['recipient_id' => $recipientId],
                'message_data' => ['text' => $text],
            ],
        ];

        $body = ['event' => $event];

        $oauth = [
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'token' => $token,
            'token_secret' => $tokenSecret,
        ];

        $authHeader = OAuth1Client::oauth1Header('POST', $url, array_merge($oauth, ['nonce' => bin2hex(random_bytes(8)), 'timestamp' => time()]));

        $resp = \App\Services\Http\RetryableHttpClient::post($url, ['headers' => ['Authorization' => $authHeader, 'Content-Type' => 'application/json'], 'body' => json_encode($body)], 3);
        $resBody = json_decode((string) $resp->getBody(), true);

        if (in_array($resp->getStatusCode(), [200, 201], true)) {
            $this->message->update(['status' => Message::STATUS_SENT]);
            $this->message->sendUpdateEvent();
        } else {
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => json_encode($resBody)]);
        }
    }

    protected function sendTweetReply($consumerKey, $consumerSecret, $token, $tokenSecret): void
    {
        $replyTo = $this->message->in_reply_to; // id of reply message - ensure exists
        $replyToSourceId = null;
        if ($replyTo) {
            $replyMsg = $this->conversation()->messages()->find($replyTo);
            $replyToSourceId = $replyMsg->source_id ?? null;
        } else {
            $lastIncoming = $this->conversation()->messages()->where('message_type', 0)->latest()->first();
            $replyToSourceId = $lastIncoming->source_id ?? null;
        }

        $screenName = '';
        if ($this->message->in_reply_to) {
            $replyMsg = $this->conversation()->messages()->find($this->message->in_reply_to);
            if ($replyMsg && $replyMsg->sender) {
                $screenName = '@' . ($replyMsg->sender->additional_attributes['screen_name'] ?? '');
            }
        }

        $status = trim(($screenName ? $screenName . ' ' : '') . ($this->message->content ?? $this->message->outgoing_content));

        $url = 'https://api.twitter.com/1.1/statuses/update.json';

        $params = ['status' => $status];
        if ($replyToSourceId) {
            $params['in_reply_to_status_id'] = $replyToSourceId;
        }

        $oauth = [
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'token' => $token,
            'token_secret' => $tokenSecret,
        ];

        $authHeader = OAuth1Client::oauth1Header('POST', $url, array_merge($oauth, ['nonce' => bin2hex(random_bytes(8)), 'timestamp' => time()]), $params);

        $client = new Client(['timeout' => 15]);
        $resp = $client->post($url, ['headers' => ['Authorization' => $authHeader], 'form_params' => $params]);
        $resBody = json_decode((string) $resp->getBody(), true);

        if ($resp->getStatusCode() === 200) {
            $this->message->update(['source_id' => $resBody['id_str'] ?? null, 'status' => Message::STATUS_SENT]);
            $this->message->sendUpdateEvent();
        } else {
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => json_encode($resBody)]);
        }
    }
}
