<?php

namespace App\Services\Channels\Whatsapp;

use App\Services\Channels\BaseSendOnChannelService;
use App\Models\Message;
use GuzzleHttp\Client;

class SendOnWhatsappService extends BaseSendOnChannelService
{
    protected function channelClass(): string
    {
        return \App\Models\Channels\Whatsapp::class;
    }

    protected function performReply(): void
    {
        $channel = $this->channel();
        $provider = $channel->provider ?? ($channel->provider_config['provider'] ?? 'unknown');

        try {
            if ($provider === 'whatsapp_cloud' || $provider === 'facebook') {
                $this->sendViaFacebookCloud($channel);
            } elseif ($provider === 'twilio' || ($channel instanceof \App\Models\Channels\TwilioSms && $channel->isWhatsapp())) {
                // Use Twilio SMS service when Twilio medium is whatsapp
                $svc = new \App\Services\Channels\TwilioSms\SendOnTwilioSmsService($this->message);
                $svc->perform();
            } else {
                // Unknown provider - fail
                $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => 'Unknown WhatsApp provider']);
            }
        } catch (\Exception $e) {
            report($e);
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => $e->getMessage()]);
        }
    }

    protected function sendViaFacebookCloud($channel): void
    {
        $cfg = $channel->provider_config ?? [];
        $phoneNumberId = $cfg['phone_number_id'] ?? null;
        $token = $cfg['access_token'] ?? null;

        if (empty($phoneNumberId) || empty($token)) {
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => 'WhatsApp Cloud configuration missing']);
            return;
        }

        $baseUrl = env('FACEBOOK_GRAPH_URL', 'https://graph.facebook.com');
        $version = env('FACEBOOK_GRAPH_VERSION', 'v15.0');
        $url = "{$baseUrl}/{$version}/{$phoneNumberId}/messages";

        $to = $this->message->conversation->contact_inbox->source_id;

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $this->message->content ?? $this->message->outgoing_content],
        ];

        try {
            $resp = \App\Services\Http\RetryableHttpClient::post($url, ['headers' => ['Authorization' => "Bearer {$token}"], 'json' => $payload], 3);
            $body = json_decode((string) $resp->getBody(), true);

            if (! empty($body['messages'][0]['id'] ?? null)) {
                $this->message->update(['source_id' => $body['messages'][0]['id'], 'status' => Message::STATUS_SENT]);
                $this->message->sendUpdateEvent();
                return;
            }

            if (! empty($body['error'] ?? null)) {
                $errMsg = $body['error']['message'] ?? '';
                if (str_contains($errMsg, 'Invalid OAuth access token') || str_contains($errMsg, 'Authentication') || str_contains($errMsg, 'Permission')) {
                    if (method_exists($this->channel(), 'authorizationError')) {
                        $this->channel()->authorizationError();
                    }
                }
            }

            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => json_encode($body)]);
        } catch (\Exception $e) {
            report($e);
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => $e->getMessage()]);
        }
    }
}

