/**
 * WebSocket Types
 * Type definitions for WebSocket client and channel management
 */

export type ConnectionState =
  | 'disconnected'
  | 'connecting'
  | 'connected'
  | 'reconnecting'
  | 'failed';

export interface WebSocketMessage {
  type: string;
  channel?: string;
  data?: any;
  identifier?: string;
  command?: string;
}

export interface ChannelSubscription {
  channel: string;
  callbacks: Set<(data: any) => void>;
}

export interface WebSocketConfig {
  url: string;
  token: string;
  reconnectAttempts?: number;
  reconnectDelay?: number;
  maxReconnectDelay?: number;
  heartbeatInterval?: number;
  heartbeatTimeout?: number;
}

export interface WebSocketClientState {
  state: ConnectionState;
  error: string | null;
  reconnectAttempts: number;
  lastConnectedAt: Date | null;
  lastDisconnectedAt: Date | null;
}

export interface ChannelOptions {
  onConnected?: () => void;
  onDisconnected?: () => void;
  onError?: (error: Error) => void;
}

export type MessageHandler = (data: any) => void;
export type UnsubscribeFunction = () => void;
