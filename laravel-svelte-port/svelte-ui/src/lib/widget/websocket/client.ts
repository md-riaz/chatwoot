/**
 * Widget WebSocket Client
 * 
 * WebSocket client for widget real-time communication.
 * Implements Vue ActionCable parity with contact authentication and message filtering.
 */

import { widgetConversationStore } from '../stores/conversation.svelte';
import { widgetAgentStore } from '../stores/agent.svelte';
import { widgetConfigStore } from '../stores/config.svelte';
import { eventBus, BUS_EVENTS } from '$lib/utils/event-bus';
import { playNewMessageNotificationInWidget } from '$lib/utils/audio-notifications';
import type { WebSocketEventHandler } from './types';

type ConnectionState =
  | 'disconnected'
  | 'connecting'
  | 'connected'
  | 'reconnecting'
  | 'failed';

/**
 * Widget WebSocket Connector with Vue ActionCable parity
 * Implements contact authentication, message filtering, and reconnection logic
 */
class WidgetWebSocketClient {
  private ws: WebSocket | null = null;
  private channels = new Map<string, Set<WebSocketEventHandler>>();
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 10;
  private heartbeatInterval: number | null = null;
  private reconnectTimeout: number | null = null;
  private presenceInterval: number | null = null;
  private lastMessageId: number | null = null;
  private activeConversationId: number | null = null;
  private readonly PRESENCE_INTERVAL = 20000; // 20 seconds (matching Vue)
  
  // Typing timeout management (matching Vue widget implementation)
  private typingTimeout: number | null = null;
  private readonly TYPING_TIMEOUT = 30000; // 30 seconds (matching Vue)

  state = $state<ConnectionState>('disconnected');

  constructor(
    private contactToken: string,
    private baseUrl?: string
  ) {
    this.baseUrl = baseUrl || import.meta.env.VITE_WS_URL || 'ws://localhost:3000/cable';
  }

  /**
   * Connect to WebSocket server with contact authentication
   */
  connect() {
    if (this.ws?.readyState === WebSocket.OPEN) return;

    this.state = 'connecting';
    
    // Use contact token for authentication (matching Vue widget implementation)
    const url = `${this.baseUrl}?contact_token=${this.contactToken}`;

    try {
      this.ws = new WebSocket(url);

      this.ws.onopen = () => {
        console.log('[Widget WS] Connected');
        this.state = 'connected';
        this.reconnectAttempts = 0;
        this.startHeartbeat();
        this.startPresenceUpdates();
        this.resubscribeChannels();
        
        // Emit reconnect event (matching Vue)
        eventBus.emit(BUS_EVENTS.WEBSOCKET_RECONNECT);
      };

      this.ws.onmessage = (event) => {
        try {
          const message = JSON.parse(event.data);
          this.handleMessage(message);
        } catch (err) {
          console.error('[Widget WS] Failed to parse message:', err);
        }
      };

      this.ws.onerror = (error) => {
        console.error('[Widget WS] Error:', error);
      };

      this.ws.onclose = () => {
        console.log('[Widget WS] Disconnected');
        this.state = 'disconnected';
        this.stopHeartbeat();
        this.stopPresenceUpdates();
        
        // Set last message ID for sync (matching Vue onDisconnected)
        this.setLastMessageId();
        
        // Emit disconnect event
        eventBus.emit(BUS_EVENTS.WEBSOCKET_DISCONNECT);
        
        this.reconnect();
      };
    } catch (err) {
      console.error('[Widget WS] Failed to connect:', err);
      this.state = 'failed';
    }
  }

  /**
   * Disconnect from WebSocket server
   */
  disconnect() {
    if (this.reconnectTimeout) {
      clearTimeout(this.reconnectTimeout);
      this.reconnectTimeout = null;
    }

    if (this.ws) {
      this.ws.close();
      this.ws = null;
    }

    this.stopHeartbeat();
    this.stopPresenceUpdates();
    this.clearTypingTimeout(); // Clear typing timeout on disconnect
    this.channels.clear();
    this.state = 'disconnected';
  }

  /**
   * Set active conversation ID for message filtering
   */
  setActiveConversation(conversationId: number | null): void {
    this.activeConversationId = conversationId;
  }

  /**
   * Check if message is in active conversation (matching Vue implementation)
   */
  private isMessageInActiveConversation(message: any): boolean {
    const { conversation_id: conversationId } = message;
    return this.activeConversationId && conversationId !== this.activeConversationId;
  }

  /**
   * Set last message ID for sync on reconnect (matching Vue)
   */
  private setLastMessageId(): void {
    // Get the last message ID from widget conversation store
    const lastMessage = widgetConversationStore.getLastMessage();
    if (lastMessage) {
      this.lastMessageId = lastMessage.id;
      console.log('[Widget WS] Set last message ID for sync:', this.lastMessageId);
    }
  }

