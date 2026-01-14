/**
 * Search API Client
 * Handles global search across conversations, contacts, and messages
 */

import api, { toSearchParams } from './client';

export interface SearchResult {
  id: number;
  type: 'conversation' | 'contact' | 'message';
  title: string;
  description?: string;
  thumbnail?: string;
  metadata?: Record<string, any>;
  conversationId?: number;
  contactId?: number;
  createdAt: string;
}

export interface SearchFilters {
  type?: 'conversation' | 'contact' | 'message' | 'all';
  status?: string;
  assigneeId?: number;
  inboxId?: number;
  teamId?: number;
  labelIds?: number[];
  dateFrom?: string;
  dateTo?: string;
}

export interface SearchResponse {
  results: SearchResult[];
  meta: {
    total: number;
    page: number;
    perPage: number;
  };
}

/**
 * Global search across all entities
 */
export async function search(
  query: string,
  filters: SearchFilters = {},
  page: number = 1
): Promise<SearchResponse> {
  return api.get('api/v1/search', {
    searchParams: toSearchParams({
      q: query,
      ...filters,
      page
    })
  }).json();
}

/**
 * Search conversations specifically
 */
export async function searchConversations(
  query: string,
  filters: Omit<SearchFilters, 'type'> = {},
  page: number = 1
): Promise<SearchResponse> {
  return search(query, { ...filters, type: 'conversation' }, page);
}

/**
 * Search contacts specifically
 */
export async function searchContacts(
  query: string,
  page: number = 1
): Promise<SearchResponse> {
  return search(query, { type: 'contact' }, page);
}

/**
 * Search messages specifically
 */
export async function searchMessages(
  query: string,
  filters: Omit<SearchFilters, 'type'> = {},
  page: number = 1
): Promise<SearchResponse> {
  return search(query, { ...filters, type: 'message' }, page);
}
