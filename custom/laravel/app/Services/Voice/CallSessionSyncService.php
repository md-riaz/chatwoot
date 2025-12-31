<?php

namespace App\Services\Voice;

use App\Models\Conversation;

class CallSessionSyncService
{
    protected $conversation;
    protected $callSid;
    protected $fromNumber;
    protected $toNumber;
    protected $direction;

    public function __construct(Conversation $conversation, $callSid, $fromNumber, $toNumber, $direction)
    {
        $this->conversation = $conversation;
        $this->callSid = $callSid;
        $this->fromNumber = $fromNumber;
        $this->toNumber = $toNumber;
        $this->direction = $direction;
    }

    public function perform()
    {
        $attrs = $this->conversation->additional_attributes ?? [];
        $attrs['call_direction'] = $this->direction;
        $attrs['call_status'] = $attrs['call_status'] ?? 'ringing';
        $attrs['conference_sid'] = $attrs['conference_sid'] ?? 'conf_' . $this->conversation->id;
        $attrs['meta'] = $attrs['meta'] ?? [];
        $attrs['meta']['initiated_at'] = $attrs['meta']['initiated_at'] ?? now()->toIso8601String();
        $this->conversation->additional_attributes = $attrs;
        $this->conversation->last_activity_at = now();
        $this->conversation->save();
        // Create/sync the voice call message
        $agent = null;
        if (!empty($attrs['agent_id'])) {
            $agent = $this->conversation->account->users()->find($attrs['agent_id']) ?? null;
            if ($this->direction === 'outbound' && $agent === null) {
                throw new \InvalidArgumentException('Agent sender required for outbound call sync');
            }
        }

        // Use action-based flow to create call activity message
        try {
            \App\Actions\Voice\CreateCallMessageAction::run(
                $this->conversation,
                $this->direction,
                [
                    'call_sid' => $this->callSid,
                    'status' => $attrs['call_status'],
                    'conference_sid' => $attrs['conference_sid'],
                    'from_number' => $this->fromNumber,
                    'to_number' => $this->toNumber,
                ],
                $agent,
                [
                    'created_at' => $attrs['meta']['initiated_at'] ?? now(),
                ]
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create call message via action', ['error' => $e->getMessage()]);
        }

        return $this->conversation;
    }
}
