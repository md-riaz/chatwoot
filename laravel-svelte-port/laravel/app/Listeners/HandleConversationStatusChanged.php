<?php

namespace App\Listeners;

use App\Events\Conversation\ConversationStatusChanged;
use App\Actions\Csat\DispatchCsatSurveyAction;
use App\Actions\Conversation\ScheduleAutoResolveAction;
use App\Actions\Sla\DispatchSlaTimersAction;
use App\Jobs\Conversations\CreateActivityMessageJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Psr\Log\LoggerInterface;
use function Spatie\Activitylog\activity;

class HandleConversationStatusChanged
{
    public function __construct(private LoggerInterface $log) {}

    public function handle(ConversationStatusChanged $event): void
    {
        $conversation = $event->conversation;

        // Create an activity message describing the status change
        $content = sprintf('Conversation status changed from %s to %s', $event->previousStatus, $event->newStatus);
        CreateActivityMessageJob::dispatch($conversation, ['content' => $content]);

        // Re-evaluate SLAs on status change
        DispatchSlaTimersAction::run($conversation);

        if ($event->newStatus === \App\Models\Conversation::STATUS_RESOLVED) {
            DispatchCsatSurveyAction::run($conversation);
        } else {
            ScheduleAutoResolveAction::run($conversation);
        }

        // Emit webhooks for 'conversation_status_changed'
        SendWebhooksJob::dispatch($conversation->account_id, 'conversation_status_changed', [
            'conversation_id' => $conversation->id,
            'previous_status' => $event->previousStatus,
            'new_status' => $event->newStatus,
        ]);

        activity()
            ->performedOn($conversation)
            ->withProperties([
                'event' => 'conversation_status_changed',
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
            ])
            ->event('conversation_status_changed')
            ->log('Conversation status changed');

        $this->log->info('HandleConversationStatusChanged dispatched side-effects', ['conversation_id' => $conversation->id]);
    }
}
