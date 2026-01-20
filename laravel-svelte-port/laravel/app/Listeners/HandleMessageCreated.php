<?php

namespace App\Listeners;

use App\Events\Message\MessageCreated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HandleMessageCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(MessageCreated $event): void
    {
        $message = $event->message;
        
        // Only notify for incoming or private messages, or if it's an outgoing message sent by another user (e.g. agent to agent)
        // Rails logic: return unless message.notifiable? (which checks private? || incoming? || outgoing?)
        // Actually, we usually want to notify for:
        // 1. Mentions (any message type)
        // 2. New messages (incoming/private)
        // Outgoing messages (from agent) usually don't notify other agents unless mentioned or participating?
        // Rails NewMessageNotificationService checks `message.notifiable?`.
        // Let's assume all messages except maybe system messages are notifiable for now, 
        // or strictly follow Rails: Private=true OR Incoming=true OR (Outgoing=true AND not from current user?)
        
        // For now, let's process mentions first as they are most critical.
        
        $conversation = $message->conversation;
        $sender = $message->sender;
        $account = $message->account;
        
        $notifiedUserIds = [];

        // 1. Handle Mentions
        $mentionedUserIds = $this->extractMentionedUserIds($message->content);
        if (!empty($mentionedUserIds)) {
            foreach ($mentionedUserIds as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                // Add as participant
                $conversation->conversationParticipants()->firstOrCreate(['user_id' => $userId]);
                
                if ($this->shouldNotify($user, 'conversation_mention', $account->id)) {
                    Notification::create([
                        'user_id' => $user->id,
                        'account_id' => $account->id,
                        'notification_type' => NotificationSetting::NOTIFICATION_TYPES['conversation_mention'],
                        'primary_actor_type' => Conversation::class,
                        'primary_actor_id' => $conversation->id,
                        'secondary_actor_type' => Message::class,
                        'secondary_actor_id' => $message->id,
                    ]);
                    $notifiedUserIds[] = $user->id;
                }
            }
        }

        // 2. Notify Assignee
        // Only if message is incoming or private? Rails `notifiable?` logic:
        // def notifiable?
        //   return false if content.blank? && attachments.blank?
        //   return true if private? || incoming? || activity?
        //   return true if message_type == 'outgoing' && conversation.inbox.is_a?(Channel::Api) # etc
        //   false
        // end
        
        // Simplified check:
        if ($message->private || $message->message_type === Message::TYPE_INCOMING) {
             $this->notifyAssignee($message, $conversation, $sender, $notifiedUserIds);
             $this->notifyParticipatingUsers($message, $conversation, $sender, $notifiedUserIds);
        }
    }

    private function notifyAssignee(Message $message, Conversation $conversation, $sender, array &$notifiedUserIds): void
    {
        $assignee = $conversation->assignee;
        
        if (!$assignee) return;
        if ($sender && $assignee->id === $sender->id) return; // Don't notify self
        if (in_array($assignee->id, $notifiedUserIds)) return; // Already notified via mention

        if ($this->shouldNotify($assignee, 'assigned_conversation_new_message', $conversation->account_id)) {
             Notification::create([
                'user_id' => $assignee->id,
                'account_id' => $conversation->account_id,
                'notification_type' => NotificationSetting::NOTIFICATION_TYPES['assigned_conversation_new_message'],
                'primary_actor_type' => Conversation::class,
                'primary_actor_id' => $conversation->id,
                'secondary_actor_type' => Message::class,
                'secondary_actor_id' => $message->id,
            ]);
            $notifiedUserIds[] = $assignee->id;
        }
    }

    private function notifyParticipatingUsers(Message $message, Conversation $conversation, $sender, array &$notifiedUserIds): void
    {
        $participants = $conversation->conversationParticipants()->with('user')->get()->pluck('user');
        
        foreach ($participants as $participant) {
            if (!$participant) continue;
            if ($sender && $participant->id === $sender->id) continue;
            if (in_array($participant->id, $notifiedUserIds)) continue;

            if ($this->shouldNotify($participant, 'participating_conversation_new_message', $conversation->account_id)) {
                 Notification::create([
                    'user_id' => $participant->id,
                    'account_id' => $conversation->account_id,
                    'notification_type' => NotificationSetting::NOTIFICATION_TYPES['participating_conversation_new_message'],
                    'primary_actor_type' => Conversation::class,
                    'primary_actor_id' => $conversation->id,
                    'secondary_actor_type' => Message::class,
                    'secondary_actor_id' => $message->id,
                ]);
                $notifiedUserIds[] = $participant->id;
            }
        }
    }

    private function shouldNotify(User $user, string $type, int $accountId): bool
    {
        $setting = NotificationSetting::where('user_id', $user->id)
            ->where('account_id', $accountId)
            ->first();
            
        if (!$setting) return false;
        
        return $setting->isSubscribed($type);
    }

    private function extractMentionedUserIds(?string $content): array
    {
        if (!$content) return [];
        
        // Format: [name](mention://user/{id}/{name})
        // Regex to capture ID
        preg_match_all('/\[.*?\]\(mention:\/\/user\/(\d+)\/.*?\)/', $content, $matches);
        
        return array_unique($matches[1] ?? []);
    }
}
