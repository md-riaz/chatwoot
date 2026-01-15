/**
 * Widget WebSocket Client
 * 
 * WebSocket client for widget real-time communication.
 * Extends base WebSocket client with widget-specific functionality.
 */

import { widgetConversationStore } from '../stores/conversation.svelte';
import { widgetAgentStore } from '../stores/agent.svelte';
import { widgetConfigStore } from '../stores/config.svelte';
import type { WebSocketEventHandler } from './types';

type ConnectionState =
  | 'disconnected'
  | 'connecting'
  | 'connected'
  | 'reconnecting'
  | 'failed';

class WidgetWebSocketClient {
  private ws: WebSocket | null = null;
  private channels = new Map<string, Set<WebSocketEventHandler>>();
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 10;
  private heartbeatInterval: number | null = null;
  private reconnectTimeout: number | null = null;

  state = $state<ConnectionState>('disconnected');

  constructor(
    private websiteToken: string,
    private baseUrl?: string
  ) {
    this.baseUrl = baseUrl || import.meta.env.VITE_WS_URL || 'ws://localhost:3000/cable';
  }

  /**
   * Connect to WebSocket server
   */
  connect() {
    if (this.ws?.readyState === WebSocket.OPEN) return;

    this.state = 'connecting';
    const url = `${this.baseUrl}?website_token=${this.websiteToken}`;

    try {
      this.ws = new WebSocket(url);

      this.ws.onopen = () => {
        console.log('[Widget WS] Connected');
        this.state = 'connected';
        this.reconnectAttempts = 0;
        this.startHeartbeat();
        this.resubscribeChannels();
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
    this.channels.clear();
    this.state = 'disconnected';
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
   * Subscribe to conversation channel
   */
  subscribeToConversation(conversationId: number) {
    const channel = `conversation_${conversationId}`;

    return this.subscribe(channel, (event) => {
      switch (event.type) {
        case 'message.created':
          this.handleMessageCreated(event.data);
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

  private handleMessageCreated(data: any) {
    widgetConversationStore.addMessage(data);

    // Increment unread count if widget is closed and message is from agent
    if (!widgetConfigStore.isWidgetOpen && data.messageType === 0) {
      widgetConfigStore.incrementUnread();
    }
  }

  private handleConversationStatusChanged(data: any) {
    widgetConversationStore.updateStatus(data.status);
  }

  private handleTypingOn(data: any) {
    if (data.agentId) {
      widgetAgentStore.setTyping(data.agentId, true);
    }
  }

  private handleTypingOff(data: any) {
    if (data.agentId) {
      widgetAgentStore.setTyping(data.agentId, false);
    }
  }

  private handlePresenceUpdate(data: any) {
    if (data.agentId && data.status) {
      widgetAgentStore.updateAgentStatus(data.agentId, data.status);
    }
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
  websiteToken: string,
  baseUrl?: string
): WidgetWebSocketClient {
  if (!wsClient || wsClient['websiteToken'] !== websiteToken) {
    if (wsClient) {
      wsClient.disconnect();
    }
    wsClient = new WidgetWebSocketClient(websiteToken, baseUrl);
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
