/**
 * WebSocket Store using Svelte 5 runes
 * Manages WebSocket connection state and statistics
 */

type ConnectionState = 'disconnected' | 'connecting' | 'connected' | 'reconnecting' | 'failed';

interface WebSocketState {
  connectionState: ConnectionState;
  error: string | null;
  reconnectAttempts: number;
  maxReconnectAttempts: number;
  lastConnectedAt: string | null;
  lastDisconnectedAt: string | null;
  subscriptionsCount: number;
  isReconnecting: boolean;
}

class WebSocketStore {
  private state = $state<WebSocketState>({
    connectionState: 'disconnected',
    error: null,
    reconnectAttempts: 0,
    maxReconnectAttempts: 5,
    lastConnectedAt: null,
    lastDisconnectedAt: null,
    subscriptionsCount: 0,
    isReconnecting: false
  });

  // Reactive getters
  get connectionState() {
    return this.state.connectionState;
  }

  get error() {
    return this.state.error;
  }

  get reconnectAttempts() {
    return this.state.reconnectAttempts;
  }

  get maxReconnectAttempts() {
    return this.state.maxReconnectAttempts;
  }

  get lastConnectedAt() {
    return this.state.lastConnectedAt;
  }

  get lastDisconnectedAt() {
    return this.state.lastDisconnectedAt;
  }

  get subscriptionsCount() {
    return this.state.subscriptionsCount;
  }

  get isReconnecting() {
    return this.state.isReconnecting;
  }

  // Computed properties
  get isConnected() {
    return this.state.connectionState === 'connected';
  }

  get isConnecting() {
    return this.state.connectionState === 'connecting';
  }

  get isDisconnected() {
    return this.state.connectionState === 'disconnected';
  }

  get isFailed() {
    return this.state.connectionState === 'failed';
  }

  get canReconnect() {
    return this.state.reconnectAttempts < this.state.maxReconnectAttempts;
  }

  get connectionStatus() {
    switch (this.state.connectionState) {
      case 'connected':
        return 'Connected';
      case 'connecting':
        return 'Connecting...';
      case 'reconnecting':
        return `Reconnecting... (${this.state.reconnectAttempts}/${this.state.maxReconnectAttempts})`;
      case 'failed':
        return 'Connection failed';
      case 'disconnected':
      default:
        return 'Disconnected';
    }
  }

  get connectionDuration() {
    if (!this.state.lastConnectedAt || !this.isConnected) {
      return null;
    }
    
    const connectedAt = new Date(this.state.lastConnectedAt);
    const now = new Date();
    const duration = now.getTime() - connectedAt.getTime();
    
    const seconds = Math.floor(duration / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    
    if (hours > 0) {
      return `${hours}h ${minutes % 60}m`;
    } else if (minutes > 0) {
      return `${minutes}m ${seconds % 60}s`;
    } else {
      return `${seconds}s`;
    }
  }

  // Actions
  setState(state: ConnectionState): void {
    const previousState = this.state.connectionState;
    this.state.connectionState = state;
    
    // Update timestamps
    if (state === 'connected' && previousState !== 'connected') {
      this.state.lastConnectedAt = new Date().toISOString();
      this.state.reconnectAttempts = 0;
      this.state.isReconnecting = false;
      this.clearError();
    } else if (state === 'disconnected' && previousState === 'connected') {
      this.state.lastDisconnectedAt = new Date().toISOString();
    } else if (state === 'reconnecting') {
      this.state.isReconnecting = true;
    } else if (state === 'failed') {
      this.state.isReconnecting = false;
    }
  }

  setError(error: string): void {
    this.state.error = error;
  }

  clearError(): void {
    this.state.error = null;
  }

  incrementReconnectAttempts(): void {
    this.state.reconnectAttempts += 1;
  }

  resetReconnectAttempts(): void {
    this.state.reconnectAttempts = 0;
  }

  setMaxReconnectAttempts(max: number): void {
    this.state.maxReconnectAttempts = max;
  }

  setSubscriptionsCount(count: number): void {
    this.state.subscriptionsCount = count;
  }

  incrementSubscriptions(): void {
    this.state.subscriptionsCount += 1;
  }

  decrementSubscriptions(): void {
    this.state.subscriptionsCount = Math.max(0, this.state.subscriptionsCount - 1);
  }

  // Statistics
  get stats() {
    return {
      connectionState: this.state.connectionState,
      isConnected: this.isConnected,
      reconnectAttempts: this.state.reconnectAttempts,
      maxReconnectAttempts: this.state.maxReconnectAttempts,
      subscriptionsCount: this.state.subscriptionsCount,
      lastConnectedAt: this.state.lastConnectedAt,
      lastDisconnectedAt: this.state.lastDisconnectedAt,
      connectionDuration: this.connectionDuration,
      hasError: !!this.state.error,
      error: this.state.error
    };
  }

  // Reset store
  reset(): void {
    this.state = {
      connectionState: 'disconnected',
      error: null,
      reconnectAttempts: 0,
      maxReconnectAttempts: 5,
      lastConnectedAt: null,
      lastDisconnectedAt: null,
      subscriptionsCount: 0,
      isReconnecting: false
    };
  }
}

// Singleton instance
let webSocketStore: WebSocketStore | null = null;

/**
 * Get WebSocket store instance
 */
export function getWebSocketStore(): WebSocketStore {
  if (!webSocketStore) {
    webSocketStore = new WebSocketStore();
  }
  return webSocketStore;
}

/**
 * Reset WebSocket store (useful for testing)
 */
export function resetWebSocketStore(): void {
  webSocketStore = null;
}