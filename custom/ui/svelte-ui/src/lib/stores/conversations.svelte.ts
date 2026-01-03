/**
 * Conversations Store using Svelte 5 runes
 * Replaces app/javascript/dashboard/store/modules/conversations/
 * 
 * This is a simplified version focusing on core conversation management.
 * Additional features like filtering, sorting, and advanced actions can be added incrementally.
 */

import { get } from 'svelte/store';
import { page } from '$app/stores';
import * as conversationsAPI from '$lib/api/conversations';
import type { Conversation, ConversationStatus, ConversationPriority, ConversationListParams } from '$lib/api/conversations';

/**
 * Sort type for conversations
 */
type SortType = 'latest' | 'oldest' | 'unread' | 'priority';

/**
 * Conversations store using Svelte 5 runes
 */
class ConversationsStore {
  // Reactive state
  allConversations = $state<Conversation[]>([]);
  selectedConversationId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  error = $state<string | null>(null);
  
  // Filters
  statusFilter = $state<ConversationStatus>('open');
  sortFilter = $state<SortType>('latest');
  currentInboxId = $state<number | null>(null);
  appliedFilters = $state<any[]>([]);
  
  // Attachments cache
  attachments = $state<Record<number, any[]>>({});
  
  // Computed values using $derived
  selectedConversation = $derived(
    this.allConversations.find(c => c.id === this.selectedConversationId) || null
  );
  
  selectedConversationAttachments = $derived(
    this.selectedConversationId 
      ? this.attachments[this.selectedConversationId] || []
      : []
  );
  
  /**
   * Get sorted conversations
   */
  get sortedConversations() {
    return [...this.allConversations].sort((a, b) => {
      switch (this.sortFilter) {
        case 'latest':
          return b.lastActivityAt - a.lastActivityAt;
        case 'oldest':
          return a.lastActivityAt - b.lastActivityAt;
        case 'unread':
          return b.unreadCount - a.unreadCount;
        case 'priority':
          const priorityOrder = { urgent: 0, high: 1, medium: 2, low: 3, null: 4 };
          return (priorityOrder[a.priority] || 4) - (priorityOrder[b.priority] || 4);
        default:
          return 0;
      }
    });
  }
  
  /**
   * Get filtered conversations (by status, inbox, etc.)
   */
  get filteredConversations() {
    return this.sortedConversations.filter(conversation => {
      // Filter by status
      if (this.statusFilter && conversation.status !== this.statusFilter) {
        return false;
      }
      
      // Filter by inbox
      if (this.currentInboxId && conversation.inboxId !== this.currentInboxId) {
        return false;
      }
      
      return true;
    });
  }
  
  /**
   * Get current account ID from route
   */
  get currentAccountId(): number | null {
    const pageData = get(page);
    const accountId = pageData?.params?.accountId;
    return accountId ? Number(accountId) : null;
  }
  
  /**
   * Get conversation by ID
   */
  getConversationById(conversationId: number): Conversation | null {
    return this.allConversations.find(c => c.id === conversationId) || null;
  }
  
  /**
   * Fetch all conversations
   */
  async fetchConversations(params?: Partial<ConversationListParams>) {
    const accountId = this.currentAccountId;
    if (!accountId) {
      this.error = 'No account ID available';
      return;
    }
    
    this.isLoading = true;
    this.error = null;
    
    try {
      const response = await conversationsAPI.getConversations({
        accountId,
        status: this.statusFilter,
        inboxId: this.currentInboxId || undefined,
        sortBy: this.sortFilter,
        ...params
      });
      
      this.setConversations(response.items);
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch conversations';
      throw err;
    } finally {
      this.isLoading = false;
    }
  }
  
