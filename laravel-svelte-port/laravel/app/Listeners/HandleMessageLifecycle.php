<?php

namespace App\Listeners;

use App\Events\Message\MessageCreated;
use App\Events\Message\MessageDeleted;
use App\Events\Message\MessageUpdated;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;
use function Spatie\Activitylog\activity;

class HandleMessageLifecycle
{
    public function __construct(private LogContract $log) {}

    public function handle(MessageCreated|MessageUpdated|MessageDeleted $event): void
    {
        $message = $event->message;

        $eventName = $this->resolveEventName($event);
        $payload = [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'account_id' => $message->account_id,
        ];

        SendWebhooksJob::dispatch($message->account_id, $eventName, $payload);

        activity()
            ->performedOn($message)
            ->withProperties([
                'event' => $eventName,
                'conversation_id' => $message->conversation_id,
                'account_id' => $message->account_id,
            ])
            ->event($eventName)
            ->log('Message lifecycle change captured');

        $this->log->info('Handled message lifecycle event', ['event' => $eventName, 'message_id' => $message->id]);
    }

    private function resolveEventName(MessageCreated|MessageUpdated|MessageDeleted $event): string
    {
        return match (true) {
            $event instanceof MessageCreated => 'message_created',
            $event instanceof MessageDeleted => 'message_deleted',
            default => 'message_updated',
        };
    }
}
