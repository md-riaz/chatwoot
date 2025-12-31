<?php

namespace App\Services\Channels\TwilioSms;

use App\Services\Channels\BaseSendOnChannelService;
use App\Models\Message;
use GuzzleHttp\Client;

class SendOnTwilioSmsService extends BaseSendOnChannelService
{
    protected function channelClass(): string
    {
        return \App\Models\Channels\TwilioSms::class;
    }

    protected function performReply(): void
    {
        $channel = $this->channel();

        try {
            if ($this->hasTemplateParams()) {
                $twilioMessage = $this->sendTemplateMessage($channel);
            } else {
                $twilioMessage = $this->sendSessionMessage($channel);
            }

            if (! empty($twilioMessage['sid'] ?? null)) {
                $this->message->update(['source_id' => $twilioMessage['sid']]);
            }
        } catch (\Exception $e) {
            report($e);
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => $e->getMessage()]);
        }
    }

    protected function hasTemplateParams(): bool
    {
        return ! empty($this->message->additional_attributes['template_params'] ?? null);
    }

    protected function sendSessionMessage($channel): ?array
    {
        $to = $this->message->conversation->contact_inbox->source_id ?? null;
        $body = $this->message->content ?? '';

        $accountSid = $channel->account_sid;
        $authToken = $channel->auth_token;
        $messagingServiceSid = $channel->messaging_service_sid;
        $from = $channel->phone_number;

        $params = [
            'To' => $to,
            'Body' => $body,
        ];

        if (! empty($messagingServiceSid)) {
            $params['MessagingServiceSid'] = $messagingServiceSid;
        } elseif (! empty($from)) {
            $params['From'] = $from;
        }

        $media = $this->attachments();
        if (! empty($media)) {
            foreach ($media as $i => $m) {
                $params['MediaUrl' . ($i + 1)] = $m;
            }
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        $resp = \App\Services\Http\RetryableHttpClient::post($url, ['form_params' => $params, 'client' => ['auth' => [$accountSid, $authToken]]], 3);
        $body = json_decode((string) $resp->getBody(), true);
        $this->message->update(['status' => Message::STATUS_SENT]);
        $this->message->sendUpdateEvent();

        return $body;
    }

    protected function sendTemplateMessage($channel): ?array
    {
        // Very small template processor: attempt to find content_sid in template_params
        $tpl = $this->message->additional_attributes['template_params'] ?? null;
        $contentSid = $tpl['content_sid'] ?? null;
        if (empty($contentSid)) {
            $this->message->update(['status' => Message::STATUS_FAILED, 'external_error' => 'Template not found']);
            return null;
        }

        // Build send params similar to Twilio template send
        $to = $this->message->conversation->contact_inbox->source_id ?? null;

        $sendParams = ['To' => $to, 'ContentSid' => $contentSid];
        if (! empty($tpl['content_variables'])) {
            $sendParams['ContentVariables'] = json_encode($tpl['content_variables']);
        }

        // Use Twilio client via HTTP
        $accountSid = $channel->account_sid;
        $authToken = $channel->auth_token;

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
        $resp = \App\Services\Http\RetryableHttpClient::post($url, ['form_params' => $sendParams, 'client' => ['auth' => [$accountSid, $authToken]]], 3);
        $body = json_decode((string) $resp->getBody(), true);

        $this->message->update(['status' => Message::STATUS_SENT]);
        $this->message->sendUpdateEvent();

        return $body;
    }

    protected function attachments(): array
    {
        return $this->message->attachments->map(fn($a) => $a->download_url)->filter()->values()->all();
    }
}

