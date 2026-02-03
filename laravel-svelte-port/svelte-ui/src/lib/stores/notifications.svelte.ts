/**
 * Notifications Store
 * Manages notifications state using Svelte 5 runes
 */

import * as notificationsApi from '$lib/api/notifications';
import type { Notification } from '$lib/api/notifications';

interface NotificationsState {
  all: Notification[];
  unreadCount: number;
  isLoading: boolean;
  isMarkingRead: boolean;
  isDeleting: boolean;
  error: string | null;
  currentPage: number;
  hasMore: boolean;
  accountId: number | null;
  filters: {
    sortOrder: 'newest' | 'oldest';
    showRead: boolean;
    showSnoozed: boolean;
  };
}

class NotificationsStore {
  private state = $state<NotificationsState>({
    all: [],
    unreadCount: 0,
    isLoading: false,
    isMarkingRead: false,
    isDeleting: false,
    error: null,
    currentPage: 1,
    hasMore: true,
    accountId: null,
    filters: {
      sortOrder: 'newest',
      showRead: true,
      showSnoozed: true
    }
  });

  // Getters
  get all() {
    return this.state.all;
  }

  get unreadCount() {
    return this.state.unreadCount;
  }

  get isLoading() {
    return this.state.isLoading;
  }

  get isMarkingRead() {
    return this.state.isMarkingRead;
  }

  get isDeleting() {
    return this.state.isDeleting;
  }

  get error() {
    return this.state.error;
  }

  get accountId() {
    return this.state.accountId;
  }

  get filters() {
    return this.state.filters;
  }

  // Derived getters
  get unreadNotifications() {
    return (
      this.state.all.filter(n => !n.readAt)
    );
  }

  get readNotifications() {
    return (
      this.state.all.filter(n => n.readAt)
    );
  }

  get sortedNotifications() {
    return (
      [...this.state.all].sort((a, b) => 
        new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
      )
    );
  }

  get hasUnread() {
    return (this.state.unreadCount > 0);
  }

