<?php

namespace App\Actions\Notifications;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\Notification;
use App\Models\SlaPolicy;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSlaNotificationAction
{
    use AsAction;

    public function handle(
        string $notificationType,
        User $user,
        Account $account,
        Conversation $conversation,
        SlaPolicy $slaPolicy
    ): ?Notification {
        // Check if notification already exists to avoid duplicates
        $existingNotification = Notification::where([
            'notification_type' => $notificationType,
            'user_id' => $user->id,
            'account_id' => $account->id,
            'primary_actor_type' => Conversation::class,
            'primary_actor_id' => $conversation->id,
            'secondary_actor_type' => SlaPolicy::class,
            'secondary_actor_id' => $slaPolicy->id,
        ])->first();

        if ($existingNotification) {
            return $existingNotification;
        }

        return Notification::create([
            'notification_type' => $notificationType,
            'user_id' => $user->id,
            'account_id' => $account->id,
            'primary_actor_type' => Conversation::class,
            'primary_actor_id' => $conversation->id,
            'secondary_actor_type' => SlaPolicy::class,
            'secondary_actor_id' => $slaPolicy->id,
            'read_at' => null,
            'snoozed_until' => null,
            'meta' => [
                'sla_name' => $slaPolicy->name,
                'conversation_display_id' => $conversation->display_id,
                'inbox_name' => $conversation->inbox->name ?? '',
            ],
        ]);
    }
}