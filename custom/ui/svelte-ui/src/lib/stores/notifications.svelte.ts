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
    hasMore: true
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

  // Derived getters
  get unreadNotifications() {
    return $derived(
      this.state.all.filter(n => !n.readAt)
    );
  }

  get readNotifications() {
    return $derived(
      this.state.all.filter(n => n.readAt)
    );
  }

  get sortedNotifications() {
    return $derived(
      [...this.state.all].sort((a, b) => 
        new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
      )
    );
  }

  get hasUnread() {
    return $derived(this.state.unreadCount > 0);
  }

  // Actions
  async fetchNotifications(page: number = 1) {
    this.state.isLoading = true;
    this.state.error = null;

    try {
      const response = await notificationsApi.getNotifications(page);
      
      if (page === 1) {
        this.state.all = response.data.payload;
      } else {
        this.state.all = [...this.state.all, ...response.data.payload];
      }
      
      this.state.unreadCount = response.data.meta.unreadCount;
      this.state.currentPage = response.data.meta.currentPage;
      this.state.hasMore = response.data.payload.length > 0;
    } catch (error) {
      this.state.error = error instanceof Error ? error.message : 'Failed to fetch notifications';
      console.error('Error fetching notifications:', error);
    } finally {
      this.state.isLoading = false;
    }
  }

  async fetchUnreadCount() {
    try {
      const response = await notificationsApi.getUnreadCount();
      this.state.unreadCount = response.unreadCount;
    } catch (error) {
      console.error('Error fetching unread count:', error);
    }
  }

  async loadMore() {
    if (!this.state.hasMore || this.state.isLoading) return;
    
    await this.fetchNotifications(this.state.currentPage + 1);
  }

  async markAsRead(notificationId: number) {
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
      await notificationsApi.markAsRead(notificationId);
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

  async markAllAsRead() {
    this.state.isMarkingRead = true;
    
    // Optimistic update
    const oldNotifications = [...this.state.all];
    const oldUnreadCount = this.state.unreadCount;
    const now = new Date().toISOString();
    
    this.state.all = this.state.all.map(n => ({ ...n, readAt: now }));
    this.state.unreadCount = 0;

    try {
      await notificationsApi.markAllAsRead();
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

  async deleteNotification(notificationId: number) {
    this.state.isDeleting = true;
    
    // Optimistic delete
    const deletedNotification = this.state.all.find(n => n.id === notificationId);
    this.state.all = this.state.all.filter(n => n.id !== notificationId);
    
    if (deletedNotification && !deletedNotification.readAt) {
      this.state.unreadCount = Math.max(0, this.state.unreadCount - 1);
    }

    try {
      await notificationsApi.deleteNotification(notificationId);
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

  async deleteAll(type?: string) {
    this.state.isDeleting = true;
    
    // Optimistic delete
    const oldNotifications = [...this.state.all];
    const oldUnreadCount = this.state.unreadCount;
    
    if (type) {
      this.state.all = this.state.all.filter(n => n.notificationType !== type);
    } else {
      this.state.all = [];
    }
    this.state.unreadCount = 0;

    try {
      await notificationsApi.deleteAll(type);
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
    // Add to beginning of list
    this.state.all = [notification, ...this.state.all];
    this.state.unreadCount = this.state.unreadCount + 1;
  }

  handleNotificationRead(notificationId: number) {
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
      hasMore: true
    };
  }
}

export const notificationsStore = new NotificationsStore();
