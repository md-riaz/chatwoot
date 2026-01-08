/**
 * Iframe Communication Utilities
 * 
 * Utilities for communication between widget (iframe) and parent window.
 */

export interface WidgetEvent {
  event: string;
  data?: any;
}

/**
 * Send event to parent window
 */
export function sendEventToParent(event: string, data?: any): void {
  if (typeof window === 'undefined') return;

  if (window.parent && window.parent !== window) {
    window.parent.postMessage(
      {
        event: `chatwoot:${event}`,
        data,
      },
      '*'
    );
  }
}

/**
 * Listen to events from parent window
 */
export function listenToParentEvents(
  callback: (event: WidgetEvent) => void
): () => void {
  if (typeof window === 'undefined') return () => {};

  const handler = (event: MessageEvent) => {
    if (event.data?.event?.startsWith('chatwoot:')) {
      callback({
        event: event.data.event.replace('chatwoot:', ''),
        data: event.data.data,
      });
    }
  };

  window.addEventListener('message', handler);

  // Return cleanup function
  return () => {
    window.removeEventListener('message', handler);
  };
}

/**
 * Notify parent that widget is ready
 */
export function notifyWidgetReady(): void {
  sendEventToParent('widget:ready');
}

/**
 * Notify parent that widget opened
 */
export function notifyWidgetOpened(): void {
  sendEventToParent('widget:opened');
}

/**
 * Notify parent that widget closed
 */
export function notifyWidgetClosed(): void {
  sendEventToParent('widget:closed');
}

/**
 * Notify parent of unread count change
 */
export function notifyUnreadCountChanged(count: number): void {
  sendEventToParent('unread_count_changed', { count });
}

/**
 * Notify parent of conversation created
 */
export function notifyConversationCreated(conversationId: number): void {
  sendEventToParent('conversation:created', { conversationId });
}

/**
 * Notify parent of message sent
 */
export function notifyMessageSent(messageId: number): void {
  sendEventToParent('message:sent', { messageId });
}

/**
 * Get URL parameters from parent window
 */
export function getUrlParams(): Record<string, string> {
  if (typeof window === 'undefined') return {};

  const params: Record<string, string> = {};
  const searchParams = new URLSearchParams(window.location.search);

  searchParams.forEach((value, key) => {
    params[key] = value;
  });

  return params;
}
