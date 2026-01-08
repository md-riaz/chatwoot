<?php

namespace App\Actions\Voice;

use App\Models\Account;
use App\Models\Conversation;
use App\Services\Voice\Conference\ManagerService;
use Lorisleiva\Actions\Concerns\AsAction;

class ProcessConferenceEventAction
{
    use AsAction;

    private const CONFERENCE_EVENT_PATTERNS = [
        '/conference-start/i' => 'start',
        '/participant-join/i' => 'join',
        '/participant-leave/i' => 'leave',
        '/conference-end/i' => 'end',
    ];

    public function __construct(
        private ManagerService $conferenceManager
    ) {}

    /**
     * Process a conference event from Twilio webhook.
     */
    public function handle(
        Account $account,
        string $callSid,
        ?string $statusCallbackEvent,
        ?string $conferenceSid = null,
        ?string $participantLabel = null
    ): void {
        $event = $this->mapConferenceEvent($statusCallbackEvent);
        
        if (!$event) {
            return;
        }

        $conversation = $this->findConversationForConference($account, $conferenceSid, $callSid);
        
        if (!$conversation) {
            return;
        }

        $this->conferenceManager->processEvent(
            $conversation,
            $event,
            $callSid,
            $participantLabel ?? ''
        );
    }

    private function mapConferenceEvent(?string $event): ?string
    {
        if (!$event) {
            return null;
        }

        foreach (self::CONFERENCE_EVENT_PATTERNS as $pattern => $mapped) {
            if (preg_match($pattern, $event)) {
                return $mapped;
            }
        }

        return null;
    }

    private function findConversationForConference(Account $account, ?string $conferenceSid, string $callSid): ?Conversation
    {
        // First try to find by conference SID
        if ($conferenceSid) {
            $conversation = $account->conversations()
                ->whereJsonContains('additional_attributes->conference_sid', $conferenceSid)
                ->first();
            
            if ($conversation) {
                return $conversation;
            }
        }

        // Fallback to call SID
        return $account->conversations()->where('identifier', $callSid)->first();
    }
}