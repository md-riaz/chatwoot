/**
 * Notifications API Client
 * Handles notification fetching, marking as read, and deletion
 */

import api from './client';

export interface Notification {
  id: number;
  accountId: number;
  userId: number;
  notificationType: string;
  primaryActorType: string;
  primaryActorId: number;
  primaryActor: {
    id: number;
    name: string;
    thumbnail?: string;
  };
  readAt: string | null;
  snoozedUntil: string | null;
  createdAt: string;
  lastActivityAt: string;
  meta?: Record<string, any>;
  pushMessageTitle?: string;
}

export interface NotificationsListResponse {
  data: {
    payload: Notification[];
    meta: {
      count: number;
      currentPage: number;
      unreadCount: number;
    };
  };
}

export interface UnreadCountResponse {
  unreadCount: number;
}

/**
 * Get notifications with pagination
 */
export async function getNotifications(
  page: number = 1
): Promise<NotificationsListResponse> {
  return api.get('api/v1/notifications', {
    searchParams: { page }
  }).json();
}

/**
 * Get unread notification count
 */
export async function getUnreadCount(): Promise<UnreadCountResponse> {
  return api.get('api/v1/notifications/unread_count').json();
}

/**
 * Mark single notification as read
 */
export async function markAsRead(notificationId: number): Promise<void> {
  await api.post(`api/v1/notifications/${notificationId}/read`).json();
}

/**
 * Mark all notifications as read
 */
export async function markAllAsRead(): Promise<void> {
  await api.post('api/v1/notifications/read_all').json();
}

/**
 * Delete single notification
 */
export async function deleteNotification(notificationId: number): Promise<void> {
  await api.delete(`api/v1/notifications/${notificationId}`).json();
}

/**
 * Delete all notifications of a specific type
 */
export async function deleteAll(type?: string): Promise<void> {
  const searchParams = type ? { type } : {};
  await api.post('api/v1/notifications/destroy_all', {
    searchParams
  }).json();
}