  // Actions
  async fetchNotifications(accountId: number, page: number = 1) {
    this.state.isLoading = true;
    this.state.error = null;
    this.state.accountId = accountId;

    try {
      const sortOrder = this.state.filters.sortOrder === 'newest' ? 'desc' : 'asc';
      const includes: string[] = [];
      if (this.state.filters.showRead) includes.push('read');
      if (this.state.filters.showSnoozed) includes.push('snoozed');

      const response = await notificationsApi.getNotifications(
        accountId, 
        page, 
        sortOrder, 
        includes
      );
      
      if (page === 1) {
        this.state.all = response.data;
      } else {
        this.state.all = [...this.state.all, ...response.data];
      }
      
      this.state.unreadCount = response.meta.unread_count;
      this.state.currentPage = response.meta.current_page;
      this.state.hasMore = response.data.length > 0;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch notifications';
      console.error('Error fetching notifications:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async updateFilters(accountId: number, filters: Partial<NotificationsState['filters']>) {
    this.state.filters = { ...this.state.filters, ...filters };
    this.state.all = []; // Clear list to show loading state
    await this.fetchNotifications(accountId, 1);
  }

  async fetchUnreadCount(accountId: number) {
    try {
      const response = await notificationsApi.getUnreadCount(accountId);
      this.state.unreadCount = response.unreadCount;
    } catch (error) {
      console.error('Error fetching unread count:', error);
    }
  }

  async loadMore() {
    if (!this.state.hasMore || this.state.isLoading || !this.state.accountId) return;
    
    await this.fetchNotifications(this.state.accountId, this.state.currentPage + 1);
  }

  async markAsRead(accountId: number, notificationId: string) {
    this.state.isMarkingRead = true;
    
    // Optimistic update
    const notification = this.state.all.find(n => n.id === notificationId);
    if (notification && !notification.readAt) {
      this.state.all = this.state.all.map(n =>
        n.id === notificationId ? { ...n, readAt: new Date().toISOString() } : n
      );
      this.state.unreadCount = Math.max(0, this.state.unreadCount - 1);
    }

    try {
      await notificationsApi.markAsRead(accountId, notificationId);
    } catch (error) {
      // Rollback on error
      if (notification) {
        this.state.all = this.state.all.map(n =>
          n.id === notificationId ? notification : n
        );
        this.state.unreadCount = this.state.unreadCount + 1;
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to mark as read';
      console.error('Error marking notification as read:', error);
    } finally {
      this.state.isMarkingRead = false;
    }
  }

  async markAllAsRead(accountId: number) {
    this.state.isMarkingRead = true;
    
    // Optimistic update
    const oldNotifications = [...this.state.all];
    const oldUnreadCount = this.state.unreadCount;
    const now = new Date().toISOString();
    
    this.state.all = this.state.all.map(n => ({ ...n, readAt: now }));
    this.state.unreadCount = 0;

    try {
      await notificationsApi.markAllAsRead(accountId);
    } catch (error) {
      // Rollback on error
      this.state.all = oldNotifications;
      this.state.unreadCount = oldUnreadCount;
      this.state.error = error instanceof Error ? error.message : 'Failed to mark all as read';
      console.error('Error marking all as read:', error);
    } finally {
      this.state.isMarkingRead = false;
    }
  }

  async deleteNotification(accountId: number, notificationId: string) {
    this.state.isDeleting = true;
    
    // Optimistic delete
    const deletedNotification = this.state.all.find(n => n.id === notificationId);
    this.state.all = this.state.all.filter(n => n.id !== notificationId);
    
    if (deletedNotification && !deletedNotification.readAt) {
      this.state.unreadCount = Math.max(0, this.state.unreadCount - 1);
    }

    try {
      await notificationsApi.deleteNotification(accountId, notificationId);
    } catch (error) {
      // Rollback on error
      if (deletedNotification) {
        this.state.all = [...this.state.all, deletedNotification];
        if (!deletedNotification.readAt) {
          this.state.unreadCount = this.state.unreadCount + 1;
        }
      }
      this.state.error = error instanceof Error ? error.message : 'Failed to delete notification';
      console.error('Error deleting notification:', error);
    } finally {
      this.state.isDeleting = false;
    }
  }

  async deleteAll(accountId: number, type: 'read' | 'all' = 'read') {
    this.state.isDeleting = true;
    
    // Optimistic delete
    const oldNotifications = [...this.state.all];
    const oldUnreadCount = this.state.unreadCount;
    
    if (type === 'read') {
      this.state.all = this.state.all.filter(n => !n.readAt);
      // unreadCount shouldn't change as we only remove read ones
    } else {
      // type === 'all'
      this.state.all = [];
      this.state.unreadCount = 0;
    }

    try {
      await notificationsApi.deleteAll(accountId, type);
    } catch (error) {
      // Rollback on error
      this.state.all = oldNotifications;
      this.state.unreadCount = oldUnreadCount;
      this.state.error = error instanceof Error ? error.message : 'Failed to delete notifications';
      console.error('Error deleting notifications:', error);
    } finally {
      this.state.isDeleting = false;
    }
  }

  // WebSocket event handlers
  handleNewNotification(notification: Notification) {
    // Check if notification belongs to current account
    if (this.state.accountId && notification.accountId === this.state.accountId) {
      // Add to beginning of list
      this.state.all = [notification, ...this.state.all];
      this.state.unreadCount = this.state.unreadCount + 1;
    }
  }

  handleNotificationRead(notificationId: string) {
    this.state.all = this.state.all.map(n =>
      n.id === notificationId ? { ...n, readAt: new Date().toISOString() } : n
    );
    this.state.unreadCount = Math.max(0, this.state.unreadCount - 1);
  }

  clearError() {
    this.state.error = null;
  }

  reset() {
    this.state = {
      all: [],
      unreadCount: 0,
      isLoading: false,
      isMarkingRead: false,
      isDeleting: false,
      error: null,
      currentPage: 1,
      hasMore: true,
      accountId: null,
      filters: {
        sortOrder: 'newest',
        showRead: true,
        showSnoozed: true
      }
    };
  }

  // WebSocket event handlers

  /**
   * Handle notification updated via WebSocket
   */
  handleNotificationUpdated(notification: Notification): void {
    const index = this.state.all.findIndex(n => n.id === notification.id);
    if (index >= 0) {
      this.state.all[index] = notification;
    }
  }

  /**
   * Handle notification deleted via WebSocket
   */
  handleNotificationDeleted(notificationId: string): void {
    const notification = this.state.all.find(n => n.id === notificationId);
    this.state.all = this.state.all.filter(n => n.id !== notificationId);
    
    // Update unread count if deleted notification was unread
    if (notification && !notification.readAt) {
      this.state.unreadCount = Math.max(0, this.state.unreadCount - 1);
    }
  }

  /**
   * Add notification from WebSocket
   */
  addNotification(notification: Notification): void {
    // Check if notification already exists
    const exists = this.state.all.some(n => n.id === notification.id);
    if (!exists) {
      this.state.all.unshift(notification);
      if (!notification.readAt) {
        this.state.unreadCount += 1;
      }
    }
  }

  /**
   * Update notification from WebSocket
   */
  updateNotification(notification: Notification): void {
    const index = this.state.all.findIndex(n => n.id === notification.id);
    if (index >= 0) {
      const wasUnread = !this.state.all[index].readAt;
      const isUnread = !notification.readAt;
      
      this.state.all[index] = notification;
      
      // Update unread count if read status changed
      if (wasUnread && !isUnread) {
        this.state.unreadCount = Math.max(0, this.state.unreadCount - 1);
      } else if (!wasUnread && isUnread) {
        this.state.unreadCount += 1;
      }
    }
  }

  /**
   * Remove notification from WebSocket
   */
  removeNotification(notificationId: string): void {
    this.handleNotificationDeleted(notificationId);
  }

  /**
   * Add mention notification from WebSocket
   */
  addMentionNotification(conversation: any, message: any): void {
    const mentionNotification: Notification = {
      id: `mention-${Date.now()}`,
      notificationType: 'conversation_mention',
      primaryActorType: 'Conversation',
      primaryActorId: conversation.id,
      primaryActor: conversation,
      secondaryActor: message,
      accountId: conversation.account_id,
      userId: message.mentioned_user?.id,
      readAt: null,
      snoozedUntil: null,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      meta: {
        conversation,
        message,
        mentioner: message.user
      }
    };
    
    this.addNotification(mentionNotification);
  }
}

export const notificationsStore = new NotificationsStore();
