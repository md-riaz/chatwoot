<?php

namespace App\Services\Voice\Provider\Twilio;

use App\Models\Conversation;
use App\Models\User;
use Twilio\Rest\Client;

class ConferenceService
{
    private ?Client $twilioClient = null;

    /**
     * Ensure a conference SID exists for the conversation.
     */
    public function ensureConferenceSid(Conversation $conversation): string
    {
        $attrs = $conversation->additional_attributes ?? [];
        $existing = $attrs['conference_sid'] ?? null;
        
        if ($existing) {
            return $existing;
        }

        $sid = $this->generateConferenceName($conversation);
        $attrs['conference_sid'] = $sid;
        $conversation->update(['additional_attributes' => $attrs]);
        
        return $sid;
    }

    /**
     * Mark that an agent has joined the conference.
     */
    public function markAgentJoined(Conversation $conversation, User $user): void
    {
        $attrs = $conversation->additional_attributes ?? [];
        $attrs['agent_joined'] = true;
        $attrs['joined_at'] = now()->timestamp;
        $attrs['joined_by'] = [
            'id' => $user->id,
            'name' => $user->name,
        ];
        
        $conversation->update(['additional_attributes' => $attrs]);
    }

    /**
     * End the conference by updating all in-progress conferences.
     */
    public function endConference(Conversation $conversation): void
    {
        $conferenceName = $this->generateConferenceName($conversation);
        $client = $this->getTwilioClient($conversation);
        
        $conferences = $client->conferences->read([
            'friendlyName' => $conferenceName,
            'status' => 'in-progress',
        ]);

        foreach ($conferences as $conference) {
            $client->conferences($conference->sid)->update(['status' => 'completed']);
        }
    }

    private function generateConferenceName(Conversation $conversation): string
    {
        return "conf_{$conversation->id}";
    }

    private function getTwilioClient(Conversation $conversation): Client
    {
        if ($this->twilioClient) {
            return $this->twilioClient;
        }

        $config = $conversation->inbox->channel->provider_config;
        
        $this->twilioClient = new Client(
            $config['account_sid'],
            $config['auth_token']
        );

        return $this->twilioClient;
    }
}