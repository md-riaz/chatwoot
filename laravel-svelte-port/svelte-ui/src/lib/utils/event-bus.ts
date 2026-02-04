/**
 * Event Bus System
 * Replaces Vue's mitt/emitter for cross-component communication
 * Provides same functionality as Vue ActionCable event system
 */

export type EventHandler<T = any> = (data: T) => void;

class EventBus {
  private listeners = new Map<string, Set<EventHandler>>();

  /**
   * Emit an event with optional data
   */
  emit<T = any>(event: string, data?: T): void {
    const eventListeners = this.listeners.get(event);
    if (eventListeners) {
      eventListeners.forEach(listener => {
        try {
          listener(data);
        } catch (error) {
          console.error(`Error in event listener for ${event}:`, error);
        }
      });
    }
  }

  /**
   * Subscribe to an event
   * Returns unsubscribe function
   */
  on<T = any>(event: string, listener: EventHandler<T>): () => void {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, new Set());
    }
    
    this.listeners.get(event)!.add(listener);

    // Return unsubscribe function
    return () => {
      const listeners = this.listeners.get(event);
      if (listeners) {
        listeners.delete(listener);
        if (listeners.size === 0) {
          this.listeners.delete(event);
        }
      }
    };
  }

  /**
   * Subscribe to an event only once
   */
  once<T = any>(event: string, listener: EventHandler<T>): () => void {
    const unsubscribe = this.on(event, (data: T) => {
      unsubscribe();
      listener(data);
    });
    return unsubscribe;
  }

  /**
   * Remove all listeners for an event
   */
  off(event: string): void {
    this.listeners.delete(event);
  }

  /**
   * Remove all listeners
   */
  clear(): void {
    this.listeners.clear();
  }

  /**
   * Get list of events with listeners
   */
  getEvents(): string[] {
    return Array.from(this.listeners.keys());
  }

  /**
   * Get number of listeners for an event
   */
  getListenerCount(event: string): number {
    return this.listeners.get(event)?.size || 0;
  }
}

// Singleton instance
export const eventBus = new EventBus();

// Bus event constants (matching Vue implementation)
export const BUS_EVENTS = {
  WEBSOCKET_RECONNECT: 'websocket:reconnect',
  WEBSOCKET_DISCONNECT: 'websocket:disconnect',
  FETCH_CONVERSATION_STATS: 'fetch_conversation_stats',
  CONVERSATION_MENTIONED: 'conversation:mentioned',
  AGENT_MESSAGE_RECEIVED: 'agent:message:received',
  CONTACT_MERGED: 'contact:merged',
  CACHE_INVALIDATED: 'cache:invalidated',
} as const;

export type BusEventType = typeof BUS_EVENTS[keyof typeof BUS_EVENTS];