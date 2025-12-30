<?php

namespace App\Listeners;

use App\Events\Conversation\ConversationAssigned;
use App\Jobs\Conversations\CreateActivityMessageJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;

class HandleConversationAssigned
{
    public function __construct(private LogContract $log) {}

    public function handle(ConversationAssigned $event): void
    {
        $conversation = $event->conversation;
        $assignee = $event->assignee;

        // Create activity message about assignment
        $content = $assignee ? sprintf('Assigned to %s', $assignee->name) : 'Assignee removed';
        CreateActivityMessageJob::dispatch($conversation, ['content' => $content]);

        // Emit webhooks for 'conversation_assigned'
        SendWebhooksJob::dispatch($conversation->account_id, 'conversation_assigned', [
            'conversation_id' => $conversation->id,
            'assignee_id' => $assignee?->id,
        ]);

        // Notify the new assignee via Notification channels
        if ($assignee) {
            try {
                $assignee->notify(new \App\Notifications\ConversationAssignedNotification($conversation, $event->previousAssignee ?? null));
            } catch (\Throwable $e) {
                $this->log->warning('Failed to notify assignee', ['assignee_id' => $assignee->id, 'error' => $e->getMessage()]);
            }
        }

        // Notify previous assignee about unassignment
        if ($event->previousAssignee) {
            try {
                $event->previousAssignee->notify(new \App\Notifications\ConversationAssignedNotification($conversation, $event->previousAssignee));
            } catch (\Throwable $e) {
                $this->log->warning('Failed to notify previous assignee', ['assignee_id' => $event->previousAssignee->id, 'error' => $e->getMessage()]);
            }
        }

        $this->log->info('HandleConversationAssigned dispatched side-effects', ['conversation_id' => $conversation->id]);
    }
}
