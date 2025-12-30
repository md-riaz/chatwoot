<?php

namespace App\Listeners;

use App\Events\Conversation\ConversationStatusChanged;
use App\Jobs\Conversations\CreateActivityMessageJob;
use App\Jobs\Sla\CheckSlaJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;

class HandleConversationStatusChanged
{
    public function __construct(private LogContract $log) {}

    public function handle(ConversationStatusChanged $event): void
    {
        $conversation = $event->conversation;

        // Create an activity message describing the status change
        $content = sprintf('Conversation status changed from %s to %s', $event->previousStatus, $event->newStatus);
        CreateActivityMessageJob::dispatch($conversation, ['content' => $content]);

        // Re-evaluate SLAs on status change
        CheckSlaJob::dispatch($conversation->id);

        // Emit webhooks for 'conversation_status_changed'
        SendWebhooksJob::dispatch($conversation->account_id, 'conversation_status_changed', [
            'conversation_id' => $conversation->id,
            'previous_status' => $event->previousStatus,
            'new_status' => $event->newStatus,
        ]);

        $this->log->info('HandleConversationStatusChanged dispatched side-effects', ['conversation_id' => $conversation->id]);
    }
}
