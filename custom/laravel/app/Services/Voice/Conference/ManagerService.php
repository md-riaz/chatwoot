<?php

namespace App\Services\Voice\Conference;

use App\Models\Conversation;
use App\Services\Voice\CallStatus\ManagerService as CallStatusManager;

class ManagerService
{
    public function __construct(
        private CallStatusManager $callStatusManager
    ) {}

    /**
     * Process a conference event.
     */
    public function processEvent(
        Conversation $conversation,
        string $event,
        string $callSid,
        string $participantLabel
    ): void {
        switch ($event) {
            case 'start':
                $this->ensureConferenceSid($conversation);
                $this->markRinging($conversation, $callSid);
                break;
            case 'join':
                if ($this->isAgentParticipant($participantLabel)) {
                    $this->markInProgress($conversation, $callSid);
                }
                break;
            case 'leave':
                $this->handleLeave($conversation, $callSid);
                break;
            case 'end':
                $this->finalizeConference($conversation, $callSid);
                break;
        }
    }

    private function ensureConferenceSid(Conversation $conversation): void
    {
        $attrs = $conversation->additional_attributes ?? [];
        
        if (!empty($attrs['conference_sid'])) {
            return;
        }

        $attrs['conference_sid'] = "conf_{$conversation->id}";
        $conversation->update(['additional_attributes' => $attrs]);
    }

    private function markRinging(Conversation $conversation, string $callSid): void
    {
        $currentStatus = $conversation->additional_attributes['call_status'] ?? null;
        
        if ($currentStatus) {
            return;
        }

        $this->callStatusManager->processStatusUpdate($conversation, $callSid, 'ringing');
    }

    private function markInProgress(Conversation $conversation, string $callSid): void
    {
        $this->callStatusManager->processStatusUpdate(
            $conversation,
            $callSid,
            'in-progress',
            null,
            now()->timestamp
        );
    }

    private function handleLeave(Conversation $conversation, string $callSid): void
    {
        $currentStatus = $conversation->additional_attributes['call_status'] ?? null;

        switch ($currentStatus) {
            case 'ringing':
                $this->callStatusManager->processStatusUpdate(
                    $conversation,
                    $callSid,
                    'no-answer',
                    null,
                    now()->timestamp
                );
                break;
            case 'in-progress':
                $this->callStatusManager->processStatusUpdate(
                    $conversation,
                    $callSid,
                    'completed',
                    null,
                    now()->timestamp
                );
                break;
        }
    }

    private function finalizeConference(Conversation $conversation, string $callSid): void
    {
        $currentStatus = $conversation->additional_attributes['call_status'] ?? null;
        
        if (in_array($currentStatus, ['completed', 'no-answer', 'failed'])) {
            return;
        }

        $this->callStatusManager->processStatusUpdate(
            $conversation,
            $callSid,
            'completed',
            null,
            now()->timestamp
        );
    }

    private function isAgentParticipant(string $participantLabel): bool
    {
        return str_starts_with($participantLabel, 'agent');
    }
}