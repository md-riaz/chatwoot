<?php

namespace App\Actions\Voice;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Inbox;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ContactInbox;
use App\Services\Voice\Provider\Twilio\AdapterService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class InitiateOutboundCallAction
{
    use AsAction;

    public function __construct(
        private AdapterService $twilioAdapter,
        private CreateCallMessageAction $createCallMessageAction
    ) {}

    /**
     * Initiate an outbound call to a contact.
     */
    public function handle(Account $account, Inbox $inbox, User $user, Contact $contact): array
    {
        if (empty($contact->phone_number)) {
            throw new \InvalidArgumentException('Contact phone number required');
        }

        if (!$user) {
            throw new \InvalidArgumentException('Agent required');
        }

        $timestamp = now()->timestamp;

        return DB::transaction(function () use ($account, $inbox, $user, $contact, $timestamp) {
            $contactInbox = $this->ensureContactInbox($contact, $inbox);
            $conversation = $this->createConversation($account, $inbox, $contact, $contactInbox);
            $conferenceSid = $this->generateConferenceSid($conversation);
            $callSid = $this->initiateCall($inbox, $contact);
            $this->updateConversation($conversation, $callSid, $conferenceSid, $user, $timestamp);
            $this->createVoiceMessage($conversation, $callSid, $conferenceSid, $user, $contact, $inbox, $timestamp);

            return [
                'conversation' => $conversation->fresh(),
                'call_sid' => $callSid,
                'conference_sid' => $conferenceSid,
            ];
        });
    }

    private function ensureContactInbox(Contact $contact, Inbox $inbox): ContactInbox
    {
        return ContactInbox::firstOrCreate(
            [
                'contact_id' => $contact->id,
                'inbox_id' => $inbox->id,
            ],
            ['source_id' => $contact->phone_number]
        );
    }

    private function createConversation(Account $account, Inbox $inbox, Contact $contact, ContactInbox $contactInbox): Conversation
    {
        return $account->conversations()->create([
            'contact_inbox_id' => $contactInbox->id,
            'inbox_id' => $inbox->id,
            'contact_id' => $contact->id,
            'status' => Conversation::STATUS_OPEN,
            'last_activity_at' => now(),
        ]);
    }

    private function generateConferenceSid(Conversation $conversation): string
    {
        return "conf_{$conversation->id}";
    }

    private function initiateCall(Inbox $inbox, Contact $contact): string
    {
        $result = $this->twilioAdapter->initiateCall($inbox->channel, $contact->phone_number);
        return $result['call_sid'];
    }

    private function updateConversation(Conversation $conversation, string $callSid, string $conferenceSid, User $user, int $timestamp): void
    {
        $attrs = [
            'call_direction' => 'outbound',
            'call_status' => 'ringing',
            'agent_id' => $user->id,
            'conference_sid' => $conferenceSid,
            'meta' => ['initiated_at' => $timestamp],
        ];

        $conversation->update([
            'identifier' => $callSid,
            'additional_attributes' => $attrs,
            'last_activity_at' => now(),
        ]);
    }

    private function createVoiceMessage(Conversation $conversation, string $callSid, string $conferenceSid, User $user, Contact $contact, Inbox $inbox, int $timestamp): void
    {
        $this->createCallMessageAction->handle(
            $conversation,
            'outbound',
            [
                'call_sid' => $callSid,
                'status' => 'ringing',
                'conference_sid' => $conferenceSid,
                'from_number' => $inbox->channel->phone_number,
                'to_number' => $contact->phone_number,
            ],
            $user,
            ['created_at' => now(), 'ringing_at' => now()]
        );
    }
}