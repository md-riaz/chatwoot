<?php

namespace App\Listeners;

use App\Events\Account\AccountCacheInvalidated;
use App\Events\Contact\ContactDeleted;
use App\Events\Contact\ContactMerged;
use App\Events\Conversation\AssigneeChanged;
use App\Events\Conversation\ConversationContactChanged;
use App\Events\Conversation\ConversationMentioned;
use App\Events\Conversation\ConversationRead;
use App\Events\Conversation\ConversationTyping;
use App\Events\Conversation\FirstReplyCreated;
use App\Events\Conversation\TeamChanged;
use App\Events\Message\MessageCreated;
use App\Events\Notification\NotificationDeleted;
use App\Events\Notification\NotificationUpdated;
use App\Events\Presence\PresenceUpdate;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WebSocketEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle notification created events.
     */
    public function handleNotificationCreated($event): void
    {
        // The existing NotificationCreatedBroadcast should handle this
        // This is just a placeholder for consistency
    }

    /**
     * Handle notification updated events.
     */
    public function handleNotificationUpdated($event): void
    {
        broadcast(new NotificationUpdated($event->notification, $event->performer ?? null));
    }

    /**
     * Handle notification deleted events.
     */
    public function handleNotificationDeleted($event): void
    {
        broadcast(new NotificationDeleted(
            $event->notificationId,
            $event->userId,
            $event->notificationData ?? [],
            $event->performer ?? null
        ));
    }

    /**
     * Handle message created events.
     */
    public function handleMessageCreated($event): void
    {
        // Check if this is the first reply from an agent
        if ($this->isFirstReply($event->message)) {
            broadcast(new FirstReplyCreated(
                $event->message->conversation,
                $event->message,
                $event->message->user
            ));
        }
    }

    /**
     * Handle conversation read events.
     */
    public function handleConversationRead($event): void
    {
        broadcast(new ConversationRead($event->conversation, $event->reader));
    }

    /**
     * Handle typing started events.
     */
    public function handleTypingStarted($event): void
    {
        broadcast(new ConversationTyping(
            $event->conversation,
            $event->typer,
            true
        ));
    }

    /**
     * Handle typing stopped events.
     */
    public function handleTypingStopped($event): void
    {
        broadcast(new ConversationTyping(
            $event->conversation,
            $event->typer,
            false
        ));
    }

    /**
     * Handle assignee changed events.
     */
    public function handleAssigneeChanged($event): void
    {
        broadcast(new AssigneeChanged(
            $event->conversation,
            $event->previousAssignee ?? null,
            $event->newAssignee ?? null,
            $event->performer ?? null
        ));
    }

    /**
     * Handle team changed events.
     */
    public function handleTeamChanged($event): void
    {
        broadcast(new TeamChanged(
            $event->conversation,
            $event->previousTeam ?? null,
            $event->newTeam ?? null,
            $event->performer ?? null
        ));
    }

    /**
     * Handle conversation contact changed events.
     */
    public function handleConversationContactChanged($event): void
    {
        broadcast(new ConversationContactChanged(
            $event->conversation,
            $event->previousContact,
            $event->newContact,
            $event->performer ?? null
        ));
    }

    /**
     * Handle conversation mentioned events.
     */
    public function handleConversationMentioned($event): void
    {
        broadcast(new ConversationMentioned(
            $event->conversation,
            $event->mentionedUser,
            $event->message,
            $event->mentioner
        ));
    }

    /**
     * Handle contact merged events.
     */
    public function handleContactMerged($event): void
    {
        broadcast(new ContactMerged(
            $event->primaryContact,
            $event->mergedContact,
            $event->performer ?? null
        ));
    }

    /**
     * Handle contact deleted events.
     */
    public function handleContactDeleted($event): void
    {
        broadcast(new ContactDeleted(
            $event->contactId,
            $event->accountId,
            $event->contactData ?? [],
            $event->performer ?? null
        ));
    }

    /**
     * Handle user presence changed events.
     */
    public function handleUserPresenceChanged($event): void
    {
        // Broadcast to all accounts the user belongs to
        foreach ($event->user->accounts as $account) {
            broadcast(new PresenceUpdate(
                $event->user,
                $account->id,
                $event->status,
                $event->metadata ?? null
            ));
        }
    }

    /**
     * Handle contact presence changed events.
     */
    public function handleContactPresenceChanged($event): void
    {
        broadcast(new PresenceUpdate(
            $event->contact,
            $event->contact->account_id,
            $event->status,
            $event->metadata ?? null
        ));
    }

    /**
     * Handle account cache invalidated events.
     */
    public function handleAccountCacheInvalidated($event): void
    {
        broadcast(new AccountCacheInvalidated(
            $event->account,
            $event->invalidatedKeys ?? []
        ));
    }

    /**
     * Check if a message is the first reply from an agent.
     */
    private function isFirstReply(Message $message): bool
    {
        // Only consider outgoing messages (from agents)
        if ($message->message_type !== 'outgoing') {
            return false;
        }

        // Check if this is the first outgoing message in the conversation
        return $message->conversation->messages()
            ->where('message_type', 'outgoing')
            ->where('id', '<=', $message->id)
            ->count() === 1;
    }
}