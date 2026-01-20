/**
 * Notifications API Client
 * Handles notification fetching, marking as read, and deletion
 */

import api, { toSearchParams } from './client';

export interface Notification {
  id: string;
  accountId: number;
  userId: number;
  notificationType: string;
  primaryActorType: string | null;
  primaryActorId: number | null;
  primaryActor: {
    id: number;
    name: string;
    thumbnail?: string;
  } | null;
  readAt: string | null;
  snoozedUntil: string | null;
  createdAt: string;
  lastActivityAt: string;
  meta?: Record<string, any>;
  pushMessageTitle?: string;
}

export interface NotificationsListResponse {
  data: Notification[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    unread_count: number;
  };
}

export interface UnreadCountResponse {
  unreadCount: number;
}

export async function getNotifications(
  accountId: number,
  page: number = 1,
  sortOrder: 'asc' | 'desc' = 'desc',
  includes: string[] = ['snoozed', 'read']
): Promise<NotificationsListResponse> {
  const searchParams = new URLSearchParams();
  searchParams.append('page', String(page));
  searchParams.append('sort_order', sortOrder);
  includes.forEach(inc => searchParams.append('includes[]', inc));

  return api.get(`api/v1/accounts/${accountId}/notifications`, {
    searchParams
  }).json();
}

export async function getUnreadCount(accountId: number): Promise<UnreadCountResponse> {
  return api.get(`api/v1/accounts/${accountId}/notifications/unread_count`).json();
}

export async function markAsRead(accountId: number, id: string): Promise<void> {
  return api.post(`api/v1/accounts/${accountId}/notifications/${id}/read`).json();
}

export async function markAllAsRead(accountId: number): Promise<void> {
  return api.post(`api/v1/accounts/${accountId}/notifications/read_all`).json();
}

export async function deleteNotification(accountId: number, id: string): Promise<void> {
  return api.delete(`api/v1/accounts/${accountId}/notifications/${id}`).json();
}

export async function deleteAll(accountId: number, type: 'read' | 'all' = 'read'): Promise<void> {
  return api.delete(`api/v1/accounts/${accountId}/notifications`, {
    searchParams: toSearchParams({ type })
  }).json();
}
