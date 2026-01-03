<?php

namespace App\Services\Voice\Provider\Twilio;

use App\Models\Channels\Voice;
use Twilio\Rest\Client;

class AdapterService
{
    /**
     * Initiate a call via Twilio.
     */
    public function initiateCall(Voice $channel, string $toNumber, ?string $conferenceSid = null, ?int $agentId = null): array
    {
        $client = $this->getTwilioClient($channel);
        
        $call = $client->calls->create([
            'from' => $channel->phone_number,
            'to' => $toNumber,
            'url' => $this->getTwimlUrl($channel),
            'statusCallback' => $this->getStatusCallbackUrl($channel),
            'statusCallbackEvent' => [
                'initiated', 'ringing', 'answered', 'completed', 'failed', 'busy', 'no-answer', 'canceled'
            ],
            'statusCallbackMethod' => 'POST',
        ]);

        return [
            'provider' => 'twilio',
            'call_sid' => $call->sid,
            'status' => $call->status,
            'call_direction' => 'outbound',
            'requires_agent_join' => true,
            'agent_id' => $agentId,
            'conference_sid' => $conferenceSid,
        ];
    }

    private function getTwilioClient(Voice $channel): Client
    {
        $config = $channel->provider_config;
        
        return new Client(
            $config['account_sid'],
            $config['auth_token']
        );
    }

    private function getTwimlUrl(Voice $channel): string
    {
        $phoneDigits = ltrim($channel->phone_number, '+');
        return url("/api/v1/webhooks/voice/call/{$phoneDigits}");
    }

    private function getStatusCallbackUrl(Voice $channel): string
    {
        $phoneDigits = ltrim($channel->phone_number, '+');
        return url("/api/v1/webhooks/voice/status/{$phoneDigits}");
    }
}