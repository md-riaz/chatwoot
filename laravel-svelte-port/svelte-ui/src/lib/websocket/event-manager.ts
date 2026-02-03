/**
 * WebSocket Event Manager
 * Centralized event handling for all WebSocket events from Laravel backend
 */

import { getReverbClient } from './reverb-client';
import { conversationsStore } from '$lib/stores/conversations.svelte';
import { notificationsStore } from '$lib/stores/notifications.svelte';
import { contactsStore } from '$lib/stores/contacts.svelte';
import { presenceStore } from './presence-store.svelte.js';

export interface AccountEventHandlers {
  onMessageCreated: (data: any) => void;
  onConversationCreated: (data: any) => void;
  onConversationUpdated: (data: any) => void;
  onConversationStatusChanged: (data: any) => void;
  onAssigneeChanged: (data: any) => void;
  onTeamChanged: (data: any) => void;
  onContactCreated: (data: any) => void;
  onContactUpdated: (data: any) => void;
  onContactMerged: (data: any) => void;
  onContactDeleted: (data: any) => void;
  onFirstReplyCreated: (data: any) => void;
  onCacheInvalidated: (data: any) => void;
  onPresenceUpdate?: (data: any) => void;
  onMemberAdded?: (member: any) => void;
  onMemberRemoved?: (member: any) => void;
}

export interface UserEventHandlers {
  onNotificationCreated: (data: any) => void;
  onNotificationUpdated: (data: any) => void;
  onNotificationDeleted: (data: any) => void;
  onConversationMentioned: (data: any) => void;
}

export interface ConversationEventHandlers {
  onMessageCreated: (data: any) => void;
  onMessageUpdated: (data: any) => void;
  onMessageDeleted: (data: any) => void;
  onConversationRead: (data: any) => void;
  onTypingOn: (data: any) => void;
  onTypingOff: (data: any) => void;
  onContactChanged: (data: any) => void;
}

export class WebSocketEventManager {
  private accountUnsubscribe: (() => void) | null = null;
  private userUnsubscribe: (() => void) | null = null;
  private conversationUnsubscribes = new Map<number, () => void>();

  /**
   * Initialize WebSocket subscriptions for a user account
   */
  initializeForAccount(accountId: number, userId: number): void {
    const client = getReverbClient();

    // Subscribe to account-level events
    this.accountUnsubscribe = this.subscribeToAccount(client, accountId);

    // Subscribe to user-level events
    this.userUnsubscribe = this.subscribeToUser(client, userId);
  }

  /**
   * Subscribe to conversation-specific events
   */
  subscribeToConversation(conversationId: number): void {
    if (this.conversationUnsubscribes.has(conversationId)) {
      return; // Already subscribed
    }

    const client = getReverbClient();
    const unsubscribeFunctions: (() => void)[] = [];

    // Subscribe to conversation events
    unsubscribeFunctions.push(
      client.subscribePrivate(`conversation.${conversationId}`, 'message.created', this.getConversationEventHandlers().onMessageCreated),
      client.subscribePrivate(`conversation.${conversationId}`, 'message.updated', this.getConversationEventHandlers().onMessageUpdated),
      client.subscribePrivate(`conversation.${conversationId}`, 'message.deleted', this.getConversationEventHandlers().onMessageDeleted),
      client.subscribePrivate(`conversation.${conversationId}`, 'conversation.read', this.getConversationEventHandlers().onConversationRead),
      client.subscribePrivate(`conversation.${conversationId}`, 'conversation.typing_on', this.getConversationEventHandlers().onTypingOn),
      client.subscribePrivate(`conversation.${conversationId}`, 'conversation.typing_off', this.getConversationEventHandlers().onTypingOff),
      client.subscribePrivate(`conversation.${conversationId}`, 'conversation.contact_changed', this.getConversationEventHandlers().onContactChanged)
    );

    // Combined unsubscribe function
    const unsubscribe = () => {
      unsubscribeFunctions.forEach(fn => fn());
    };

    this.conversationUnsubscribes.set(conversationId, unsubscribe);
  }

  /**
   * Unsubscribe from conversation events
   */
  unsubscribeFromConversation(conversationId: number): void {
    const unsubscribe = this.conversationUnsubscribes.get(conversationId);
    if (unsubscribe) {
      unsubscribe();
      this.conversationUnsubscribes.delete(conversationId);
    }
  }

