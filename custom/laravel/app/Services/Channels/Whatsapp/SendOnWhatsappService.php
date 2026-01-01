<?php

namespace App\Services\Channels\Whatsapp;

use App\Services\Channels\BaseSendOnChannelService;
use App\Models\Message;

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
            // Use provider service for WhatsApp Cloud and 360Dialog
            if (in_array($provider, ['whatsapp_cloud', 'facebook', '360dialog', 'default'])) {
                $this->sendViaProviderService($channel);
            } elseif ($provider === 'twilio' || ($channel instanceof \App\Models\Channels\TwilioSms && $channel->isWhatsapp())) {
                // Use Twilio SMS service when Twilio medium is whatsapp
                $svc = new \App\Services\Channels\TwilioSms\SendOnTwilioSmsService($this->message);
                $svc->perform();
            } else {
                // Unknown provider - fail
                $this->message->update([
                    'status' => Message::STATUS_FAILED, 
                    'external_error' => 'Unknown WhatsApp provider: ' . $provider
                ]);
            }
        } catch (\Exception $e) {
            report($e);
            $this->message->update([
                'status' => Message::STATUS_FAILED, 
                'external_error' => $e->getMessage()
            ]);
        }
    }

    protected function sendViaProviderService($channel): void
    {
        try {
            $to = $this->message->conversation->contact_inbox->source_id;
            $messageId = $channel->sendMessage($to, $this->message);

            if ($messageId) {
                $this->message->update([
                    'source_id' => $messageId,
                    'status' => Message::STATUS_SENT
                ]);
                $this->message->sendUpdateEvent();
            } else {
                $this->message->update([
                    'status' => Message::STATUS_FAILED,
                    'external_error' => 'Failed to send message via provider service'
                ]);
            }
        } catch (\Exception $e) {
            // Check if it's an authorization error
            if (str_contains($e->getMessage(), 'Invalid OAuth access token') || 
                str_contains($e->getMessage(), 'Authentication') || 
                str_contains($e->getMessage(), 'Permission')) {
                if (method_exists($channel, 'authorizationError')) {
                    $channel->authorizationError();
                }
            }

            throw $e;
        }
    }
}

