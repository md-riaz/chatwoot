/**
 * Widget Conversation Store
 * 
 * Manages the current conversation state for the widget.
 */

import * as conversationApi from '../api/conversation';
import * as messageApi from '../api/message';
import type { Conversation, Message, CreateConversationParams } from '../api/types';

class WidgetConversationStore {
  private current = $state<Conversation | null>(null);
  private messages = $state<Message[]>([]);
  private isLoading = $state(false);
  private isSending = $state(false);
  private error = $state<string | null>(null);

  // Getters
  get conversation() {
    return this.current;
  }

  get allMessages() {
    return this.messages;
  }

  get loading() {
    return this.isLoading;
  }

  get sending() {
    return this.isSending;
  }

  get errorMessage() {
    return this.error;
  }

  // Derived values
  get hasConversation() {
    return (!!this.current);
  }

  get conversationId() {
    return (this.current?.id || null);
  }

  get unreadCount() {
    return (
      this.messages.filter((m) => !m.read && m.messageType === 0).length
    );
  }

  get sortedMessages() {
    return ([...this.messages].sort((a, b) => 
      new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime()
    ));
  }

  get lastMessage() {
    return this.sortedMessages[this.sortedMessages.length - 1] || null;
  }

  // Methods for WebSocket integration
  getLastMessage(): Message | null {
    return this.lastMessage;
  }

  // Actions
  async createConversation(params: CreateConversationParams): Promise<Conversation | null> {
    this.isLoading = true;
    this.error = null;

    try {
      const conversation = await conversationApi.createConversation(params);
      this.current = conversation;
      
      // If message was included, fetch messages
      if (params.message) {
        await this.loadMessages(conversation.id);
      }

      return conversation;
    } catch (err: any) {
      this.error = err.message || 'Failed to create conversation';
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  async loadConversation(id: number): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const conversation = await conversationApi.getConversation(id);
      this.current = conversation;
      await this.loadMessages(id);
    } catch (err: any) {
      this.error = err.message || 'Failed to load conversation';
    } finally {
      this.isLoading = false;
    }
  }

  async loadMessages(conversationId: number): Promise<void> {
    try {
      const messages = await messageApi.getMessages(conversationId);
      this.messages = messages;
    } catch (err: any) {
      this.error = err.message || 'Failed to load messages';
    }
  }

  async sendMessage(content: string, attachments?: File[]): Promise<Message | null> {
    if (!this.current) {
      this.error = 'No active conversation';
      return null;
    }

    this.isSending = true;
    this.error = null;

    // Create temporary message for optimistic update
    const echoId = `temp-${Date.now()}`;
    const tempMessage: Message = {
      id: -1,
      content,
      messageType: 1,
      createdAt: new Date().toISOString(),
      conversationId: this.current.id,
      contentAttributes: { echoId },
    };

    // Add temp message immediately
    this.messages = [...this.messages, tempMessage];

    try {
      const message = await messageApi.createMessage({
        conversationId: this.current.id,
        content,
        attachments,
        echoId,
      });

      // Replace temp message with real message
      this.messages = this.messages.map((m) =>
        m.contentAttributes?.echoId === echoId ? message : m
      );

      return message;
    } catch (err: any) {
      // Remove temp message on error
      this.messages = this.messages.filter(
        (m) => m.contentAttributes?.echoId !== echoId
      );
      this.error = err.message || 'Failed to send message';
      return null;
    } finally {
      this.isSending = false;
    }
  }

  addMessage(message: Message) {
    // Check if message already exists (by echo ID or message ID)
    const exists = this.messages.some(
      (m) =>
        m.id === message.id ||
        (m.contentAttributes?.echoId &&
          m.contentAttributes.echoId === message.contentAttributes?.echoId)
    );

    if (!exists) {
      this.messages = [...this.messages, message];
    }
  }

  updateMessage(messageId: number, updates: Partial<Message>) {
    this.messages = this.messages.map((m) =>
      m.id === messageId ? { ...m, ...updates } : m
    );
  }

  async markAsRead(): Promise<void> {
    if (!this.current) return;

    try {
      await conversationApi.markMessagesRead(this.current.id);
      this.messages = this.messages.map((m) => ({ ...m, read: true }));
    } catch (err: any) {
      console.error('Failed to mark messages as read:', err);
    }
  }

  updateStatus(status: 'open' | 'resolved' | 'pending' | 'snoozed') {
    if (this.current) {
      this.current = { ...this.current, status };
    }
  }

  reset() {
    this.current = null;
    this.messages = [];
    this.isLoading = false;
    this.isSending = false;
    this.error = null;
  }
}

export const widgetConversationStore = new WidgetConversationStore();
