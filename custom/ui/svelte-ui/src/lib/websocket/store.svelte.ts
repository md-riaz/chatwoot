/**
 * WebSocket Connection State Store
 * Manages WebSocket connection state using Svelte 5 runes
 */

import type { ConnectionState } from './types';

interface ConnectionStore {
  state: ConnectionState;
  error: string | null;
  reconnectAttempts: number;
  lastConnectedAt: Date | null;
  lastDisconnectedAt: Date | null;
}

// Create reactive connection state
let connectionState = $state<ConnectionStore>({
  state: 'disconnected',
  error: null,
  reconnectAttempts: 0,
  lastConnectedAt: null,
  lastDisconnectedAt: null,
});

// Derived computed values
const isConnected = $derived(connectionState.state === 'connected');
const isConnecting = $derived(
  connectionState.state === 'connecting' ||
    connectionState.state === 'reconnecting'
);
const isDisconnected = $derived(
  connectionState.state === 'disconnected' || connectionState.state === 'failed'
);
const hasError = $derived(connectionState.error !== null);

export function createWebSocketStore() {
  return {
    // Getters - return reactive values
    get state() {
      return connectionState.state;
    },
    get error() {
      return connectionState.error;
    },
    get reconnectAttempts() {
      return connectionState.reconnectAttempts;
    },
    get lastConnectedAt() {
      return connectionState.lastConnectedAt;
    },
    get lastDisconnectedAt() {
      return connectionState.lastDisconnectedAt;
    },
    get isConnected() {
      return isConnected;
    },
    get isConnecting() {
      return isConnecting;
    },
    get isDisconnected() {
      return isDisconnected;
    },
    get hasError() {
      return hasError;
    },

    // Setters - update reactive state
    setState(newState: ConnectionState) {
      connectionState.state = newState;
      if (newState === 'connected') {
        connectionState.lastConnectedAt = new Date();
        connectionState.reconnectAttempts = 0;
        connectionState.error = null;
      } else if (newState === 'disconnected' || newState === 'failed') {
        connectionState.lastDisconnectedAt = new Date();
      }
    },

    setError(error: string | null) {
      connectionState.error = error;
    },

    incrementReconnectAttempts() {
      connectionState.reconnectAttempts++;
    },

    resetReconnectAttempts() {
      connectionState.reconnectAttempts = 0;
    },

    reset() {
      connectionState = {
        state: 'disconnected',
        error: null,
        reconnectAttempts: 0,
        lastConnectedAt: null,
        lastDisconnectedAt: null,
      };
    },
  };
}

// Singleton instance
let wsStore: ReturnType<typeof createWebSocketStore> | null = null;

export function getWebSocketStore() {
  if (!wsStore) {
    wsStore = createWebSocketStore();
  }
  return wsStore;
}