  /**
   * Clean up all subscriptions
   */
  cleanup(): void {
    // Unsubscribe from account and user events
    this.accountUnsubscribe?.();
    this.userUnsubscribe?.();

    // Unsubscribe from all conversation events
    this.conversationUnsubscribes.forEach(unsubscribe => unsubscribe());
    this.conversationUnsubscribes.clear();

    this.accountUnsubscribe = null;
    this.userUnsubscribe = null;
  }

  /**
   * Subscribe to account-level events
   */
  private subscribeToAccount(client: any, accountId: number): () => void {
    const unsubscribeFunctions: (() => void)[] = [];
    const handlers = this.getAccountEventHandlers();

    // Subscribe to account channel events
    unsubscribeFunctions.push(
      client.subscribePrivate(`account.${accountId}`, 'message.created', handlers.onMessageCreated),
      client.subscribePrivate(`account.${accountId}`, 'conversation.created', handlers.onConversationCreated),
      client.subscribePrivate(`account.${accountId}`, 'conversation.updated', handlers.onConversationUpdated),
      client.subscribePrivate(`account.${accountId}`, 'conversation.status_changed', handlers.onConversationStatusChanged),
      client.subscribePrivate(`account.${accountId}`, 'assignee.changed', handlers.onAssigneeChanged),
      client.subscribePrivate(`account.${accountId}`, 'team.changed', handlers.onTeamChanged),
      client.subscribePrivate(`account.${accountId}`, 'contact.created', handlers.onContactCreated),
      client.subscribePrivate(`account.${accountId}`, 'contact.updated', handlers.onContactUpdated),
      client.subscribePrivate(`account.${accountId}`, 'contact.merged', handlers.onContactMerged),
      client.subscribePrivate(`account.${accountId}`, 'contact.deleted', handlers.onContactDeleted),
      client.subscribePrivate(`account.${accountId}`, 'first.reply.created', handlers.onFirstReplyCreated),
      client.subscribePrivate(`account.${accountId}`, 'account.cache_invalidated', handlers.onCacheInvalidated)
    );

    // Subscribe to presence channel
    unsubscribeFunctions.push(
      client.subscribePresence(`account.${accountId}.presence`, {
        onMessage: (eventName: string, data: any) => {
          if (eventName === 'presence.update' && handlers.onPresenceUpdate) {
            handlers.onPresenceUpdate(data);
          }
        },
        onMemberAdded: handlers.onMemberAdded,
        onMemberRemoved: handlers.onMemberRemoved,
      })
    );

    // Return combined unsubscribe function
    return () => {
      unsubscribeFunctions.forEach(unsubscribe => unsubscribe());
    };
  }

  /**
   * Subscribe to user-level events
   */
  private subscribeToUser(client: any, userId: number): () => void {
    const unsubscribeFunctions: (() => void)[] = [];
    const handlers = this.getUserEventHandlers();

    unsubscribeFunctions.push(
      client.subscribePrivate(`user.${userId}`, 'notification.created', handlers.onNotificationCreated),
      client.subscribePrivate(`user.${userId}`, 'notification.updated', handlers.onNotificationUpdated),
      client.subscribePrivate(`user.${userId}`, 'notification.deleted', handlers.onNotificationDeleted),
      client.subscribePrivate(`user.${userId}`, 'conversation.mentioned', handlers.onConversationMentioned)
    );

    return () => {
      unsubscribeFunctions.forEach(unsubscribe => unsubscribe());
    };
  }

  /**
   * Get account event handlers
   */
  private getAccountEventHandlers(): AccountEventHandlers {
    return {
      onMessageCreated: (data) => {
        console.log('Message created:', data);
        if (data.message) {
          conversationsStore.handleMessageCreated(data.message);
        }
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }
      },

      onConversationCreated: (data) => {
        console.log('Conversation created:', data);
        if (data.conversation) {
          conversationsStore.addConversation(data.conversation);
        }
      },

      onConversationUpdated: (data) => {
        console.log('Conversation updated:', data);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }
      },

