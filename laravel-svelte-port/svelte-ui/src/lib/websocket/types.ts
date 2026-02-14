/**
 * WebSocket Types
 * Type definitions for WebSocket client and channel management
 */

import type { Agent } from '$lib/api/agents';
import type { Team } from '$lib/api/teams';

export type PresenceStatus = 'online' | 'offline' | 'busy' | 'away';

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

export interface RealtimeEventEnvelope {
  account_id?: number;
  user_id?: number;
}

export interface AccountPresencePayload extends RealtimeEventEnvelope {
  status: PresenceStatus;
  metadata?: Record<string, unknown>;
  user: {
    id: number;
    name: string;
    avatar_url?: string;
    avatarUrl?: string;
    type?: 'agent' | 'contact';
  };
}

export interface AssigneeChangedPayload extends RealtimeEventEnvelope {
  conversation?: { id: number } & Record<string, unknown>;
  previous_assignee?: Agent | null;
  new_assignee?: Agent | null;
}

export interface TeamChangedPayload extends RealtimeEventEnvelope {
  conversation?: { id: number } & Record<string, unknown>;
  previous_team?: Team | null;
  new_team?: Team | null;
}

export type MessageHandler = (data: any) => void;
export type UnsubscribeFunction = () => void;
