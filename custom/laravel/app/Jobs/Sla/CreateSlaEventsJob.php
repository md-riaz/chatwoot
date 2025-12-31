<?php

namespace App\Jobs\Sla;

use App\Models\AppliedSla;
use App\Models\SlaEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateSlaEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public int $timeout = 120;

    public string $queue = 'sla';

    public function __construct(public int $conversationId) {}

    public function handle(): void
    {
        $applied = AppliedSla::with(['conversation', 'slaPolicy'])
            ->where('conversation_id', $this->conversationId)
            ->get();

        foreach ($applied as $record) {
            $conversation = $record->conversation;
            if (! $conversation) {
                continue;
            }

            $inboxId = $conversation->inbox_id;
            $accountId = $conversation->account_id;
            $policyId = $record->sla_policy_id;

            $this->recordDeadline($record, $record->sla_first_response_at, SlaEvent::TYPE_FIRST_RESPONSE_DUE, SlaEvent::TYPE_FIRST_RESPONSE_BREACHED, $accountId, $inboxId, $policyId);
            $this->recordDeadline($record, $record->sla_next_response_at, SlaEvent::TYPE_NEXT_RESPONSE_DUE, SlaEvent::TYPE_NEXT_RESPONSE_BREACHED, $accountId, $inboxId, $policyId);
            $this->recordDeadline($record, $record->sla_resolution_at, SlaEvent::TYPE_RESOLUTION_DUE, SlaEvent::TYPE_RESOLUTION_BREACHED, $accountId, $inboxId, $policyId);
        }

        Log::info('CreateSlaEventsJob processed conversation SLA checkpoints', ['conversation_id' => $this->conversationId]);
    }

    private function recordDeadline(
        AppliedSla $record,
        ?Carbon $deadline,
        int $dueType,
        int $breachType,
        int $accountId,
        int $inboxId,
        int $policyId
    ): void {
        if (! $deadline) {
            return;
        }

        SlaEvent::firstOrCreate(
            [
                'applied_sla_id' => $record->id,
                'event_type' => $dueType,
            ],
            [
                'conversation_id' => $record->conversation_id,
                'account_id' => $accountId,
                'sla_policy_id' => $policyId,
                'inbox_id' => $inboxId,
                'meta' => [
                    'deadline_at' => $deadline->toIso8601String(),
                    'status' => 'pending',
                ],
            ]
        );

        if ($deadline->isPast()) {
            SlaEvent::firstOrCreate(
                [
                    'applied_sla_id' => $record->id,
                    'event_type' => $breachType,
                ],
                [
                    'conversation_id' => $record->conversation_id,
                    'account_id' => $accountId,
                    'sla_policy_id' => $policyId,
                    'inbox_id' => $inboxId,
                    'meta' => [
                        'deadline_at' => $deadline->toIso8601String(),
                        'breached_at' => now()->toIso8601String(),
                    ],
                ]
            );
        }
    }
}
