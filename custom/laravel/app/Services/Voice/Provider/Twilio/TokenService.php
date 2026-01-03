<?php

namespace App\Services\Voice\Provider\Twilio;

use App\Models\Account;
use App\Models\Inbox;
use App\Models\User;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;

class TokenService
{
    public function __construct(
        private Inbox $inbox,
        private User $user,
        private Account $account
    ) {}

    /**
     * Generate a Twilio access token for WebRTC.
     */
    public function generate(): array
    {
        $config = $this->inbox->channel->provider_config;
        
        $identity = $this->getIdentity();
        $accessToken = $this->createAccessToken($config, $identity);
        
        return [
            'token' => $accessToken->toJWT(),
            'identity' => $identity,
            'voice_enabled' => true,
            'account_sid' => $config['account_sid'],
            'agent_id' => $this->user->id,
            'account_id' => $this->account->id,
            'inbox_id' => $this->inbox->id,
            'phone_number' => $this->inbox->channel->phone_number,
            'twiml_endpoint' => $this->getTwimlUrl(),
            'has_twiml_app' => !empty($config['twiml_app_sid']),
        ];
    }

    private function getIdentity(): string
    {
        return "agent-{$this->user->id}-account-{$this->account->id}";
    }

    private function createAccessToken(array $config, string $identity): AccessToken
    {
        $accessToken = new AccessToken(
            $config['account_sid'],
            $config['api_key_sid'],
            $config['api_key_secret'],
            3600, // 1 hour TTL
            $identity
        );

        $voiceGrant = new VoiceGrant();
        $voiceGrant->setIncomingAllow(true);
        $voiceGrant->setOutgoingApplicationSid($config['twiml_app_sid']);
        $voiceGrant->setOutgoingApplicationParams([
            'account_id' => $this->account->id,
            'agent_id' => $this->user->id,
            'identity' => $identity,
            'client_name' => $identity,
            'accountSid' => $config['account_sid'],
            'is_agent' => 'true',
        ]);

        $accessToken->addGrant($voiceGrant);

        return $accessToken;
    }

    private function getTwimlUrl(): string
    {
        $phoneDigits = ltrim($this->inbox->channel->phone_number, '+');
        return url("/api/v1/webhooks/voice/call/{$phoneDigits}");
    }
}