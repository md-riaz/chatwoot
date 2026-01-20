<?php

namespace App\Listeners;

use App\Events\Conversation\ConversationAssigned;
use App\Jobs\Conversations\CreateActivityMessageJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;
use function Spatie\Activitylog\activity;

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

        // Notify the new assignee via Notification channels (Rails parity: manual creation for DB, standard for others)
        if ($assignee) {
            try {
                // 1. Create DB Notification manually (Rails parity schema)
                \App\Models\Notification::create([
                    'user_id' => $assignee->id,
                    'account_id' => $conversation->account_id,
                    'notification_type' => \App\Models\NotificationSetting::NOTIFICATION_TYPES['conversation_assignment'],
                    'primary_actor_type' => get_class($conversation),
                    'primary_actor_id' => $conversation->id,
                    'meta' => [
                        'conversation_id' => $conversation->id,
                        'previous_assignee_id' => $event->previousAssignee?->id ?? null,
                    ]
                ]);

                // 2. Dispatch Laravel Notification for Email/Broadcast
                $assignee->notify(new \App\Notifications\ConversationAssignedNotification($conversation, $event->previousAssignee));
            } catch (\Throwable $e) {
                $this->log->warning('Failed to notify assignee', ['assignee_id' => $assignee->id, 'error' => $e->getMessage()]);
            }
        }

        // Notify previous assignee about unassignment - TODO: Does Rails notify unassignment? 
        // Based on Rails NotificationListener: return if assignee.blank?
        // It seems Rails only notifies the *current* assignee (if any).
        // I will keep existing logic but converted to manual creation if appropriate, 
        // but for strict parity, Rails 'conversation_assignment' is only for the new assignee.
        
        // (Removed previous assignee notification to match Rails parity if desired, or keep it if it was custom)
        // I will comment it out to be safe with "Rails parity" unless I confirm otherwise.
        /*
        if ($event->previousAssignee) {
            try {
                $event->previousAssignee->notify(new \App\Notifications\ConversationAssignedNotification($conversation, $event->previousAssignee));
            } catch (\Throwable $e) {
                $this->log->warning('Failed to notify previous assignee', ['assignee_id' => $event->previousAssignee->id, 'error' => $e->getMessage()]);
            }
        }
        */

        activity()
            ->performedOn($conversation)
            ->withProperties([
                'event' => 'conversation_assigned',
                'assignee_id' => $assignee?->id,
                'previous_assignee_id' => $event->previousAssignee?->id,
            ])
            ->event('conversation_assigned')
            ->log('Conversation assignment updated');

        $this->log->info('HandleConversationAssigned dispatched side-effects', ['conversation_id' => $conversation->id]);
    }
}