  /**
   * Sync latest messages on reconnect (matching Vue)
   */
  private async syncLatestMessages(): Promise<void> {
    if (!this.lastMessageId) return;

    try {
      // Import the widget messages API
      const { getWidgetMessagesSince } = await import('../api/messages');
      
      const newMessages = await getWidgetMessagesSince(this.lastMessageId);
      
      // Add each new message to the widget conversation store
      newMessages.forEach(message => {
        widgetConversationStore.addMessage(message);
      });
      
      console.log(`[Widget WS] Synced ${newMessages.length} messages since ID ${this.lastMessageId}`);
    } catch (error) {
      console.error('[Widget WS] Failed to sync messages:', error);
    }
  }

  /**
   * Start presence updates (matching Vue 20-second interval)
   */
  private startPresenceUpdates(): void {
    if (this.presenceInterval) return;

    this.presenceInterval = window.setInterval(() => {
      if (this.ws?.readyState === WebSocket.OPEN) {
        // Update presence on server
        this.send('presence', { type: 'update_presence' });
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
   * Clear typing timeout (matching Vue widget implementation)
   */
  private clearTypingTimeout(): void {
    if (this.typingTimeout) {
      clearTimeout(this.typingTimeout);
      this.typingTimeout = null;
    }
  }

  /**
   * Set typing timeout (matching Vue widget implementation)
   */
  private setTypingTimeout(agentId: number): void {
    // Set timeout to automatically turn off typing after 30 seconds
    this.typingTimeout = window.setTimeout(() => {
      console.log('[Widget WS] Auto-clearing typing after 30 seconds');
      widgetAgentStore.setTyping(agentId, false);
      this.typingTimeout = null;
    }, this.TYPING_TIMEOUT);
  }

  /**
   * Subscribe to a channel
   */
  subscribe(channel: string, callback: WebSocketEventHandler) {
    if (!this.channels.has(channel)) {
      this.channels.set(channel, new Set());
      this.sendCommand('subscribe', channel);
    }

    this.channels.get(channel)!.add(callback);

    // Return unsubscribe function
    return () => {
      const callbacks = this.channels.get(channel);
      if (callbacks) {
        callbacks.delete(callback);
        if (callbacks.size === 0) {
          this.sendCommand('unsubscribe', channel);
          this.channels.delete(channel);
        }
      }
    };
  }

  /**
   * Subscribe to conversation channel with message filtering
   */
  subscribeToConversation(conversationId: number) {
    const channel = `conversation_${conversationId}`;
    this.setActiveConversation(conversationId);

    return this.subscribe(channel, (event) => {
      switch (event.type) {
        case 'message.created':
          this.handleMessageCreated(event.data);
          break;
        case 'message.updated':
          this.handleMessageUpdated(event.data);
          break;
        case 'conversation.status_changed':
          this.handleConversationStatusChanged(event.data);
          break;
        case 'conversation.typing_on':
          this.handleTypingOn(event.data);
          break;
        case 'conversation.typing_off':
          this.handleTypingOff(event.data);
          break;
        case 'presence.update':
          this.handlePresenceUpdate(event.data);
          break;
        case 'contact.merged':
          this.handleContactMerged(event.data);
          break;
      }
    });
  }

  /**
   * Send typing status
   */
  sendTypingStatus(conversationId: number, isTyping: boolean) {
    this.send(`conversation_${conversationId}`, {
      type: isTyping ? 'typing_on' : 'typing_off',
    });
  }

  /**
   * Send message to channel
   */
  send(channel: string, data: any) {
    if (this.ws?.readyState === WebSocket.OPEN) {
      this.ws.send(
        JSON.stringify({
          command: 'message',
          identifier: JSON.stringify({ channel }),
          data: JSON.stringify(data),
        })
      );
    }
  }

  // Private methods

  private handleMessage(message: any) {
    const { identifier, message: data } = message;

    if (identifier) {
      try {
        const channelInfo = JSON.parse(identifier);
        const channel = channelInfo.channel;
        const callbacks = this.channels.get(channel);

        if (callbacks && data) {
          const eventData = typeof data === 'string' ? JSON.parse(data) : data;
          callbacks.forEach((callback) => callback(eventData));
        }
      } catch (err) {
        console.error('[Widget WS] Failed to handle message:', err);
      }
    }

    // Handle ping/pong
    if (message.type === 'ping') {
      this.sendPong();
    }
  }

  /**
   * Handle message created with filtering (matching Vue implementation)
   */
  private handleMessageCreated(data: any) {
    // Filter messages from other conversations (matching Vue)
    if (this.isMessageInActiveConversation(data)) {
      return;
    }

    widgetConversationStore.addMessage(data);

    // Increment unread count if widget is closed and message is from agent (matching Vue)
    if (!widgetConfigStore.isWidgetOpen && data.messageType === 0) {
      widgetConfigStore.incrementUnread();
    }

    // Play notification sound for agent messages (matching Vue)
    if (data.sender_type === 'User') {
      playNewMessageNotificationInWidget();
    }

    // Emit event for external integrations (matching Vue IFrame helper)
    eventBus.emit(BUS_EVENTS.AGENT_MESSAGE_RECEIVED, data);
  }

  /**
   * Handle message updated with filtering (matching Vue implementation)
   */
  private handleMessageUpdated(data: any) {
    // Filter messages from other conversations
    if (this.isMessageInActiveConversation(data)) {
      return;
    }

    widgetConversationStore.updateMessage(data);
  }

  private handleConversationStatusChanged(data: any) {
    widgetConversationStore.updateStatus(data.status);
    
    // Reset campaign if conversation is resolved (matching Vue)
    if (data.status === 'resolved') {
      // widgetCampaignStore.resetCampaign(); // When implemented
    }
  }

  /**
   * Handle typing with private message filtering (matching Vue implementation)
   */
  private handleTypingOn(data: any) {
    // Filter typing from other conversations and private messages (matching Vue)
    const isUserTypingOnAnotherConversation =
      data.conversation && data.conversation.id !== this.activeConversationId;

    if (isUserTypingOnAnotherConversation || data.is_private) {
      return;
    }

    if (data.agentId) {
      // Clear existing timeout and set typing
      this.clearTypingTimeout();
      widgetAgentStore.setTyping(data.agentId, true);
      
      // Set 30-second auto-timeout (matching Vue implementation)
      this.setTypingTimeout(data.agentId);
    }
  }

  private handleTypingOff(data: any) {
    // Filter typing from other conversations and private messages
    const isUserTypingOnAnotherConversation =
      data.conversation && data.conversation.id !== this.activeConversationId;

    if (isUserTypingOnAnotherConversation || data.is_private) {
      return;
    }

    if (data.agentId) {
      // Clear timeout and stop typing
      this.clearTypingTimeout();
      widgetAgentStore.setTyping(data.agentId, false);
    }
  }

  private handlePresenceUpdate(data: any) {
    if (data.agentId && data.status) {
      widgetAgentStore.updateAgentStatus(data.agentId, data.status);
    }
  }

  /**
   * Handle contact merge (matching Vue implementation)
   */
  private handleContactMerged(data: any) {
    const { pubsub_token: pubsubToken } = data;
    if (pubsubToken) {
      // Refresh connector with new token (matching Vue)
      this.refreshConnector(pubsubToken);
    }
  }

  /**
   * Refresh connector with new token (matching Vue static method)
   */
  private refreshConnector(newToken: string): void {
    this.disconnect();
    this.contactToken = newToken;
    this.connect();
  }

  private reconnect() {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.error('[Widget WS] Max reconnection attempts reached');
      this.state = 'failed';
      return;
    }

    this.state = 'reconnecting';
    const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);
    this.reconnectAttempts++;

    console.log(`[Widget WS] Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts})`);

    this.reconnectTimeout = window.setTimeout(() => {
      this.connect();
      
      // Sync messages on reconnect (matching Vue onReconnect)
      this.syncLatestMessages();
    }, delay);
  }

  private resubscribeChannels() {
    for (const channel of this.channels.keys()) {
      this.sendCommand('subscribe', channel);
    }
  }

  private sendCommand(command: string, channel: string) {
    if (this.ws?.readyState === WebSocket.OPEN) {
      this.ws.send(
        JSON.stringify({
          command,
          identifier: JSON.stringify({ channel }),
        })
      );
    }
  }

  private startHeartbeat() {
    this.heartbeatInterval = window.setInterval(() => {
      if (this.ws?.readyState === WebSocket.OPEN) {
        this.ws.send(JSON.stringify({ type: 'ping' }));
      }
    }, 30000);
  }

  private stopHeartbeat() {
    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval);
      this.heartbeatInterval = null;
    }
  }

  private sendPong() {
    if (this.ws?.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify({ type: 'pong' }));
    }
  }
}

// Singleton instance
let wsClient: WidgetWebSocketClient | null = null;

/**
 * Create or get Widget WebSocket client
 */
export function createWidgetWebSocket(
  contactToken: string,
  baseUrl?: string
): WidgetWebSocketClient {
  if (!wsClient || wsClient['contactToken'] !== contactToken) {
    if (wsClient) {
      wsClient.disconnect();
    }
    wsClient = new WidgetWebSocketClient(contactToken, baseUrl);
  }
  return wsClient;
}

/**
 * Get current WebSocket client instance
 */
export function getWidgetWebSocket(): WidgetWebSocketClient | null {
  return wsClient;
}

export { WidgetWebSocketClient };
