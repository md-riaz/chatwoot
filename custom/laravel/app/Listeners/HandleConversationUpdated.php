<?php

namespace App\Listeners;

use App\Events\Conversation\ConversationUpdated;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;
use function Spatie\Activitylog\activity;

class HandleConversationUpdated
{
    public function __construct(private LogContract $log) {}

    public function handle(ConversationUpdated $event): void
    {
        $conversation = $event->conversation;

        SendWebhooksJob::dispatch($conversation->account_id, 'conversation_updated', [
            'conversation_id' => $conversation->id,
            'changes' => $event->changes,
        ]);

        activity()
            ->performedOn($conversation)
            ->withProperties([
                'event' => 'conversation_updated',
                'changes' => $event->changes,
            ])
            ->event('conversation_updated')
            ->log('Conversation updated');

        $this->log->info('HandleConversationUpdated dispatched side-effects', [
            'conversation_id' => $conversation->id,
            'changes' => $event->changes,
        ]);
    }
}
