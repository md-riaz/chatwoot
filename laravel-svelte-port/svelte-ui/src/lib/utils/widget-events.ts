/**
 * Widget Event Emitter
 * Simple event emitter for widget communication
 * Ported from Vue mitt implementation
 */

type EventHandler<T = any> = (event: T) => void;
type EventMap = Record<string, EventHandler[]>;

class WidgetEventEmitter {
  private events: EventMap = {};

  /**
   * Register an event handler
   */
  on<T = any>(event: string, handler: EventHandler<T>): void {
    if (!this.events[event]) {
      this.events[event] = [];
    }
    this.events[event].push(handler);
  }

  /**
   * Unregister an event handler
   */
  off<T = any>(event: string, handler: EventHandler<T>): void {
    if (!this.events[event]) return;
    
    const index = this.events[event].indexOf(handler);
    if (index > -1) {
      this.events[event].splice(index, 1);
    }
  }

  /**
   * Emit an event
   */
  emit<T = any>(event: string, data?: T): void {
    if (!this.events[event]) return;
    
    this.events[event].forEach(handler => {
      try {
        handler(data);
      } catch (error) {
        console.error(`Error in event handler for ${event}:`, error);
      }
    });
  }

  /**
   * Register a one-time event handler
   */
  once<T = any>(event: string, handler: EventHandler<T>): void {
    const onceHandler = (data: T) => {
      handler(data);
      this.off(event, onceHandler);
    };
    this.on(event, onceHandler);
  }

  /**
   * Clear all event handlers
   */
  clear(): void {
    this.events = {};
  }

  /**
   * Clear all handlers for a specific event
   */
  clearEvent(event: string): void {
    delete this.events[event];
  }

  /**
   * Get all registered events
   */
  getEvents(): string[] {
    return Object.keys(this.events);
  }

  /**
   * Get handler count for an event
   */
  getHandlerCount(event: string): number {
    return this.events[event]?.length || 0;
  }
}

// Export singleton instance
export const widgetEmitter = new WidgetEventEmitter();