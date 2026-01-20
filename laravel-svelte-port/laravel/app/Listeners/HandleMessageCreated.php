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
                $conversation->participants()->syncWithoutDetaching([$userId]);
                
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

        // 2. Notify Assignee and Participants
        // Notify if message is private OR incoming
        // Also verify if the message is meaningful (not blank/empty unless has attachments) - skipping for now as strict parity check
        if ($message->private || $message->message_type === Message::TYPE_INCOMING) {
             $this->notifyAssignee($message, $conversation, $sender, $notifiedUserIds);
             $this->notifyParticipatingUsers($message, $conversation, $sender, $notifiedUserIds);
        }
    }

    private function notifyAssignee(Message $message, Conversation $conversation, $sender, array &$notifiedUserIds): void
    {
        $assignee = $conversation->assignee;
        
        if (!$assignee) return;
        
        // Don't notify self (if sender is the assignee)
        if ($sender && $sender->is($assignee)) return;
        
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
        // Use the BelongsToMany relationship to get participants (Users)
        $participants = $conversation->participants;
        
        if ($participants->isEmpty()) {
            return;
        }

        // Pre-fetch settings
        $participantIds = $participants->pluck('id');
        $settings = NotificationSetting::whereIn('user_id', $participantIds)
            ->where('account_id', $conversation->account_id)
            ->get()
            ->keyBy('user_id');

        foreach ($participants as $participant) {
            if (!$participant) continue;
            
            // Don't notify self
            if ($sender && $sender->is($participant)) continue;
            
            if (in_array($participant->id, $notifiedUserIds)) continue;

            $setting = $settings->get($participant->id);
            if ($setting && $setting->isSubscribed('participating_conversation_new_message')) {
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
