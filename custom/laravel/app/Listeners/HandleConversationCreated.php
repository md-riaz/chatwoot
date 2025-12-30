<?php

namespace App\Listeners;

use App\Events\Conversation\ConversationCreated;
use App\Jobs\Conversations\RunAutoAssignConversationJob;
use App\Jobs\Conversations\CreateActivityMessageJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use App\Jobs\Sla\CheckSlaJob;
use Illuminate\Contracts\Logging\Log as LogContract;

class HandleConversationCreated
{
    public function __construct(private LogContract $log) {}

    public function handle(ConversationCreated $event): void
    {
        $conversation = $event->conversation;

        // 1) Create an activity message announcing conversation creation
        CreateActivityMessageJob::dispatch($conversation, ['content' => 'Conversation created']);

        // 2) Run auto-assign asynchronously
        RunAutoAssignConversationJob::dispatch($conversation->id);

        // 3) Trigger SLA evaluation
        CheckSlaJob::dispatch($conversation->id);

        // 4) Emit webhooks for 'conversation_created'
        SendWebhooksJob::dispatch($conversation->account_id, 'conversation_created', ['conversation_id' => $conversation->id]);

        $this->log->info('HandleConversationCreated dispatched side-effects', ['conversation_id' => $conversation->id]);
    }
}
