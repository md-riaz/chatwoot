/**
 * Laravel Reverb WebSocket Client
 * Uses Pusher JS for Laravel Reverb compatibility
 */

import type { Channel, PresenceChannel } from 'pusher-js';
import Pusher from 'pusher-js';

export interface ReverbConfig {
  host: string;
  port: number;
  key: string;
  cluster?: string;
  forceTLS?: boolean;
  authEndpoint?: string;
  auth?: {
    headers: {
      Authorization: string;
    };
  };
}

export interface ChannelSubscription {
  channel: Channel | PresenceChannel;
  unsubscribe: () => void;
}

export class ReverbClient {
  private pusher: Pusher | null = null;
  private subscriptions = new Map<string, ChannelSubscription>();
  private config: ReverbConfig;

  constructor(config: ReverbConfig) {
    this.config = config;
  }

  /**
   * Connect to Laravel Reverb
   */
  connect(): void {
    if (this.pusher) {
      console.warn('Reverb client already connected');
      return;
    }

    this.pusher = new Pusher(this.config.key, {
      wsHost: this.config.host,
      wsPort: this.config.port,
      wssPort: this.config.port,
      forceTLS: this.config.forceTLS || false,
      enabledTransports: ['ws', 'wss'],
      cluster: this.config.cluster || '',
      authEndpoint: this.config.authEndpoint,
      auth: this.config.auth,
    });

    // Connection event handlers
    this.pusher.connection.bind('connected', () => {
      console.log('Reverb connected');
    });

    this.pusher.connection.bind('disconnected', () => {
      console.log('Reverb disconnected');
    });

    this.pusher.connection.bind('error', (error: any) => {
      console.error('Reverb connection error:', error);
    });
  }

  /**
   * Disconnect from Laravel Reverb
   */
  disconnect(): void {
    if (this.pusher) {
      // Unsubscribe from all channels
      this.subscriptions.forEach(({ unsubscribe }) => unsubscribe());
      this.subscriptions.clear();

      this.pusher.disconnect();
      this.pusher = null;
    }
  }

  /**
   * Subscribe to a public channel
   */
  subscribe(channelName: string, eventName: string, callback: (data: any) => void): () => void {
    if (!this.pusher) {
      throw new Error('Reverb client not connected');
    }

    const channel = this.pusher.subscribe(channelName);
    channel.bind(eventName, callback);

    const unsubscribe = () => {
      channel.unbind(eventName, callback);
      this.pusher?.unsubscribe(channelName);
      this.subscriptions.delete(`${channelName}:${eventName}`);
    };

    this.subscriptions.set(`${channelName}:${eventName}`, {
      channel,
      unsubscribe,
    });

    return unsubscribe;
  }

  /**
   * Subscribe to a private channel
   */
  subscribePrivate(channelName: string, eventName: string, callback: (data: any) => void): () => void {
    if (!this.pusher) {
      throw new Error('Reverb client not connected');
    }

    const channel = this.pusher.subscribe(`private-${channelName}`);
    channel.bind(eventName, callback);

    const unsubscribe = () => {
      channel.unbind(eventName, callback);
      this.pusher?.unsubscribe(`private-${channelName}`);
      this.subscriptions.delete(`private-${channelName}:${eventName}`);
    };

    this.subscriptions.set(`private-${channelName}:${eventName}`, {
      channel,
      unsubscribe,
    });

    return unsubscribe;
  }

  /**
   * Subscribe to a presence channel
   */
  subscribePresence(
    channelName: string,
    callbacks: {
      onMessage?: (eventName: string, data: any) => void;
      onMemberAdded?: (member: any) => void;
      onMemberRemoved?: (member: any) => void;
    }
  ): () => void {
    if (!this.pusher) {
      throw new Error('Reverb client not connected');
    }

    const channel = this.pusher.subscribe(`presence-${channelName}`) as PresenceChannel;

    // Bind event handlers
    if (callbacks.onMessage) {
      channel.bind_global(callbacks.onMessage);
    }

    if (callbacks.onMemberAdded) {
      channel.bind('pusher:member_added', callbacks.onMemberAdded);
    }

    if (callbacks.onMemberRemoved) {
      channel.bind('pusher:member_removed', callbacks.onMemberRemoved);
    }

    const unsubscribe = () => {
      if (callbacks.onMessage) {
        channel.unbind_global(callbacks.onMessage);
      }
      if (callbacks.onMemberAdded) {
        channel.unbind('pusher:member_added', callbacks.onMemberAdded);
      }
      if (callbacks.onMemberRemoved) {
        channel.unbind('pusher:member_removed', callbacks.onMemberRemoved);
      }
      this.pusher?.unsubscribe(`presence-${channelName}`);
      this.subscriptions.delete(`presence-${channelName}`);
    };

    this.subscriptions.set(`presence-${channelName}`, {
      channel,
      unsubscribe,
    });

    return unsubscribe;
  }

  /**
   * Get connection state
   */
  get connectionState() {
    return this.pusher?.connection.state || 'disconnected';
  }

  /**
   * Check if connected
   */
  get isConnected() {
    return this.pusher?.connection.state === 'connected';
  }
}

// Singleton instance
let reverbClient: ReverbClient | null = null;

/**
 * Get or create Reverb client instance
 */
export function getReverbClient(config?: ReverbConfig): ReverbClient {
  if (!reverbClient && config) {
    reverbClient = new ReverbClient(config);
  }

  if (!reverbClient) {
    throw new Error('Reverb client not initialized. Provide config on first call.');
  }

  return reverbClient;
}

/**
 * Reset Reverb client (useful for testing)
 */
export function resetReverbClient(): void {
  if (reverbClient) {
    reverbClient.disconnect();
    reverbClient = null;
  }
}