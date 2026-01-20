<?php

namespace App\Listeners;

use App\Events\Notification\NotificationCreated;
use App\Http\Resources\NotificationResource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleNotificationCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NotificationCreated $event): void
    {
        $notification = $event->notification;
        $user = $notification->user;
        $account = $notification->account;

        if (!$user || !$account) {
            return;
        }

        try {
            // Calculate counts
            $unreadCount = $user->notifications()
                ->where('account_id', $account->id)
                ->whereNull('read_at')
                ->count();
            
            $count = $user->notifications()
                ->where('account_id', $account->id)
                ->count();

            // Prepare payload
            $payload = [
                'notification' => (new NotificationResource($notification))->resolve(),
                'unread_count' => $unreadCount,
                'count' => $count,
            ];

            // Broadcast to user's private channel
            // Channel pattern: "user.{id}" or similar. Rails uses pubsub_token.
            // In Laravel Reverb/Echo, we typically use PrivateChannel('App.Models.User.{id}')
            // or we can follow Rails pattern if we are keeping the frontend as is.
            // Assuming standard Laravel broadcasting for now, but checking User model for pubsub_token usage might be good.
            // The frontend likely expects 'notification.created' event.
            
            // Using Laravel's broadcast helper to a specific channel might be needed if not using standard event broadcasting.
            // But usually we dispatch an event that implements ShouldBroadcast.
            // However, this listener handles the "side effect" of broadcasting.
            // If we want to use Laravel's broadcasting system, we should probably fire a SEPARATE event that implements ShouldBroadcast
            // OR use a specific broadcasting mechanism.
            
            // In Chatwoot Rails: ActionCable broadcasts to specific tokens.
            // In Laravel Port: We likely want to broadcast to the user's channel.
            
            // Let's assume we use a broadcast event.
            broadcast(new \App\Events\Broadcasting\NotificationCreatedBroadcast(
                $user, 
                $payload
            ));

        } catch (\Throwable $e) {
            Log::error('Failed to broadcast notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
