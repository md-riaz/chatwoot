/**
 * Messages Store
 * Manages message state for conversations using Svelte 5 runes
 * Replaces Vue's app/javascript/dashboard/store/modules/conversations/messages
 */

import type { Message, CreateMessageParams } from '$lib/api/messages';
import * as messagesApi from '$lib/api/messages';

/**
 * Messages store using Svelte 5 runes
 * Manages messages for the currently selected conversation
 */
class MessagesStore {
  // Reactive state using $state rune
  messages = $state<Message[]>([]);
  isLoading = $state<boolean>(false);
  isSending = $state<boolean>(false);
  error = $state<string | null>(null);
  
  // Pagination state
  allMessagesLoaded = $state<boolean>(false);
  currentPage = $state<number>(1);
  
  // UI state
  selectedMessageId = $state<number | null>(null);
  
  // Computed values using $derived rune
  selectedMessage = $derived(
    this.messages.find(m => m.id === this.selectedMessageId) || null
  );
  
  // Get messages sorted by creation time (oldest first)
  get sortedMessages(): Message[] {
    return [...this.messages].sort((a, b) => {
      const timeA = new Date(a.createdAt).getTime();
      const timeB = new Date(b.createdAt).getTime();
      return timeA - timeB;
    });
  }
  
  // Get unread messages count
  get unreadCount(): number {
    return this.messages.filter(m => m.messageType === 0 && m.status !== 'read').length;
  }
  
  // Get messages grouped by date
  get messagesByDate(): Record<string, Message[]> {
    const grouped: Record<string, Message[]> = {};
    
    this.sortedMessages.forEach(message => {
      const date = new Date(message.createdAt).toDateString();
      if (!grouped[date]) {
        grouped[date] = [];
      }
      grouped[date].push(message);
    });
    
    return grouped;
  }
  
  // Get private (internal) messages
  get privateMessages(): Message[] {
    return this.messages.filter(m => m.private);
  }
  
  // Get public messages
  get publicMessages(): Message[] {
    return this.messages.filter(m => !m.private);
  }
  
  /**
   * Set messages for a conversation
   * Used when loading a conversation or switching conversations
   */
  setMessages(messages: Message[]): void {
    this.messages = messages;
    this.allMessagesLoaded = false;
    this.currentPage = 1;
    this.error = null;
  }
  
  /**
   * Add a single message
   * Used for new incoming/outgoing messages via WebSocket
   */
  addMessage(message: Message): void {
    // Check if message already exists (avoid duplicates)
    const existingIndex = this.messages.findIndex(m => m.id === message.id);
    
    if (existingIndex === -1) {
      this.messages = [...this.messages, message];
    } else {
      // Update existing message (e.g., status change)
      this.messages = [
        ...this.messages.slice(0, existingIndex),
        message,
        ...this.messages.slice(existingIndex + 1),
      ];
    }
  }
  
  /**
   * Update a message
   * Used for status updates, edits, etc.
   */
  updateMessage(messageId: number, updates: Partial<Message>): void {
    const index = this.messages.findIndex(m => m.id === messageId);
    
    if (index !== -1) {
      this.messages = [
        ...this.messages.slice(0, index),
        { ...this.messages[index], ...updates },
        ...this.messages.slice(index + 1),
      ];
    }
  }
  
  /**
   * Remove a message
   */
  removeMessage(messageId: number): void {
    this.messages = this.messages.filter(m => m.id !== messageId);
  }
  
  /**
   * Create and send a new message
   */
  async sendMessage(params: CreateMessageParams): Promise<Message | null> {
    this.isSending = true;
    this.error = null;
    
    try {
      const message = await messagesApi.createMessage(params);
      
      // Add message to store
      this.addMessage(message);
      
      return message;
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to send message';
      console.error('Failed to send message:', err);
      return null;
    } finally {
      this.isSending = false;
    }
  }
  
  /**
   * Delete a message
   */
  async deleteMessage(conversationId: number, messageId: number): Promise<boolean> {
    this.error = null;
    
    // Optimistic update
    const originalMessages = [...this.messages];
    this.removeMessage(messageId);
    
    try {
      await messagesApi.deleteMessage(conversationId, messageId);
      return true;
    } catch (err) {
      // Rollback on error
      this.messages = originalMessages;
      this.error = err instanceof Error ? err.message : 'Failed to delete message';
      console.error('Failed to delete message:', err);
      return false;
    }
  }
  