  /**
   * Fetch filtered conversations
   */
  async fetchFilteredConversations(payload: any[], page: number = 1) {
    const accountId = this.currentAccountId;
    if (!accountId) {
      this.error = 'No account ID available';
      return;
    }
    
    this.isLoading = true;
    this.error = null;
    
    try {
      const response = await conversationsAPI.filterConversations({
        accountId,
        payload,
        page
      });
      
      this.setConversations(response.payload);
      this.appliedFilters = payload;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch filtered conversations';
      throw err;
    } finally {
      this.isLoading = false;
    }
  }
  
  /**
   * Fetch single conversation
   */
  async fetchConversation(conversationId: number) {
    const accountId = this.currentAccountId;
    if (!accountId) {
      this.error = 'No account ID available';
      return;
    }
    
    try {
      const conversation = await conversationsAPI.getConversation(accountId, conversationId);
      this.updateConversation(conversation);
      return conversation;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch conversation';
      throw err;
    }
  }
  
  /**
   * Select a conversation
   */
  setSelectedConversation(conversationId: number | null) {
    this.selectedConversationId = conversationId;
  }
  
  /**
   * Update conversation status
   */
  async updateStatus(conversationId: number, status: ConversationStatus, snoozedUntil?: number) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    const previousStatus = this.getConversationById(conversationId)?.status;
    
    // Optimistic update
    this.updateConversationLocally(conversationId, { status, snoozedUntil: snoozedUntil || null });
    
