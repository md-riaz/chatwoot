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

        // Generate title and body from model accessors
        $title = $notification->push_message_title;
        $body = $notification->push_message_body;
        
        if (!$title) {
            return;
        }

        // Dispatch job
        SendPushNotificationJob::dispatch(
            $user->id,
            $title,
            $body,
            $notification->push_event_data ?? [],
            $notification->account_id,
            $notification->notification_type_string
        );
    }
}
