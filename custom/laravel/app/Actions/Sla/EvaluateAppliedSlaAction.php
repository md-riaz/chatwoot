<?php

namespace App\Actions\Sla;

use App\Models\AppliedSla;
use App\Models\SlaEvent;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class EvaluateAppliedSlaAction
{
    use AsAction;

    private AppliedSla $appliedSla;

    public function handle(AppliedSla $appliedSla): void
    {
        $this->appliedSla = $appliedSla;

        $this->checkSlaThresholds();

        // We will calculate again in the next iteration
        if (!$this->appliedSla->conversation->isResolved()) {
            return;
        }

        // After conversation is resolved, check if the SLA was hit or missed
        $this->handleResolvedSla();
    }

    private function checkSlaThresholds(): void
    {
        $thresholds = [
            'first_response_time_threshold',
            'next_response_time_threshold',
            'resolution_time_threshold'
        ];

        foreach ($thresholds as $threshold) {
            if (empty($this->appliedSla->slaPolicy->{$threshold})) {
                continue;
            }

            $methodName = 'check' . str_replace('_', '', ucwords($threshold, '_'));
            if (method_exists($this, $methodName)) {
                $this->{$methodName}();
            }
        }
    }

    private function checkFirstResponseTimeThreshold(): void
    {
        $conversation = $this->appliedSla->conversation;
        $slaPolicy = $this->appliedSla->slaPolicy;
        
        $threshold = $conversation->created_at->timestamp + $slaPolicy->first_response_time_threshold;
        
        if ($this->firstReplyWasWithinThreshold($conversation, $threshold)) {
            return;
        }
        
        if ($this->stillWithinThreshold($threshold)) {
            return;
        }

        $this->handleMissedSla('frt');
    }

    private function checkNextResponseTimeThreshold(): void
    {
        $conversation = $this->appliedSla->conversation;
        $slaPolicy = $this->appliedSla->slaPolicy;

        // Still waiting for first reply, covered under first response time threshold
        if (empty($conversation->first_reply_created_at)) {
            return;
        }

        // Waiting on customer response, no need to check next response time threshold
        if (empty($conversation->waiting_since)) {
            return;
        }

        $threshold = $conversation->waiting_since->timestamp + $slaPolicy->next_response_time_threshold;
        
        if ($this->stillWithinThreshold($threshold)) {
            return;
        }

        $meta = ['message_id' => $this->getLastIncomingMessageId($conversation)];
        $this->handleMissedSla('nrt', $meta);
    }

    private function checkResolutionTimeThreshold(): void
    {
        $conversation = $this->appliedSla->conversation;
        $slaPolicy = $this->appliedSla->slaPolicy;

        if ($conversation->isResolved()) {
            return;
        }

        $threshold = $conversation->created_at->timestamp + $slaPolicy->resolution_time_threshold;
        
        if ($this->stillWithinThreshold($threshold)) {
            return;
        }

        $this->handleMissedSla('rt');
    }

    private function stillWithinThreshold(int $threshold): bool
    {
        return now()->timestamp < $threshold;
    }

    private function firstReplyWasWithinThreshold($conversation, int $threshold): bool
    {
        return !empty($conversation->first_reply_created_at) && 
               $conversation->first_reply_created_at->timestamp <= $threshold;
    }

    private function getLastIncomingMessageId($conversation): ?int
    {
        // Get the last incoming message ID
        return $conversation->messages()
            ->where('message_type', 'incoming')
            ->latest()
            ->value('id');
    }

    private function alreadyMissed(string $type, array $meta = []): bool
    {
        $query = SlaEvent::where('applied_sla_id', $this->appliedSla->id)
            ->where('event_type', $this->getEventTypeValue($type));

        if (!empty($meta)) {
            $query->where('meta', $meta);
        }

        return $query->exists();
    }

    private function getEventTypeValue(string $type): int
    {
        return match ($type) {
            'frt' => SlaEvent::TYPE_FRT,
            'nrt' => SlaEvent::TYPE_NRT,
            'rt' => SlaEvent::TYPE_RT,
            default => -1,
        };
    }

    private function handleMissedSla(string $type, array $meta = []): void
    {
        if ($type === 'nrt' && empty($meta)) {
            $meta = ['message_id' => $this->getLastIncomingMessageId($this->appliedSla->conversation)];
        }

        if ($this->alreadyMissed($type, $meta)) {
            return;
        }

        $this->createSlaEvent($type, $meta);

        Log::warning("SLA {$type} missed for conversation {$this->appliedSla->conversation->id} " .
                    "in account {$this->appliedSla->account_id} " .
                    "for sla_policy {$this->appliedSla->sla_policy_id}");

        if ($this->appliedSla->sla_status !== AppliedSla::STATUS_ACTIVE_WITH_MISSES) {
            $this->appliedSla->update(['sla_status' => AppliedSla::STATUS_ACTIVE_WITH_MISSES]);
        }
    }

    private function handleResolvedSla(): void
    {
        if ($this->appliedSla->isActive()) {
            $this->appliedSla->update(['sla_status' => AppliedSla::STATUS_HIT]);
            Log::info("SLA hit for conversation {$this->appliedSla->conversation->id} " .
                     "in account {$this->appliedSla->account_id} " .
                     "for sla_policy {$this->appliedSla->sla_policy_id}");
        } else {
            $this->appliedSla->update(['sla_status' => AppliedSla::STATUS_MISSED]);
            Log::info("SLA missed for conversation {$this->appliedSla->conversation->id} " .
                     "in account {$this->appliedSla->account_id} " .
                     "for sla_policy {$this->appliedSla->sla_policy_id}");
        }
    }

    private function createSlaEvent(string $eventType, array $meta = []): void
    {
        SlaEvent::create([
            'applied_sla_id' => $this->appliedSla->id,
            'conversation_id' => $this->appliedSla->conversation_id,
            'event_type' => $this->getEventTypeValue($eventType),
            'meta' => $meta,
            'account_id' => $this->appliedSla->account_id,
            'inbox_id' => $this->appliedSla->conversation->inbox_id,
            'sla_policy_id' => $this->appliedSla->sla_policy_id,
        ]);
    }
}