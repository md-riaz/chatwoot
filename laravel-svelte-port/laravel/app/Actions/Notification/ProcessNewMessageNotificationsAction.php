<?php

namespace App\Actions\Notification;

use App\Models\Message;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\Mention;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProcessNewMessageNotificationsAction
{
    public function handle(Message $message): void
    {
        if ($message->message_type !== Message::TYPE_INCOMING && $message->message_type !== Message::TYPE_OUTGOING) {
            return;
        }

        $this->processMentions($message);
        $this->processAssigneeNotification($message);
        $this->processParticipatingNotification($message);
    }

    private function processMentions(Message $message): void
    {
        if (empty($message->content)) {
            return;
        }

        // Regex to find mentions: mention://(user|team)/(\d+)/(.+)
        // Matches: mention://user/1/John Doe
        preg_match_all('/mention:\/\/(user|team)\/(\d+)\/(.+)/', $message->content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $type = $match[1];
            $id = $match[2];
            
            if ($type === 'user') {
                $user = User::find($id);
                if ($user) {
                    $this->createMentionNotification($message, $user);
                }
            }
            // Team mentions not supported yet in this port or similar logic
        }
    }

    private function createMentionNotification(Message $message, User $user): void
    {
        // Avoid self-mention notification if that's possible (unlikely but good to check)
        if ($message->sender_type === User::class && $message->sender_id === $user->id) {
            return;
        }

        // Create Mention record
        $mention = Mention::firstOrCreate([
            'user_id' => $user->id,
            'conversation_id' => $message->conversation_id,
            'account_id' => $message->account_id,
        ], [
            'mentioned_at' => now(),
        ]);

        if (!$mention->wasRecentlyCreated) {
            $mention->update(['mentioned_at' => now()]);
        }

        // Create Notification
        // Mentions are usually always notified (see Rails spec)
        Notification::create([
            'user_id' => $user->id,
            'account_id' => $message->account_id,
            'notification_type' => NotificationSetting::NOTIFICATION_TYPES['conversation_mention'],
            'primary_actor_type' => get_class($message->conversation),
            'primary_actor_id' => $message->conversation_id,
            'secondary_actor_type' => get_class($message),
            'secondary_actor_id' => $message->id,
        ]);
        
        // TODO: Dispatch broadcast/email if needed (Laravel notify())
    }

    private function processAssigneeNotification(Message $message): void
    {
        $conversation = $message->conversation;
        $assignee = $conversation->assignee;

        if (!$assignee) {
            return;
        }

        // Don't notify if assignee is the sender
        if ($this->isSender($message, $assignee)) {
            return;
        }

        // Check subscription
        if (!$this->isSubscribed($assignee, $message->account_id, 'assigned_conversation_new_message')) {
            return;
        }

        Notification::create([
            'user_id' => $assignee->id,
            'account_id' => $message->account_id,
            'notification_type' => NotificationSetting::NOTIFICATION_TYPES['assigned_conversation_new_message'],
            'primary_actor_type' => get_class($conversation),
            'primary_actor_id' => $conversation->id,
            'secondary_actor_type' => get_class($message),
            'secondary_actor_id' => $message->id,
        ]);
    }

    private function processParticipatingNotification(Message $message): void
    {
        $conversation = $message->conversation;
        $participants = $conversation->participants;

        if (!$participants) {
            return;
        }

        foreach ($participants as $user) {
            // Don't notify if user is assignee (already handled)
            if ($conversation->assignee_id === $user->id) {
                continue;
            }

            // Don't notify if user is sender
            if ($this->isSender($message, $user)) {
                continue;
            }

            // Check subscription
            if (!$this->isSubscribed($user, $message->account_id, 'participating_conversation_new_message')) {
                continue;
            }

            Notification::create([
                'user_id' => $user->id,
                'account_id' => $message->account_id,
                'notification_type' => NotificationSetting::NOTIFICATION_TYPES['participating_conversation_new_message'],
                'primary_actor_type' => get_class($conversation),
                'primary_actor_id' => $conversation->id,
                'secondary_actor_type' => get_class($message),
                'secondary_actor_id' => $message->id,
            ]);
        }
    }

    private function isSender(Message $message, User $user): bool
    {
        return $message->sender_type === User::class && $message->sender_id === $user->id;
    }

    private function isSubscribed(User $user, int $accountId, string $type): bool
    {
        $setting = NotificationSetting::where('user_id', $user->id)
            ->where('account_id', $accountId)
            ->first();

        if (!$setting) {
            return false;
        }

        return $setting->isSubscribed($type);
    }
}
