/**
 * Widget Component Types
 */

export interface WidgetBubbleProps {
  onclick?: () => void;
  unreadCount?: number;
  color?: string;
}

export interface WidgetWindowProps {
  hasConversation: boolean;
  onclose?: () => void;
}

export interface MessageListProps {
  conversationId: number;
}

export interface MessageBubbleProps {
  message: Message;
  isAgent: boolean;
}

export interface MessageInputProps {
  conversationId: number;
  disabled?: boolean;
}

import type { Message } from '$lib/widget/api/types';
