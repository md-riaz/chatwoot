<?php

namespace App\Services\WebSocket;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;

class BroadcastTargetService
{
    /**
     * Get account-level broadcast channels
     */
    public function getAccountChannels(int $accountId): array
    {
        return [
            new PrivateChannel("account.{$accountId}"),
            new PresenceChannel("account.{$accountId}.presence")
        ];
    }

    /**
     * Get user-specific broadcast channels
     */
    public function getUserChannels(int $userId): array
    {
        return [
            new PrivateChannel("user.{$userId}")
        ];
    }

    /**
     * Get conversation-specific broadcast channels
     */
    public function getConversationChannels(Conversation $conversation): array
    {
        $channels = [
            new PrivateChannel("account.{$conversation->account_id}"),
            new PrivateChannel("conversation.{$conversation->id}")
        ];

        // Add contact channel if conversation has contact
        if ($conversation->contact_id) {
            $channels[] = new PrivateChannel("contact.{$conversation->contact_id}");
        }

        // Add inbox channel for widget conversations
        if ($conversation->inbox_id) {
            $channels[] = new PrivateChannel("inbox.{$conversation->inbox_id}");
        }

        return $channels;
    }

    /**
     * Get contact-specific broadcast channels
     */
    public function getContactChannels(Contact $contact): array
    {
        $channels = [
            new PrivateChannel("account.{$contact->account_id}"),
            new PrivateChannel("contact.{$contact->id}")
        ];

        // Add inbox channels for contact's conversations
        $inboxIds = $contact->conversations()->distinct('inbox_id')->pluck('inbox_id');
        foreach ($inboxIds as $inboxId) {
            if ($inboxId) {
                $channels[] = new PrivateChannel("inbox.{$inboxId}");
            }
        }

        return $channels;
    }

    /**
     * Get inbox-specific broadcast channels
     */
    public function getInboxChannels(int $inboxId, int $accountId): array
    {
        return [
            new PrivateChannel("account.{$accountId}"),
            new PrivateChannel("inbox.{$inboxId}")
        ];
    }

    /**
     * Get channels for broadcasting to all account users
     */
    public function getAccountUserChannels(int $accountId): array
    {
        return [
            new PrivateChannel("account.{$accountId}")
        ];
    }

    /**
     * Get channels for broadcasting to specific users and contacts
     */
    public function getMultiUserChannels(array $userIds = [], array $contactIds = []): array
    {
        $channels = [];

        // Add user channels
        foreach ($userIds as $userId) {
            $channels[] = new PrivateChannel("user.{$userId}");
        }

        // Add contact channels
        foreach ($contactIds as $contactId) {
            $channels[] = new PrivateChannel("contact.{$contactId}");
        }

        return $channels;
    }

    /**
     * Get presence channels for account
     */
    public function getPresenceChannels(int $accountId): array
    {
        return [
            new PresenceChannel("account.{$accountId}.presence")
        ];
    }

    /**
     * Determine appropriate channels based on event context
     */
    public function getChannelsForEvent(string $eventType, array $context): array
    {
        return match ($eventType) {
            'message.created', 'message.updated', 'message.deleted' => 
                $this->getConversationChannels($context['conversation']),
            
            'conversation.created', 'conversation.updated', 'conversation.status_changed',
            'assignee.changed', 'team.changed', 'conversation.contact_changed' =>
                $this->getConversationChannels($context['conversation']),
            
            'conversation.read', 'conversation.typing_on', 'conversation.typing_off' =>
                $this->getConversationChannels($context['conversation']),
            
            'notification.created', 'notification.updated', 'notification.deleted',
            'conversation.mentioned' =>
                $this->getUserChannels($context['user_id']),
            
            'contact.created', 'contact.updated', 'contact.merged', 'contact.deleted' =>
                $this->getAccountUserChannels($context['account_id']),
            
            'first.reply.created', 'account.cache_invalidated' =>
                $this->getAccountUserChannels($context['account_id']),
            
            'presence.update' =>
                $this->getPresenceChannels($context['account_id']),
            
            default => []
        };
    }
}