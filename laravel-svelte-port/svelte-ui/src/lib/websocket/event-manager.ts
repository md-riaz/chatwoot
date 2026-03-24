/**
 * WebSocket Event Manager
 * Centralized event handling for all WebSocket events from Laravel backend
 * Implements Vue ActionCable parity with mutation/action pattern and security validation
 */

import { getReverbClient } from './reverb-client';
import { conversationsStore } from '$lib/stores/conversations.svelte';
import { notificationsStore } from '$lib/stores/notifications.svelte';
import { contactsStore } from '$lib/stores/contacts.svelte';
import { presenceStore } from './presence-store.svelte.js';
import { authStore } from '$lib/stores/auth.svelte';
import { agentsStore } from '$lib/stores/agents.svelte';
import { teamsStore } from '$lib/stores/teams.svelte';
import { eventBus, BUS_EVENTS } from '$lib/utils/event-bus';
import { audioNotificationManager } from '$lib/utils/audio-notifications';
import type {
  AccountPresencePayload,
  AssigneeChangedPayload,
  RealtimeEventEnvelope,
  TeamChangedPayload,
} from './types';

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
  private currentAccountId: number | null = null;
  private currentUserId: number | null = null;
  private presenceInterval: number | null = null;
  private lastMessageId: number | null = null;
  private readonly PRESENCE_INTERVAL = 20000; // 20 seconds (matching Vue)

  // Typing timeout management (matching Vue implementation)
  private typingTimeouts = new Map<number, number>(); // conversationId -> timeoutId
  private readonly TYPING_TIMEOUT = 30000; // 30 seconds (matching Vue)

  /**
   * Initialize WebSocket subscriptions for a user account
   * Implements Vue ActionCable parity with account validation
   */
  initializeForAccount(accountId: number, userId: number): void {
    // Store current account/user for validation
    this.currentAccountId = accountId;
    this.currentUserId = userId;

    const client = getReverbClient();

    // Subscribe to account-level events
    this.accountUnsubscribe = this.subscribeToAccount(client, accountId);

    // Subscribe to user-level events
    this.userUnsubscribe = this.subscribeToUser(client, userId);

    // Start presence updates (matching Vue 20-second interval)
    this.startPresenceUpdates();

    // Emit reconnect event for stats refresh
    eventBus.emit(BUS_EVENTS.WEBSOCKET_RECONNECT);
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
      client.subscribePrivate(
        `conversation.${conversationId}`,
        'message.created',
        this.getConversationEventHandlers().onMessageCreated
      ),
      client.subscribePrivate(
        `conversation.${conversationId}`,
        'message.updated',
        this.getConversationEventHandlers().onMessageUpdated
      ),
      client.subscribePrivate(
        `conversation.${conversationId}`,
        'message.deleted',
        this.getConversationEventHandlers().onMessageDeleted
      ),
      client.subscribePrivate(
        `conversation.${conversationId}`,
        'conversation.read',
        this.getConversationEventHandlers().onConversationRead
      ),
      client.subscribePrivate(
        `conversation.${conversationId}`,
        'conversation.typing_on',
        this.getConversationEventHandlers().onTypingOn
      ),
      client.subscribePrivate(
        `conversation.${conversationId}`,
        'conversation.typing_off',
        this.getConversationEventHandlers().onTypingOff
      ),
      client.subscribePrivate(
        `conversation.${conversationId}`,
        'conversation.contact_changed',
        this.getConversationEventHandlers().onContactChanged
      )
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
    // Stop presence updates
    this.stopPresenceUpdates();

    // Clear all typing timeouts
    this.clearAllTypingTimeouts();

    // Unsubscribe from account and user events
    this.accountUnsubscribe?.();
    this.userUnsubscribe?.();

    // Unsubscribe from all conversation events
    this.conversationUnsubscribes.forEach(unsubscribe => unsubscribe());
    this.conversationUnsubscribes.clear();

    this.accountUnsubscribe = null;
    this.userUnsubscribe = null;
    this.currentAccountId = null;
    this.currentUserId = null;

    // Emit disconnect event
    eventBus.emit(BUS_EVENTS.WEBSOCKET_DISCONNECT);
  }

  /**
   * Account event validation (Critical security feature from Vue)
   * Prevents cross-account event leakage
   */
  private isValidEvent(data: RealtimeEventEnvelope): boolean {
    if (!this.currentAccountId) {
      console.warn('No current account ID set for event validation');
      return false;
    }

    const isValid = this.currentAccountId === data.account_id;
    if (!isValid) {
      console.warn(
        `Ignoring event for different account: ${data.account_id} (current: ${this.currentAccountId})`
      );
    }

    return isValid;
  }

  /**
   * User event validation
   * Prevents cross-user notifications leaking between sessions
   */
  private isValidUserEvent(data: RealtimeEventEnvelope): boolean {
    if (!this.isValidEvent(data)) return false;

    if (!this.currentUserId) {
      console.warn('No current user ID set for user event validation');
      return false;
    }

    if (typeof data.user_id !== 'number') {
      console.warn('User event missing user_id, rejecting for safety');
      return false;
    }

    const isValid = data.user_id === this.currentUserId;
    if (!isValid) {
      console.warn(
        `Ignoring event for different user: ${data.user_id} (current: ${this.currentUserId})`
      );
    }

    return isValid;
  }

  /**
   * Start presence updates (matching Vue 20-second interval)
   */
  private startPresenceUpdates(): void {
    if (this.presenceInterval) return;

    this.presenceInterval = window.setInterval(() => {
      const client = getReverbClient();
      try {
        // Update presence on server
        // Note: updatePresence may not be available on all ReverbClient implementations
        if (
          'updatePresence' in client &&
          typeof (client as any).updatePresence === 'function'
        ) {
          (client as any).updatePresence();
        }
      } catch (error) {
        console.error('Failed to update presence:', error);
      }
    }, this.PRESENCE_INTERVAL);
  }

  /**
   * Stop presence updates
   */
  private stopPresenceUpdates(): void {
    if (this.presenceInterval) {
      clearInterval(this.presenceInterval);
      this.presenceInterval = null;
    }
  }

  /**
   * Set last message ID for sync on reconnect
   */
  setLastMessageId(): void {
    this.lastMessageId = conversationsStore.setLastMessageId();
    if (this.lastMessageId) {
      console.log('Set last message ID for sync:', this.lastMessageId);
    }
  }

  /**
   * Sync latest messages on reconnect (matching Vue implementation)
   */
  async syncLatestMessages(): Promise<void> {
    if (!this.lastMessageId || !this.currentAccountId) return;

    try {
      // Import the messages API
      const { getMessagesSince } = await import('$lib/api/messages');

      // Get all conversations and sync messages for each
      const conversations = conversationsStore.allConversations;

      for (const conversation of conversations) {
        try {
          const newMessages = await getMessagesSince(
            this.currentAccountId,
            conversation.id,
            this.lastMessageId
          );

          // Add each new message to the conversation
          newMessages.forEach(message => {
            conversationsStore.handleMessageCreated(message);
          });

          console.log(
            `Synced ${newMessages.length} messages for conversation ${conversation.id}`
          );
        } catch (error) {
          console.error(
            `Failed to sync messages for conversation ${conversation.id}:`,
            error
          );
        }
      }
    } catch (error) {
      console.error('Failed to sync messages:', error);
    }
  }

  /**
   * Handle reconnection (matching Vue onReconnect)
   */
  onReconnect(): void {
    this.syncLatestMessages();
    eventBus.emit(BUS_EVENTS.WEBSOCKET_RECONNECT);
  }

  /**
   * Handle disconnection (matching Vue onDisconnected)
   */
  onDisconnected(): void {
    this.setLastMessageId();
    eventBus.emit(BUS_EVENTS.WEBSOCKET_DISCONNECT);
  }

  /**
   * Clear typing timeout for a conversation (matching Vue implementation)
   */
  private clearTypingTimeout(conversationId: number): void {
    const timeoutId = this.typingTimeouts.get(conversationId);
    if (timeoutId) {
      clearTimeout(timeoutId);
      this.typingTimeouts.delete(conversationId);
    }
  }

  /**
   * Set typing timeout for a conversation (matching Vue implementation)
   */
  private setTypingTimeout(conversationId: number, typer: any): void {
    // Clear existing timeout
    this.clearTypingTimeout(conversationId);

    // Set new timeout to automatically turn off typing after 30 seconds
    const timeoutId = window.setTimeout(() => {
      console.log(
        `Auto-clearing typing for conversation ${conversationId} after 30 seconds`
      );
      conversationsStore.setTyping(conversationId, typer, false);
      this.typingTimeouts.delete(conversationId);
    }, this.TYPING_TIMEOUT);

    this.typingTimeouts.set(conversationId, timeoutId);
  }

  /**
   * Clear all typing timeouts (cleanup method)
   */
  private clearAllTypingTimeouts(): void {
    this.typingTimeouts.forEach(timeoutId => {
      clearTimeout(timeoutId);
    });
    this.typingTimeouts.clear();
  }

  /**
   * Subscribe to account-level events
   */
  private subscribeToAccount(client: any, accountId: number): () => void {
    const unsubscribeFunctions: (() => void)[] = [];
    const handlers = this.getAccountEventHandlers();

    // Subscribe to account channel events
    unsubscribeFunctions.push(
      client.subscribePrivate(
        `account.${accountId}`,
        'message.created',
        handlers.onMessageCreated
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'conversation.created',
        handlers.onConversationCreated
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'conversation.updated',
        handlers.onConversationUpdated
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'conversation.status_changed',
        handlers.onConversationStatusChanged
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'assignee.changed',
        handlers.onAssigneeChanged
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'team.changed',
        handlers.onTeamChanged
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'contact.created',
        handlers.onContactCreated
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'contact.updated',
        handlers.onContactUpdated
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'contact.merged',
        handlers.onContactMerged
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'contact.deleted',
        handlers.onContactDeleted
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'first.reply.created',
        handlers.onFirstReplyCreated
      ),
      client.subscribePrivate(
        `account.${accountId}`,
        'account.cache_invalidated',
        handlers.onCacheInvalidated
      )
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
      client.subscribePrivate(
        `user.${userId}`,
        'notification.created',
        handlers.onNotificationCreated
      ),
      client.subscribePrivate(
        `user.${userId}`,
        'notification.updated',
        handlers.onNotificationUpdated
      ),
      client.subscribePrivate(
        `user.${userId}`,
        'notification.deleted',
        handlers.onNotificationDeleted
      ),
      client.subscribePrivate(
        `user.${userId}`,
        'conversation.mentioned',
        handlers.onConversationMentioned
      )
    );

    return () => {
      unsubscribeFunctions.forEach(unsubscribe => unsubscribe());
    };
  }

  /**
   * Get account event handlers with Vue parity
   */
  private getAccountEventHandlers(): AccountEventHandlers {
    return {
      onMessageCreated: data => {
        // Account validation (Critical security feature)
        if (!this.isValidEvent(data)) return;

        console.log('Message created:', data);

        // Audio notification (matching Vue DashboardAudioNotificationHelper)
        audioNotificationManager.onNewMessage(data);

        if (data.message) {
          conversationsStore.handleMessageCreated(data.message);
        }
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onConversationCreated: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Conversation created:', data);
        if (data.conversation) {
          conversationsStore.addConversation(data.conversation);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onConversationUpdated: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Conversation updated:', data);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onConversationStatusChanged: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Conversation status changed:', data);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onAssigneeChanged: data => {
        if (!this.isValidEvent(data)) return;

        const payload = data as AssigneeChangedPayload;
        console.log('Assignee changed:', payload);

        if (payload.conversation) {
          conversationsStore.updateConversation(
            payload.conversation as unknown as Parameters<
              typeof conversationsStore.updateConversation
            >[0]
          );
        }

        if (payload.new_assignee) {
          agentsStore.addOrUpdateAgent(payload.new_assignee);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onTeamChanged: data => {
        if (!this.isValidEvent(data)) return;

        const payload = data as TeamChangedPayload;
        console.log('Team changed:', payload);

        if (payload.conversation) {
          conversationsStore.updateConversation(
            payload.conversation as unknown as Parameters<
              typeof conversationsStore.updateConversation
            >[0]
          );
        }

        if (payload.new_team) {
          teamsStore.addOrUpdateTeam(payload.new_team);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onContactCreated: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Contact created:', data);
        if (data.contact) {
          contactsStore.addOrUpdateContact(data.contact);
        }
      },

      onContactUpdated: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Contact updated:', data);
        if (data.contact) {
          contactsStore.addOrUpdateContact(data.contact);
        }
      },

      onContactMerged: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Contact merged:', data);
        if (data.primary_contact && data.merged_contact) {
          contactsStore.addOrUpdateContact(data.primary_contact);
          contactsStore.removeContact(data.merged_contact.id);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onContactDeleted: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Contact deleted:', data);
        if (data.id) {
          contactsStore.removeContact(data.id);
        }

        // Emit event for stats refresh (matching Vue)
        this.fetchConversationStats();
      },

      onFirstReplyCreated: data => {
        if (!this.isValidEvent(data)) return;

        console.log('First reply created:', data);
        if (data.conversation) {
          conversationsStore.markFirstReply(data.conversation.id, data.message);
        }
      },

      onCacheInvalidated: data => {
        if (!this.isValidEvent(data)) return;

        console.log('Cache invalidated:', data);

        // Enhanced cache revalidation (matching Vue granular approach)
        const keys = data.cache_keys || {};

        if (
          keys.conversations ||
          data.invalidated_keys?.includes('conversations')
        ) {
          conversationsStore.refreshConversations();
        }
        if (keys.contacts || data.invalidated_keys?.includes('contacts')) {
          contactsStore.fetchContacts();
        }
        if (keys.labels) {
          // Import and use labels store revalidate method
          import('$lib/stores/labels.svelte').then(({ labelsStore }) => {
            labelsStore.revalidate(keys.labels);
          });
        }
        if (keys.inboxes) {
          // Import and use inboxes store revalidate method
          import('$lib/stores/inboxes.svelte').then(({ inboxesStore }) => {
            inboxesStore.revalidate(keys.inboxes);
          });
        }
        if (keys.teams) {
          // Import and use teams store revalidate method
          import('$lib/stores/teams.svelte').then(({ teamsStore }) => {
            teamsStore.revalidate(keys.teams);
          });
        }

        // Emit cache invalidation event
        eventBus.emit(BUS_EVENTS.CACHE_INVALIDATED, data);
      },

      onPresenceUpdate: data => {
        if (!this.isValidEvent(data)) return;

        const payload = data as AccountPresencePayload;
        console.log('Presence update:', payload);
        if (payload.user) {
          presenceStore.updateUserPresence(
            payload.user,
            payload.status,
            payload.metadata
          );

          if (payload.user.type !== 'contact') {
            const availabilityStatus =
              payload.status === 'away' ? 'offline' : payload.status;
            agentsStore.updateSingleAgentPresence(
              payload.user.id,
              availabilityStatus
            );

            if (payload.user.id === authStore.currentUserId) {
              authStore.setCurrentUserAvailability({
                [payload.user.id]: availabilityStatus,
              });
            }
          }
        }
      },

      onMemberAdded: member => {
        console.log('Member added to presence:', member);
        presenceStore.addMember(member);

        if (member?.id && member.type !== 'contact') {
          agentsStore.updateSingleAgentPresence(member.id, 'online');
          if (member.id === authStore.currentUserId) {
            authStore.setCurrentUserAvailability({ [member.id]: 'online' });
          }
        }
      },

      onMemberRemoved: member => {
        console.log('Member removed from presence:', member);
        presenceStore.removeMember(member);

        if (member?.id && member.type !== 'contact') {
          agentsStore.updateSingleAgentPresence(member.id, 'offline');
          if (member.id === authStore.currentUserId) {
            authStore.setCurrentUserAvailability({ [member.id]: 'offline' });
          }
        }
      },
    };
  }

  /**
   * Fetch conversation stats (matching Vue implementation)
   */
  private fetchConversationStats(): void {
    eventBus.emit(BUS_EVENTS.FETCH_CONVERSATION_STATS);
  }

  /**
   * Get user event handlers with Vue parity
   */
  private getUserEventHandlers(): UserEventHandlers {
    return {
      onNotificationCreated: data => {
        // Account validation (Critical security feature)
        if (!this.isValidUserEvent(data)) return;

        console.log('Notification created:', data);
        if (data.notification) {
          notificationsStore.handleNewNotification(data.notification);
        }
      },

      onNotificationUpdated: data => {
        if (!this.isValidUserEvent(data)) return;

        console.log('Notification updated:', data);
        if (data.notification) {
          notificationsStore.handleNotificationUpdated(data.notification);
        }
      },

      onNotificationDeleted: data => {
        if (!this.isValidUserEvent(data)) return;

        console.log('Notification deleted:', data);
        if (data.id) {
          notificationsStore.handleNotificationDeleted(data.id);
        }
      },

      onConversationMentioned: data => {
        if (!this.isValidUserEvent(data)) return;

        console.log('Conversation mentioned:', data);
        if (data.conversation && data.message) {
          notificationsStore.addMentionNotification(
            data.conversation,
            data.message
          );

          // Play mention sound (matching Vue)
          audioNotificationManager.onMention();

          // Emit mention event
          eventBus.emit(BUS_EVENTS.CONVERSATION_MENTIONED, data);
        }
      },
    };
  }

  /**
   * Get conversation event handlers with private message filtering
   */
  private getConversationEventHandlers(): ConversationEventHandlers {
    return {
      onMessageCreated: data => {
        console.log('Conversation message created:', data);

        // Private message filtering (Critical security feature from Vue)
        if (data.is_private) {
          console.log('Filtering private message');
          return;
        }

        if (data.message) {
          conversationsStore.handleMessageCreated(data.message);
        }
      },

      onMessageUpdated: data => {
        console.log('Message updated:', data);

        // Private message filtering
        if (data.is_private) {
          return;
        }

        if (data.message) {
          conversationsStore.updateMessage(data.message);
        }
      },

      onMessageDeleted: data => {
        console.log('Message deleted:', data);
        if (data.id) {
          conversationsStore.removeMessage(data.id);
        }
      },

      onConversationRead: data => {
        console.log('Conversation read:', data);
        if (data.conversation?.id) {
          conversationsStore.markAsRead(data.conversation.id);
        }
      },

      onTypingOn: data => {
        console.log('Typing started:', data);

        // Filter typing from other conversations and private messages (matching Vue)
        const activeConversationId = conversationsStore.selectedConversationId;
        const isUserTypingOnAnotherConversation =
          data.conversation && data.conversation.id !== activeConversationId;

        if (isUserTypingOnAnotherConversation || data.is_private) {
          return;
        }

        if (data.conversation_id && data.typer) {
          // Clear existing timeout and set typing
          this.clearTypingTimeout(data.conversation_id);
          conversationsStore.setTyping(data.conversation_id, data.typer, true);

          // Set 30-second auto-timeout (matching Vue implementation)
          this.setTypingTimeout(data.conversation_id, data.typer);
        }
      },

      onTypingOff: data => {
        console.log('Typing stopped:', data);

        // Filter typing from other conversations and private messages
        const activeConversationId = conversationsStore.selectedConversationId;
        const isUserTypingOnAnotherConversation =
          data.conversation && data.conversation.id !== activeConversationId;

        if (isUserTypingOnAnotherConversation || data.is_private) {
          return;
        }

        if (data.conversation_id && data.typer) {
          // Clear timeout and stop typing
          this.clearTypingTimeout(data.conversation_id);
          conversationsStore.setTyping(data.conversation_id, data.typer, false);
        }
      },

      onContactChanged: data => {
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