      onConversationStatusChanged: (data) => {
        console.log('Conversation status changed:', data);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }
      },

      onAssigneeChanged: (data) => {
        console.log('Assignee changed:', data);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }
      },

      onTeamChanged: (data) => {
        console.log('Team changed:', data);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }
      },

      onContactCreated: (data) => {
        console.log('Contact created:', data);
        if (data.contact) {
          contactsStore.addOrUpdateContact(data.contact);
        }
      },

      onContactUpdated: (data) => {
        console.log('Contact updated:', data);
        if (data.contact) {
          contactsStore.addOrUpdateContact(data.contact);
        }
      },

      onContactMerged: (data) => {
        console.log('Contact merged:', data);
        if (data.primary_contact && data.merged_contact) {
          contactsStore.addOrUpdateContact(data.primary_contact);
          contactsStore.removeContact(data.merged_contact.id);
        }
      },

      onContactDeleted: (data) => {
        console.log('Contact deleted:', data);
        if (data.id) {
          contactsStore.removeContact(data.id);
        }
      },

      onFirstReplyCreated: (data) => {
        console.log('First reply created:', data);
        if (data.conversation) {
          conversationsStore.markFirstReply(data.conversation.id, data.message);
        }
      },

      onCacheInvalidated: (data) => {
        console.log('Cache invalidated:', data);
        // Handle cache invalidation - refresh relevant data
        if (data.invalidated_keys?.includes('conversations')) {
          conversationsStore.refreshConversations();
        }
        if (data.invalidated_keys?.includes('contacts')) {
          contactsStore.fetchContacts();
        }
      },

      onPresenceUpdate: (data) => {
        console.log('Presence update:', data);
        if (data.user) {
          presenceStore.updateUserPresence(data.user, data.status, data.metadata);
        }
      },

      onMemberAdded: (member) => {
        console.log('Member added to presence:', member);
        presenceStore.addMember(member);
      },

      onMemberRemoved: (member) => {
        console.log('Member removed from presence:', member);
        presenceStore.removeMember(member);
      },
    };
  }

  /**
   * Get user event handlers
   */
  private getUserEventHandlers(): UserEventHandlers {
    return {
      onNotificationCreated: (data) => {
        console.log('Notification created:', data);
        if (data.notification) {
          notificationsStore.handleNewNotification(data.notification);
        }
      },

      onNotificationUpdated: (data) => {
        console.log('Notification updated:', data);
        if (data.notification) {
          notificationsStore.handleNotificationUpdated(data.notification);
        }
      },

      onNotificationDeleted: (data) => {
        console.log('Notification deleted:', data);
        if (data.id) {
          notificationsStore.handleNotificationDeleted(data.id);
        }
      },

      onConversationMentioned: (data) => {
        console.log('Conversation mentioned:', data);
        if (data.conversation && data.message) {
          notificationsStore.addMentionNotification(data.conversation, data.message);
        }
      },
    };
  }

  /**
   * Get conversation event handlers
   */
  private getConversationEventHandlers(): ConversationEventHandlers {
    return {
      onMessageCreated: (data) => {
        console.log('Conversation message created:', data);
        if (data.message) {
          conversationsStore.handleMessageCreated(data.message);
        }
      },

      onMessageUpdated: (data) => {
        console.log('Message updated:', data);
        if (data.message) {
          conversationsStore.updateMessage(data.message);
        }
      },

      onMessageDeleted: (data) => {
        console.log('Message deleted:', data);
        if (data.id) {
          conversationsStore.removeMessage(data.id);
        }
      },

      onConversationRead: (data) => {
        console.log('Conversation read:', data);
        if (data.conversation?.id) {
          conversationsStore.markAsRead(data.conversation.id);
        }
      },

      onTypingOn: (data) => {
        console.log('Typing started:', data);
        if (data.conversation_id && data.typer) {
          conversationsStore.setTyping(data.conversation_id, data.typer, true);
        }
      },

      onTypingOff: (data) => {
        console.log('Typing stopped:', data);
        if (data.conversation_id && data.typer) {
          conversationsStore.setTyping(data.conversation_id, data.typer, false);
        }
      },

      onContactChanged: (data) => {
        console.log('Conversation contact changed:', data);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }
      },
    };
  }
}

// Singleton instance
let eventManager: WebSocketEventManager | null = null;

/**
 * Get WebSocket event manager instance
 */
export function getWebSocketEventManager(): WebSocketEventManager {
  if (!eventManager) {
    eventManager = new WebSocketEventManager();
  }
  return eventManager;
}

/**
 * Reset event manager (useful for testing)
 */
export function resetWebSocketEventManager(): void {
  if (eventManager) {
    eventManager.cleanup();
    eventManager = null;
  }
}