  /**
   * Retry sending a failed message
   */
  async retryMessage(conversationId: number, messageId: number): Promise<boolean> {
    this.error = null;
    
    // Update status to 'progress'
    this.updateMessage(messageId, { status: 'progress' });
    
    try {
      const message = await messagesApi.retryMessage(conversationId, messageId);
      
      // Update with new message data
      this.updateMessage(messageId, message);
      
      return true;
    } catch (err) {
      // Update status to 'failed'
      this.updateMessage(messageId, { status: 'failed' });
      this.error = err instanceof Error ? err.message : 'Failed to retry message';
      console.error('Failed to retry message:', err);
      return false;
    }
  }
  
  /**
   * Load previous messages (pagination)
   */
  async loadPreviousMessages(conversationId: number): Promise<boolean> {
    if (this.allMessagesLoaded || this.isLoading) {
      return false;
    }
    
    this.isLoading = true;
    this.error = null;
    
    try {
      // Get the oldest message ID to use as 'before' parameter
      const oldestMessage = this.sortedMessages[0];
      const before = oldestMessage?.id;
      
      const { messages, meta } = await messagesApi.getPreviousMessages({
        conversationId,
        before,
      });
      
      if (messages.length === 0) {
        this.allMessagesLoaded = true;
        return false;
      }
      
      // Prepend messages to the beginning
      this.messages = [...messages, ...this.messages];
      
      // Check if all messages are loaded
      if (meta && meta.currentPage >= meta.totalPages) {
        this.allMessagesLoaded = true;
      }
      
      this.currentPage = meta?.currentPage || this.currentPage + 1;
      
      return true;
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to load messages';
      console.error('Failed to load previous messages:', err);
      return false;
    } finally {
      this.isLoading = false;
    }
  }
  
  /**
   * Translate a message
   */
  async translateMessage(
    conversationId: number,
    messageId: number,
    targetLanguage: string
  ): Promise<boolean> {
    this.error = null;
    
    try {
      const { translatedContent } = await messagesApi.translateMessage({
        conversationId,
        messageId,
        targetLanguage,
      });
      
      // Update message with translated content
      this.updateMessage(messageId, { translatedContent });
      
      return true;
    } catch (err) {
      this.error = err instanceof Error ? err.message : 'Failed to translate message';
      console.error('Failed to translate message:', err);
      return false;
    }
  }
  
  /**
   * Add a temporary message (optimistic update for sending)
   * Used to show message immediately before API response
   */
  addTemporaryMessage(message: Partial<Message> & { echoId: string }): void {
    const tempMessage: Message = {
      id: Date.now(), // Temporary ID
      content: message.content || '',
      messageType: 1, // Outgoing
      createdAt: new Date().toISOString(),
      private: message.private || false,
      status: 'progress',
      conversationId: message.conversationId!,
      echoId: message.echoId,
      contentAttributes: message.contentAttributes,
      attachments: message.attachments,
    };
    
    this.addMessage(tempMessage);
  }
  
  /**
   * Replace temporary message with actual message from API
   */
  replaceTemporaryMessage(echoId: string, message: Message): void {
    const tempIndex = this.messages.findIndex(m => m.echoId === echoId);
    
    if (tempIndex !== -1) {
      this.messages = [
        ...this.messages.slice(0, tempIndex),
        message,
        ...this.messages.slice(tempIndex + 1),
      ];
    } else {
      // If temp message not found, just add the new message
      this.addMessage(message);
    }
  }
  
  /**
   * Remove temporary message (on send failure)
   */
  removeTemporaryMessage(echoId: string): void {
    this.messages = this.messages.filter(m => m.echoId !== echoId);
  }
  
  /**
   * Select a message
   */
  selectMessage(messageId: number | null): void {
    this.selectedMessageId = messageId;
  }
  
  /**
   * Clear all messages
   */
  clearMessages(): void {
    this.messages = [];
    this.allMessagesLoaded = false;
    this.currentPage = 1;
    this.selectedMessageId = null;
    this.error = null;
  }
  
  /**
   * Reset store state
   */
  reset(): void {
    this.clearMessages();
    this.isLoading = false;
    this.isSending = false;
  }
}

// Export singleton instance
export const messagesStore = new MessagesStore();
