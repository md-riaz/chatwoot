<?php

namespace App\Listeners;

use App\Events\Notification\NotificationCreated;
use App\Jobs\Notification\SendPushNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class HandlePushNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NotificationCreated $event): void
    {
        $notification = $event->notification;
        $user = $notification->user;
        
        if (!$user) {
            return;
        }

        // Generate title and body
        // Ideally use translation keys, but for now hardcoding English with placeholders to match Rails parity
        $title = $this->getPushMessageTitle($notification);
        $body = $this->getPushMessageBody($notification);
        
        if (!$title) {
            return;
        }

        // Dispatch job
        SendPushNotificationJob::dispatch(
            $user->id,
            $title,
            $body,
            $notification->push_event_data ?? [], // Assuming we add push_event_data or similar
            $notification->account_id,
            $this->getNotificationTypeString($notification->notification_type)
        );
    }

    private function getNotificationTypeString(int $type): ?string
    {
        $types = array_flip(\App\Models\NotificationSetting::NOTIFICATION_TYPES);
        return $types[$type] ?? null;
    }

    private function getPushMessageTitle($notification): string
    {
        $type = $this->getNotificationTypeString($notification->notification_type);
        $primaryActor = $notification->primaryActor;
        $conversation = $notification->primaryActor instanceof \App\Models\Conversation ? $notification->primaryActor : null;
        
        // If primary actor is not conversation, try to get conversation from it if possible
        // But usually primary actor IS conversation for these types.
        
        $displayId = $conversation ? $conversation->display_id : ($primaryActor->id ?? '');
        
        // Basic mapping based on Rails
        return match ($type) {
            'conversation_creation' => "A new conversation [ID - {$displayId}] has been created in " . ($conversation->inbox->name ?? 'Inbox'),
            'conversation_assignment' => "A new conversation [ID - {$displayId}] has been assigned to you.",
            'conversation_mention' => "You have been mentioned in conversation [ID - {$displayId}]",
            'assigned_conversation_new_message' => "New message in your assigned conversation [ID - {$displayId}].",
            'participating_conversation_new_message' => "New message in your participating conversation [ID - {$displayId}].",
            'sla_missed_first_response' => "SLA missed for first response in conversation [ID - {$displayId}]",
            'sla_missed_next_response' => "SLA missed for next response in conversation [ID - {$displayId}]",
            'sla_missed_resolution' => "SLA missed for resolution in conversation [ID - {$displayId}]",
            default => "Notification for conversation [ID - {$displayId}]",
        };
    }

    private function getPushMessageBody($notification): string
    {
        $primaryActor = $notification->primaryActor;
        $secondaryActor = $notification->secondaryActor;
        
        // If secondary actor is a message, use its content (truncated)
        if ($secondaryActor instanceof \App\Models\Message) {
            return Str::limit($secondaryActor->content ?? 'New attachment', 100);
        }

        // Default body
        return "Click to view details";
    }
}
