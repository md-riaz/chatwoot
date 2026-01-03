/**
 * Conversation component types
 */

export type ConversationStatus = 'open' | 'resolved' | 'pending' | 'snoozed';
export type ConversationPriority = 'urgent' | 'high' | 'medium' | 'low' | null;

export interface ConversationFilterOptions {
  status?: ConversationStatus;
  inboxId?: number;
  assigneeType?: 'me' | 'unassigned' | 'all';
  teamId?: number;
  labels?: string[];
}

export interface ConversationSortOptions {
  sortBy: 'latest' | 'oldest' | 'priority' | 'unread';
  direction?: 'asc' | 'desc';
}
