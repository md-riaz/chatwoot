<?php

namespace App\Services\Voice\Conference;

use App\Models\Conversation;
use App\Services\Voice\StatusUpdateService;

class Manager
{
    protected $conversation;
    protected $event;
    protected $callSid;
    protected $participantLabel;

    public function __construct(Conversation $conversation, $event, $callSid, $participantLabel = null)
    {
        $this->conversation = $conversation;
        $this->event = $event;
        $this->callSid = $callSid;
        $this->participantLabel = $participantLabel;
    }

    public function process()
    {
        switch ($this->event) {
            case 'start':
                $this->ensureConferenceSid();
                $this->markRinging();
                break;
            case 'join':
                if ($this->isAgentParticipant()) {
                    $this->markInProgress();
                }
                break;
            case 'leave':
                $this->handleLeave();
                break;
            case 'end':
                $this->finalizeConference();
                break;
        }
    }

    protected function ensureConferenceSid()
    {
        $attrs = $this->conversation->additional_attributes ?? [];
        if (empty($attrs['conference_sid'])) {
            $attrs['conference_sid'] = 'conf_' . $this->conversation->id;
            $this->conversation->additional_attributes = $attrs;
            $this->conversation->save();
        }
    }

    protected function markRinging()
    {
        $service = new StatusUpdateService($this->conversation, $this->callSid, 'ringing');
        $service->perform();
    }

    protected function markInProgress()
    {
        $service = new StatusUpdateService($this->conversation, $this->callSid, 'in-progress');
        $service->perform();
    }

    protected function handleLeave()
    {
        $attrs = $this->conversation->additional_attributes ?? [];
        $status = $attrs['call_status'] ?? null;
        if ($status === 'ringing') {
            $service = new StatusUpdateService($this->conversation, $this->callSid, 'no-answer');
            $service->perform();
        } elseif ($status === 'in-progress') {
            $service = new StatusUpdateService($this->conversation, $this->callSid, 'completed');
            $service->perform();
        }
    }

    protected function finalizeConference()
    {
        $attrs = $this->conversation->additional_attributes ?? [];
        $status = $attrs['call_status'] ?? null;
        if (!in_array($status, ['completed', 'no-answer', 'failed'])) {
            $service = new StatusUpdateService($this->conversation, $this->callSid, 'completed');
            $service->perform();
        }
    }

    protected function isAgentParticipant()
    {
        return $this->participantLabel === 'agent';
    }
}
