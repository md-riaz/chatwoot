/**
 * WebSocket Client
 * Native WebSocket client replacing ActionCable
 * Features: automatic reconnection, channel subscriptions, heartbeat
 */

import type {
  WebSocketConfig,
  WebSocketMessage,
  MessageHandler,
  UnsubscribeFunction,
  ChannelOptions,
} from './types';
import { getWebSocketStore } from './store.svelte';

const DEFAULT_CONFIG = {
  reconnectAttempts: 10,
  reconnectDelay: 1000, // Start with 1 second
  maxReconnectDelay: 30000, // Max 30 seconds
  heartbeatInterval: 30000, // 30 seconds
  heartbeatTimeout: 5000, // 5 seconds
};

export class WebSocketClient {
  private ws: WebSocket | null = null;
  private config: Required<WebSocketConfig>;
  private channels = new Map<string, Set<MessageHandler>>();
  private heartbeatTimer: number | null = null;
  private heartbeatTimeoutTimer: number | null = null;
  private reconnectTimer: number | null = null;
  private store = getWebSocketStore();
  private isManualDisconnect = false;

  constructor(config: WebSocketConfig) {
    this.config = {
      ...DEFAULT_CONFIG,
      ...config,
    };
  }

  /**
   * Connect to WebSocket server
   */
  connect(): void {
    if (this.ws?.readyState === WebSocket.OPEN) {
      console.warn('WebSocket already connected');
      return;
    }

    this.isManualDisconnect = false;
    this.store.setState('connecting');

    try {
      const url = `${this.config.url}?token=${this.config.token}`;
      this.ws = new WebSocket(url);

      this.ws.onopen = this.handleOpen.bind(this);
      this.ws.onmessage = this.handleMessage.bind(this);
      this.ws.onerror = this.handleError.bind(this);
      this.ws.onclose = this.handleClose.bind(this);
    } catch (error) {
      console.error('WebSocket connection error:', error);
      this.store.setState('failed');
      this.store.setError(
        error instanceof Error ? error.message : 'Unknown error'
      );
      this.scheduleReconnect();
    }
  }

  /**
   * Disconnect from WebSocket server
   */
  disconnect(): void {
    this.isManualDisconnect = true;
    this.clearTimers();

    if (this.ws) {
      this.ws.close();
      this.ws = null;
    }

    this.store.setState('disconnected');
  }

  /**
   * Subscribe to a channel
   * @param channel - Channel identifier (e.g., 'conversations', 'notifications')
   * @param callback - Function to call when message received
   * @param options - Optional channel options
   * @returns Unsubscribe function
   */
  subscribe(
    channel: string,
    callback: MessageHandler,
    options?: ChannelOptions
  ): UnsubscribeFunction {
    if (!this.channels.has(channel)) {
      this.channels.set(channel, new Set());
      // Send subscribe command if connected
      if (this.ws?.readyState === WebSocket.OPEN) {
        this.sendCommand('subscribe', channel);
      }
    }

    this.channels.get(channel)!.add(callback);

    // Call onConnected if already connected
    if (this.store.isConnected && options?.onConnected) {
      options.onConnected();
    }

    // Return unsubscribe function
    return () => this.unsubscribe(channel, callback);
  }

  /**
   * Unsubscribe from a channel
   */
  unsubscribe(channel: string, callback: MessageHandler): void {
    const callbacks = this.channels.get(channel);
    if (callbacks) {
      callbacks.delete(callback);
      // If no more callbacks, unsubscribe from channel
      if (callbacks.size === 0) {
        this.channels.delete(channel);
        if (this.ws?.readyState === WebSocket.OPEN) {
          this.sendCommand('unsubscribe', channel);
        }
      }
    }
  }

  /**
   * Send message to a channel
   */
  send(channel: string, data: any): void {
    if (this.ws?.readyState !== WebSocket.OPEN) {
      console.warn('WebSocket not connected, cannot send message');
      return;
    }

    const message: WebSocketMessage = {
      type: 'message',
      channel,
      data,
    };

    this.ws.send(JSON.stringify(message));
  }

  /**
   * Get current connection state
   */
  get connectionState() {
    return {
      state: this.store.state,
      error: this.store.error,
      reconnectAttempts: this.store.reconnectAttempts,
      isConnected: this.store.isConnected,
      isConnecting: this.store.isConnecting,
      isDisconnected: this.store.isDisconnected,
    };
  }

  // Private methods

  private handleOpen(): void {
    console.log('WebSocket connected');
    this.store.setState('connected');
    this.store.setError(null);

    // Resubscribe to all channels
    this.resubscribeChannels();

    // Start heartbeat
    this.startHeartbeat();
  }

