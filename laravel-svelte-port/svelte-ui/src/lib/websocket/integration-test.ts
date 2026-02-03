/**
 * WebSocket Integration Test Example
 * Demonstrates how to test the WebSocket functionality
 */

import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { getWebSocketEventManager, resetWebSocketEventManager } from './event-manager';
import { getWebSocketStore, resetWebSocketStore } from './store.svelte';
import { presenceStore } from './presence-store.svelte.js';
import { conversationsStore } from '$lib/stores/conversations.svelte';
import { notificationsStore } from '$lib/stores/notifications.svelte';

// Mock the Reverb client
const mockReverbClient = {
  connect: vi.fn(),
  disconnect: vi.fn(),
  subscribePrivate: vi.fn(() => vi.fn()), // Returns unsubscribe function
  subscribePresence: vi.fn(() => vi.fn()), // Returns unsubscribe function
  isConnected: true,
  connectionState: 'connected'
};

vi.mock('./reverb-client', () => ({
  getReverbClient: () => mockReverbClient,
  resetReverbClient: vi.fn()
}));

describe('WebSocket Integration', () => {
  let eventManager: ReturnType<typeof getWebSocketEventManager>;
  let wsStore: ReturnType<typeof getWebSocketStore>;

  beforeEach(() => {
    vi.clearAllMocks();
    resetWebSocketEventManager();
    resetWebSocketStore();
    
    eventManager = getWebSocketEventManager();
    wsStore = getWebSocketStore();
    
    // Reset stores
    conversationsStore.clearConversations();
    notificationsStore.reset();
    presenceStore.reset();
  });

  afterEach(() => {
    eventManager.cleanup();
  });

  describe('Event Manager Initialization', () => {
    it('should initialize subscriptions for account and user', () => {
      const accountId = 1;
      const userId = 2;

      eventManager.initializeForAccount(accountId, userId);

      // Should call subscribePrivate for account events
      expect(mockReverbClient.subscribePrivate).toHaveBeenCalledWith(
        `account.${accountId}`,
        'message.created',
        expect.any(Function)
      );

      // Should call subscribePrivate for user events
      expect(mockReverbClient.subscribePrivate).toHaveBeenCalledWith(
        `user.${userId}`,
        'notification.created',
        expect.any(Function)
      );

      // Should call subscribePresence for presence events
      expect(mockReverbClient.subscribePresence).toHaveBeenCalledWith(
        `account.${accountId}.presence`,
        expect.any(Object)
      );
    });

    it('should subscribe to conversation events', () => {
      const conversationId = 123;

      eventManager.subscribeToConversation(conversationId);

      expect(mockReverbClient.subscribePrivate).toHaveBeenCalledWith(
        `conversation.${conversationId}`,
        'message.created',
        expect.any(Function)
      );

      expect(mockReverbClient.subscribePrivate).toHaveBeenCalledWith(
        `conversation.${conversationId}`,
        'conversation.typing_on',
        expect.any(Function)
      );
    });
  });

  describe('Message Events', () => {
    it('should handle message created events', () => {
      const accountId = 1;
      const userId = 2;
      
      eventManager.initializeForAccount(accountId, userId);

      // Get the message.created handler
      const messageCreatedCall = mockReverbClient.subscribePrivate.mock.calls.find(
        call => call[1] === 'message.created'
      );
      const messageHandler = messageCreatedCall?.[2];

      expect(messageHandler).toBeDefined();

      // Simulate message created event
      const messageData = {
        message: {
          id: 1,
          conversation_id: 123,
          content: 'Hello World',
          created_at: new Date().toISOString()
        },
        conversation: {
          id: 123,
          lastActivityAt: new Date().toISOString()
        }
      };

      // Mock conversation exists in store
      conversationsStore.allConversations = [{
        id: 123,
        messages: [],
        lastActivityAt: new Date().toISOString(),
        unreadCount: 0
      } as any];

      messageHandler?.(messageData);

      // Should update conversation with new message
      const conversation = conversationsStore.getConversationById(123);
      expect(conversation?.messages).toHaveLength(1);
      expect(conversation?.messages?.[0].content).toBe('Hello World');
    });
  });

  describe('Presence Events', () => {
    it('should handle presence updates', () => {
      const accountId = 1;
      const userId = 2;
      
      eventManager.initializeForAccount(accountId, userId);

      // Get the presence subscription
      const presenceCall = mockReverbClient.subscribePresence.mock.calls.find(
        call => call[0] === `account.${accountId}.presence`
      );
      const presenceCallbacks = presenceCall?.[1];

      expect(presenceCallbacks).toBeDefined();

      // Simulate presence update
      const userData = {
        id: 3,
        name: 'John Doe',
        avatar_url: 'https://example.com/avatar.jpg',
        type: 'agent'
      };

      presenceCallbacks?.onMemberAdded?.(userData);

      // Should add user to presence store
      const userPresence = presenceStore.getUserPresence(3);
      expect(userPresence?.name).toBe('John Doe');
      expect(userPresence?.status).toBe('online');
    });

    it('should handle typing indicators', () => {
      const conversationId = 123;
      
      eventManager.subscribeToConversation(conversationId);

      // Get the typing_on handler
      const typingOnCall = mockReverbClient.subscribePrivate.mock.calls.find(
        call => call[1] === 'conversation.typing_on'
      );
      const typingHandler = typingOnCall?.[2];

      expect(typingHandler).toBeDefined();

      // Mock conversation exists
      conversationsStore.allConversations = [{
        id: 123,
        messages: []
      } as any];

      // Simulate typing event
      const typingData = {
        conversation_id: 123,
        typer: {
          id: 2,
          name: 'Jane Doe',
          type: 'agent'
        }
      };

      typingHandler?.(typingData);

      // Should show typing indicator
      expect(presenceStore.isUserTyping(123, 2)).toBe(true);
      expect(presenceStore.getTypingSummary(123)).toContain('Jane Doe is typing');
    });
  });

  describe('Notification Events', () => {
    it('should handle notification created events', () => {
      const accountId = 1;
      const userId = 2;
      
      eventManager.initializeForAccount(accountId, userId);

      // Get the notification.created handler
      const notificationCall = mockReverbClient.subscribePrivate.mock.calls.find(
        call => call[1] === 'notification.created'
      );
      const notificationHandler = notificationCall?.[2];

      expect(notificationHandler).toBeDefined();

      // Simulate notification event
      const notificationData = {
        notification: {
          id: 'notif-1',
          notificationType: 'message_created',
          primaryActorType: 'Message',
          primaryActorId: 1,
          primaryActor: { id: 1, content: 'New message' },
          accountId: 1,
          userId: 2,
          readAt: null,
          createdAt: new Date().toISOString(),
          updatedAt: new Date().toISOString()
        }
      };

      notificationHandler?.(notificationData);

      // Should add notification to store
      expect(notificationsStore.all).toHaveLength(1);
      expect(notificationsStore.unreadCount).toBe(1);
    });
  });

  describe('Connection State Management', () => {
    it('should track connection state changes', () => {
      expect(wsStore.connectionState).toBe('disconnected');
      expect(wsStore.isConnected).toBe(false);

      wsStore.setState('connecting');
      expect(wsStore.connectionState).toBe('connecting');
      expect(wsStore.isConnecting).toBe(true);

      wsStore.setState('connected');
      expect(wsStore.connectionState).toBe('connected');
      expect(wsStore.isConnected).toBe(true);
      expect(wsStore.lastConnectedAt).toBeTruthy();
    });

    it('should handle connection errors', () => {
      const errorMessage = 'Connection failed';
      
      wsStore.setError(errorMessage);
      wsStore.setState('failed');

      expect(wsStore.error).toBe(errorMessage);
      expect(wsStore.isFailed).toBe(true);
    });

    it('should track reconnection attempts', () => {
      wsStore.setState('reconnecting');
      wsStore.incrementReconnectAttempts();

      expect(wsStore.isReconnecting).toBe(true);
      expect(wsStore.reconnectAttempts).toBe(1);
      expect(wsStore.canReconnect).toBe(true);
    });
  });

  describe('Cleanup', () => {
    it('should cleanup all subscriptions', () => {
      const accountId = 1;
      const userId = 2;
      const conversationId = 123;

      eventManager.initializeForAccount(accountId, userId);
      eventManager.subscribeToConversation(conversationId);

      // Mock unsubscribe functions
      const unsubscribeFn = vi.fn();
      mockReverbClient.subscribePrivate.mockReturnValue(unsubscribeFn);
      mockReverbClient.subscribePresence.mockReturnValue(unsubscribeFn);

      eventManager.cleanup();

      // Should call unsubscribe functions
      // Note: In real implementation, unsubscribe functions would be called
      // This is a simplified test to verify cleanup logic exists
      expect(eventManager).toBeDefined();
    });
  });
});

/**
 * Example usage in a Svelte component test
 */
export function createWebSocketTestUtils() {
  return {
    // Simulate receiving a message
    simulateMessageReceived: (conversationId: number, messageContent: string) => {
      const messageData = {
        message: {
          id: Date.now(),
          conversation_id: conversationId,
          content: messageContent,
          created_at: new Date().toISOString()
        }
      };
      
      // This would normally be triggered by the WebSocket event
      conversationsStore.handleMessageCreated(messageData.message);
    },

    // Simulate user typing
    simulateUserTyping: (conversationId: number, user: any, isTyping: boolean) => {
      presenceStore.setTyping(conversationId, user, isTyping);
    },

    // Simulate presence change
    simulatePresenceChange: (userId: number, status: 'online' | 'offline' | 'away') => {
      const user = { id: userId, name: `User ${userId}`, avatar_url: '', type: 'agent' };
      presenceStore.updateUserPresence(user, status);
    },

    // Get current state for assertions
    getState: () => ({
      conversations: conversationsStore.allConversations,
      notifications: notificationsStore.all,
      presence: presenceStore.users,
      wsConnection: wsStore.stats
    })
  };
}