    try {
      const updated = await conversationsAPI.updateConversation(accountId, conversationId, {
        status,
        snoozedUntil: snoozedUntil || null
      });
      this.updateConversation(updated);
    } catch (err: any) {
      // Revert on error
      if (previousStatus) {
        this.updateConversationLocally(conversationId, { status: previousStatus });
      }
      this.error = err.message || 'Failed to update status';
      throw err;
    }
  }
  
  /**
   * Toggle conversation status (open <-> resolved)
   */
  async toggleStatus(conversationId: number) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    try {
      const updated = await conversationsAPI.toggleStatus(accountId, conversationId);
      this.updateConversation(updated);
    } catch (err: any) {
      this.error = err.message || 'Failed to toggle status';
      throw err;
    }
  }
  
  /**
   * Assign agent to conversation
   */
  async assignAgent(conversationId: number, assigneeId: number | null) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    try {
      const updated = await conversationsAPI.assignAgent(accountId, conversationId, assigneeId);
      this.updateConversation(updated);
    } catch (err: any) {
      this.error = err.message || 'Failed to assign agent';
      throw err;
    }
  }
  
  /**
   * Assign team to conversation
   */
  async assignTeam(conversationId: number, teamId: number | null) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    try {
      const updated = await conversationsAPI.assignTeam(accountId, conversationId, teamId);
      this.updateConversation(updated);
    } catch (err: any) {
      this.error = err.message || 'Failed to assign team';
      throw err;
    }
  }
  
  /**
   * Update conversation priority
   */
  async updatePriority(conversationId: number, priority: ConversationPriority) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    // Optimistic update
    this.updateConversationLocally(conversationId, { priority });
    
    try {
      const updated = await conversationsAPI.updateConversation(accountId, conversationId, { priority });
      this.updateConversation(updated);
    } catch (err: any) {
      this.error = err.message || 'Failed to update priority';
      throw err;
    }
  }
  
  /**
   * Mute conversation
   */
  async muteConversation(conversationId: number) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    // Optimistic update
    this.updateConversationLocally(conversationId, { muted: true });
    
    try {
      await conversationsAPI.muteConversation(accountId, conversationId);
    } catch (err: any) {
      // Revert
      this.updateConversationLocally(conversationId, { muted: false });
      this.error = err.message || 'Failed to mute conversation';
      throw err;
    }
  }
  
  /**
   * Unmute conversation
   */
  async unmuteConversation(conversationId: number) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    // Optimistic update
    this.updateConversationLocally(conversationId, { muted: false });
    
    try {
      await conversationsAPI.unmuteConversation(accountId, conversationId);
    } catch (err: any) {
      // Revert
      this.updateConversationLocally(conversationId, { muted: true });
      this.error = err.message || 'Failed to unmute conversation';
      throw err;
    }
  }
  
  /**
   * Update conversation labels
   */
  async updateLabels(conversationId: number, labels: string[]) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    // Optimistic update
    this.updateConversationLocally(conversationId, { labels });
    
    try {
      await conversationsAPI.updateLabels(accountId, conversationId, labels);
    } catch (err: any) {
      this.error = err.message || 'Failed to update labels';
      throw err;
    }
  }
  
  /**
   * Update custom attributes
   */
  async updateCustomAttributes(conversationId: number, customAttributes: Record<string, any>) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    try {
      const updated = await conversationsAPI.updateCustomAttributes(accountId, conversationId, customAttributes);
      this.updateConversation(updated);
    } catch (err: any) {
      this.error = err.message || 'Failed to update custom attributes';
      throw err;
    }
  }
  
  /**
   * Mark messages as read
   */
  async markMessagesRead(conversationId: number) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    try {
      const { agentLastSeenAt, unreadCount } = await conversationsAPI.markMessagesRead(
        accountId,
        conversationId
      );
      
      this.updateConversationLocally(conversationId, {
        agentLastSeenAt,
        unreadCount
      });
    } catch (err: any) {
      this.error = err.message || 'Failed to mark as read';
      throw err;
    }
  }
  
  /**
   * Fetch attachments for a conversation
   */
  async fetchAttachments(conversationId: number) {
    const accountId = this.currentAccountId;
    if (!accountId) return;
    
    try {
      const attachments = await conversationsAPI.getAllAttachments(accountId, conversationId);
      this.attachments[conversationId] = attachments;
    } catch (err: any) {
      // Don't throw, just log
      console.error('Failed to fetch attachments:', err);
    }
  }
  
  /**
   * Set status filter
   */
  setStatusFilter(status: ConversationStatus) {
    this.statusFilter = status;
  }
  
  /**
   * Set sort filter
   */
  setSortFilter(sort: SortType) {
    this.sortFilter = sort;
  }
  
  /**
   * Set active inbox
   */
  setActiveInbox(inboxId: number | null) {
    this.currentInboxId = inboxId;
  }
  
  /**
   * Clear all conversations
   */
  clearConversations() {
    this.allConversations = [];
    this.selectedConversationId = null;
  }
  
  // Private helper methods
  
  /**
   * Set conversations list (merge with existing)
   */
  private setConversations(conversations: Conversation[]) {
    const newConversations = [...this.allConversations];
    
    conversations.forEach(conversation => {
      const index = newConversations.findIndex(c => c.id === conversation.id);
      
      if (index < 0) {
        // New conversation
        newConversations.push(conversation);
      } else if (conversation.id !== this.selectedConversationId) {
        // Replace existing (not selected)
        newConversations[index] = conversation;
      } else {
        // Selected conversation - merge carefully to preserve messages
        const existing = newConversations[index];
        newConversations[index] = {
          ...conversation,
          messages: existing.messages || conversation.messages,
          allMessagesLoaded: existing.allMessagesLoaded,
          dataFetched: existing.dataFetched
        };
      }
    });
    
    this.allConversations = newConversations;
  }
  
  /**
   * Update a single conversation in the list
   */
  private updateConversation(conversation: Conversation) {
    const index = this.allConversations.findIndex(c => c.id === conversation.id);
    
    if (index >= 0) {
      // Merge with existing to preserve local state
      const existing = this.allConversations[index];
      this.allConversations[index] = {
        ...existing,
        ...conversation,
        messages: existing.messages || conversation.messages
      };
    } else {
      // Add new conversation
      this.allConversations.push(conversation);
    }
  }
  
  /**
   * Update conversation properties locally (optimistic updates)
   */
  private updateConversationLocally(conversationId: number, updates: Partial<Conversation>) {
    const conversation = this.getConversationById(conversationId);
    if (conversation) {
      Object.assign(conversation, updates);
    }
  }
}

// Export singleton instance
export const conversationsStore = new ConversationsStore();