  private handleMessage(event: MessageEvent): void {
    try {
      const message: WebSocketMessage = JSON.parse(event.data);

      // Handle pong response
      if (message.type === 'pong') {
        this.clearHeartbeatTimeout();
        return;
      }

      // Handle welcome message
      if (message.type === 'welcome') {
        console.log('WebSocket welcome received');
        return;
      }

      // Handle confirmation message
      if (message.type === 'confirm_subscription') {
        console.log('Channel subscription confirmed:', message.identifier);
        return;
      }

      // Dispatch to channel subscribers
      if (message.channel) {
        this.dispatchToChannel(message.channel, message.data);
      }
    } catch (error) {
      console.error('Error handling WebSocket message:', error);
    }
  }

  private handleError(event: Event): void {
    console.error('WebSocket error:', event);
    this.store.setError('WebSocket connection error');
  }

  private handleClose(event: CloseEvent): void {
    console.log('WebSocket closed:', event.code, event.reason);
    this.clearTimers();

    if (this.isManualDisconnect) {
      this.store.setState('disconnected');
    } else {
      this.store.setState('reconnecting');
      this.scheduleReconnect();
    }
  }

  private dispatchToChannel(channel: string, data: any): void {
    const callbacks = this.channels.get(channel);
    if (callbacks) {
      callbacks.forEach(callback => {
        try {
          callback(data);
        } catch (error) {
          console.error('Error in channel callback:', error);
        }
      });
    }
  }

  private sendCommand(command: string, channel: string): void {
    if (this.ws?.readyState === WebSocket.OPEN) {
      const message: WebSocketMessage = {
        command,
        identifier: channel,
      };
      this.ws.send(JSON.stringify(message));
    }
  }

  private resubscribeChannels(): void {
    for (const channel of this.channels.keys()) {
      this.sendCommand('subscribe', channel);
    }
  }

  private startHeartbeat(): void {
    this.heartbeatTimer = window.setInterval(() => {
      if (this.ws?.readyState === WebSocket.OPEN) {
        this.ws.send(JSON.stringify({ type: 'ping' }));
        // Set timeout to detect if pong not received
        this.heartbeatTimeoutTimer = window.setTimeout(() => {
          console.warn('Heartbeat timeout, reconnecting...');
          this.ws?.close();
        }, this.config.heartbeatTimeout);
      }
    }, this.config.heartbeatInterval);
  }

  private clearHeartbeatTimeout(): void {
    if (this.heartbeatTimeoutTimer) {
      clearTimeout(this.heartbeatTimeoutTimer);
      this.heartbeatTimeoutTimer = null;
    }
  }

  private scheduleReconnect(): void {
    if (this.isManualDisconnect) return;

    const attempts = this.store.reconnectAttempts;
    if (attempts >= this.config.reconnectAttempts) {
      console.error('Max reconnect attempts reached');
      this.store.setState('failed');
      this.store.setError('Failed to connect after multiple attempts');
      return;
    }

    // Exponential backoff: 1s, 2s, 4s, 8s, 16s, 30s (capped)
    const delay = Math.min(
      this.config.reconnectDelay * Math.pow(2, attempts),
      this.config.maxReconnectDelay
    );

    console.log(
      `Reconnecting in ${delay}ms (attempt ${attempts + 1}/${this.config.reconnectAttempts})`
    );

    this.store.incrementReconnectAttempts();

    this.reconnectTimer = window.setTimeout(() => {
      this.connect();
    }, delay);
  }

  private clearTimers(): void {
    if (this.heartbeatTimer) {
      clearInterval(this.heartbeatTimer);
      this.heartbeatTimer = null;
    }

    if (this.heartbeatTimeoutTimer) {
      clearTimeout(this.heartbeatTimeoutTimer);
      this.heartbeatTimeoutTimer = null;
    }

    if (this.reconnectTimer) {
      clearTimeout(this.reconnectTimer);
      this.reconnectTimer = null;
    }
  }
}

// Singleton instance
let wsClient: WebSocketClient | null = null;

/**
 * Get or create WebSocket client instance
 * @param config - WebSocket configuration (required on first call)
 * @returns WebSocket client instance
 */
export function getWebSocketClient(config?: WebSocketConfig): WebSocketClient {
  if (!wsClient && config) {
    wsClient = new WebSocketClient(config);
  }

  if (!wsClient) {
    throw new Error('WebSocket client not initialized. Provide config on first call.');
  }

  return wsClient;
}

/**
 * Reset WebSocket client (useful for testing)
 */
export function resetWebSocketClient(): void {
  if (wsClient) {
    wsClient.disconnect();
    wsClient = null;
  }
}
