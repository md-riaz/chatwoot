/**
 * Widget Store Types
 */

export interface WidgetState {
  isOpen: boolean;
  isMinimized: boolean;
  unreadCount: number;
}

export interface ConversationState {
  current: Conversation | null;
  isLoading: boolean;
  error: string | null;
}

export interface MessagesState {
  items: Message[];
  isLoading: boolean;
  isSending: boolean;
  hasMore: boolean;
  error: string | null;
}

export interface AgentState {
  available: Agent[];
  isOnline: boolean;
  typingAgents: Set<number>;
}

export interface CampaignState {
  active: Campaign[];
  dismissed: Set<number>;
  isLoading: boolean;
}

export interface ArticlesState {
  items: Article[];
  isLoading: boolean;
  searchQuery: string;
}

import type { Conversation, Message, Agent, Campaign, Article } from '../api/types';
