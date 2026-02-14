/**
 * Conversations Store using Svelte 5 runes with Vue-style mutation/action pattern
 * Replaces app/javascript/dashboard/store/modules/conversations/
 * 
 * Implements Vue ActionCable parity with:
 * - Strict separation of mutations (pure state) and actions (side effects)
 * - Account event validation for security
 * - Audio notifications and event bus integration
 * - Message synchronization and private message filtering
 */

import { page } from '$app/stores';
import type { Conversation, ConversationListParams, ConversationPriority, ConversationStatus } from '$lib/api/conversations';
import * as conversationsAPI from '$lib/api/conversations';
import { get } from 'svelte/store';
import { eventBus, BUS_EVENTS } from '$lib/utils/event-bus';
import { audioNotificationManager } from '$lib/utils/audio-notifications';

/**
 * Sort type for conversations
 */
type SortType = 'latest' | 'oldest' | 'unread' | 'priority';

/**
 * Message interface for type safety
 */
interface Message {
  id: number;
  conversation_id: number;
  content: string;
  message_type: string;
  created_at: string;
  sender_type: string;
  sender_id: number;
  is_private?: boolean;
  attachments?: any[];
}

/**
 * Conversations store using Svelte 5 runes with Vue-style mutation/action pattern
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
    const getLastActivityTime = (conversation: Conversation): number => {
      if (!conversation.lastActivityAt) {
        return 0;
      }

      const timestamp = new Date(conversation.lastActivityAt).getTime();
      return Number.isNaN(timestamp) ? 0 : timestamp;
    };

    return [...this.allConversations].sort((a, b) => {
      switch (this.sortFilter) {
        case 'latest':
          return getLastActivityTime(b) - getLastActivityTime(a);
        case 'oldest':
          return getLastActivityTime(a) - getLastActivityTime(b);
        case 'unread':
          return (b.unreadCount || 0) - (a.unreadCount || 0);
        case 'priority':
          const priorityOrder: Record<string, number> = { urgent: 0, high: 1, medium: 2, low: 3, '': 4 };
          return (priorityOrder[a.priority || ''] || 4) - (priorityOrder[b.priority || ''] || 4);
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
      
      // Handle Laravel pagination format
      const conversations = response?.data || (response as any)?.items || response || [];
      this.setConversations(conversations);
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
    
    // Optimistic update - convert number to string for snoozedUntil
    this.updateConversationLocally(conversationId, { 
      status, 
      snoozedUntil: snoozedUntil ? snoozedUntil.toString() : undefined 
    });
    
    try {
      const updated = await conversationsAPI.updateConversation(accountId, conversationId, {
        status,
        snoozedUntil: snoozedUntil || undefined
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
  
  // WebSocket event handlers with Vue parity

  /**
   * Handle new message created via WebSocket (Action with side effects)
   */
  handleMessageCreated(message: any): void {
    // Mutation: Pure state update
    this.mutations.ADD_MESSAGE(message);
    
    // Actions: Side effects (matching Vue pattern)
    this.playAudioNotification(message);
    this.updateConversationStats();
    this.moveConversationToTop(message.conversation_id);
    
    // Emit event for external listeners
    eventBus.emit(BUS_EVENTS.AGENT_MESSAGE_RECEIVED, message);
  }

  /**
   * Add new conversation from WebSocket (Action)
   */
  addConversation(conversation: Conversation): void {
    // Mutation: Pure state update
    this.mutations.ADD_CONVERSATION(conversation);
    
    // Actions: Side effects
    this.updateConversationStats();
  }

  /**
   * Update message via WebSocket (Action)
   */
  updateMessage(message: any): void {
    // Mutation: Pure state update
    this.mutations.UPDATE_MESSAGE(message);
  }

  /**
   * Remove message via WebSocket (Action)
   */
  removeMessage(messageId: number): void {
    // Mutation: Pure state update
    this.mutations.REMOVE_MESSAGE(messageId);
  }

  /**
   * Mark conversation as read via WebSocket (Action)
   */
  markAsRead(conversationId: number): void {
    // Mutation: Pure state update
    this.mutations.MARK_AS_READ(conversationId);
  }

  /**
   * Set typing status for a conversation (Action)
   */
  setTyping(conversationId: number, typer: any, isTyping: boolean): void {
    // Mutation: Pure state update
    this.mutations.SET_TYPING(conversationId, typer, isTyping);
  }

  /**
   * Mark first reply for conversation (Action)
   */
  markFirstReply(conversationId: number, message: any): void {
    // Mutation: Pure state update
    this.mutations.MARK_FIRST_REPLY(conversationId, message);
  }

  /**
   * Refresh conversations (for cache invalidation) (Action)
   */
  async refreshConversations(): Promise<void> {
    try {
      await this.fetchConversations();
    } catch (error) {
      console.error('Failed to refresh conversations:', error);
    }
  }

  /**
   * Set last message ID for sync on reconnect (matching Vue implementation)
   */
  setLastMessageId(): number | null {
    const allMessages = this.allConversations.flatMap(c => c.messages || []);
    
    if (allMessages.length > 0) {
      const lastMessage = allMessages.sort((a, b) => 
        new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
      )[0];
      
      return lastMessage.id;
    }
    
    return null;
  }

  // Private mutations (pure state updates, matching Vue pattern)
  private mutations = {
    ADD_MESSAGE: (message: any) => {
      const conversation = this.getConversationById(message.conversation_id);
      if (conversation) {
        // Add message to conversation
        if (!conversation.messages) {
          conversation.messages = [];
        }
        conversation.messages.push(message);
        
        // Update conversation metadata
        conversation.lastActivityAt = message.created_at;
        conversation.unreadCount = (conversation.unreadCount || 0) + 1;
      }
    },

    ADD_CONVERSATION: (conversation: Conversation) => {
      const existing = this.getConversationById(conversation.id);
      if (!existing) {
        this.allConversations.unshift(conversation);
      }
    },

    UPDATE_MESSAGE: (message: any) => {
      const conversation = this.getConversationById(message.conversation_id);
      if (conversation && conversation.messages) {
        const messageIndex = conversation.messages.findIndex(m => m.id === message.id);
        if (messageIndex >= 0) {
          conversation.messages[messageIndex] = message;
        }
      }
    },

    REMOVE_MESSAGE: (messageId: number) => {
      this.allConversations.forEach(conversation => {
        if (conversation.messages) {
          conversation.messages = conversation.messages.filter(m => m.id !== messageId);
        }
      });
    },

    MARK_AS_READ: (conversationId: number) => {
      const conversation = this.getConversationById(conversationId);
      if (conversation) {
        conversation.unreadCount = 0;
        conversation.agentLastSeenAt = Date.now();
      }
    },

    SET_TYPING: (conversationId: number, typer: any, isTyping: boolean) => {
      const conversation = this.getConversationById(conversationId);
      if (conversation) {
        if (!(conversation as any).typingUsers) {
          (conversation as any).typingUsers = [];
        }
        
        const existingIndex = (conversation as any).typingUsers.findIndex((u: any) => u.id === typer.id);
        
        if (isTyping && existingIndex === -1) {
          (conversation as any).typingUsers.push(typer);
        } else if (!isTyping && existingIndex >= 0) {
          (conversation as any).typingUsers.splice(existingIndex, 1);
        }
      }
    },

    MARK_FIRST_REPLY: (conversationId: number, message: any) => {
      const conversation = this.getConversationById(conversationId);
      if (conversation) {
        (conversation as any).firstReplyCreatedAt = message.created_at;
      }
    },

    UPDATE_CONVERSATION: (conversation: Conversation) => {
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
    },
  };

  // Private action helpers (side effects, matching Vue pattern)
  
  /**
   * Play audio notification for new message (matching Vue DashboardAudioNotificationHelper)
   */
  private playAudioNotification(message: any): void {
    audioNotificationManager.onNewMessage({
      sender_id: message.sender_id,
      message_type: message.message_type,
      conversation: this.getConversationById(message.conversation_id)
    });
  }

  /**
   * Update conversation statistics (matching Vue fetchConversationStats)
   */
  private updateConversationStats(): void {
    eventBus.emit(BUS_EVENTS.FETCH_CONVERSATION_STATS);
  }

  /**
   * Move conversation to top of list (for new messages)
   */
  private moveConversationToTop(conversationId: number): void {
    const index = this.allConversations.findIndex(c => c.id === conversationId);
    if (index > 0) {
      const conversation = this.allConversations.splice(index, 1)[0];
      this.allConversations.unshift(conversation);
    }
  }
  
  // Private helper methods
  
  /**
   * Set conversations list (merge with existing)
   */
  private setConversations(conversations: Conversation[]) {
    // Add null safety check
    if (!conversations || !Array.isArray(conversations)) {
      console.warn('setConversations called with invalid data:', conversations);
      return;
    }
    
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
   * Update a single conversation in the list (Action)
   */
  updateConversation(conversation: Conversation) {
    // Mutation: Pure state update
    this.mutations.UPDATE_CONVERSATION(conversation);
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
