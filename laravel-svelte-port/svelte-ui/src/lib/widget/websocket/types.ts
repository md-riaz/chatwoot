/**
 * Widget WebSocket Types
 */

export interface WidgetWebSocketEvent {
  type: string;
  data: any;
}

export type WebSocketEventHandler = (event: WidgetWebSocketEvent) => void;

export interface ChannelSubscription {
  channel: string;
  unsubscribe: () => void;
}
