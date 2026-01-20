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
    ): void {
        // Map string type to integer
        $typeInt = \App\Models\NotificationSetting::NOTIFICATION_TYPES[$notificationType] ?? 0;

        // Check subscription (Rails parity)
        $setting = \App\Models\NotificationSetting::where('user_id', $user->id)
            ->where('account_id', $account->id)
            ->first();

        if (!$setting || !$setting->isSubscribed($notificationType)) {
            return;
        }

        // Check if notification already exists (Rails parity: direct columns)
        $exists = $user->notifications()
            ->where('notification_type', $typeInt)
            ->where('account_id', $account->id)
            ->where('primary_actor_type', Conversation::class)
            ->where('primary_actor_id', $conversation->id)
            ->where('secondary_actor_type', SlaPolicy::class)
            ->where('secondary_actor_id', $slaPolicy->id)
            ->exists();

        if ($exists) {
            return;
        }

        Notification::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'notification_type' => $typeInt,
            'primary_actor_type' => Conversation::class,
            'primary_actor_id' => $conversation->id,
            'secondary_actor_type' => SlaPolicy::class,
            'secondary_actor_id' => $slaPolicy->id,
            'meta' => [
                'sla_name' => $slaPolicy->name,
                'conversation_display_id' => $conversation->display_id,
                'inbox_name' => $conversation->inbox->name ?? '',
            ]
        ]);
    }
}