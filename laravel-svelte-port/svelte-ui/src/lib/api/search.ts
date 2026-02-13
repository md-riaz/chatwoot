/**
 * Search API Client
 * Handles global search across conversations, contacts, and messages
 */

import { api, toSearchParams } from './client';

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

interface AccountSearchResponse {
  data?: Record<string, Record<string, unknown>[]>;
  results?: SearchResult[];
  meta?: {
    total?: number;
    totalTypes?: number;
  };
}

function toSearchResult(
  type: SearchResult['type'],
  item: Record<string, unknown>
): SearchResult {
  const id = Number(item.id ?? 0);
  const conversationId = Number(
    item.conversationId ?? item.conversation_id ?? item.id ?? 0
  );

  return {
    id,
    type,
    title:
      String(
        item.title ??
          item.name ??
          item.subject ??
          item.displayId ??
          item.display_id ??
          item.content ??
          item.id ??
          ''
      ) || `#${id}`,
    description:
      (item.description as string | undefined) ??
      (item.content as string | undefined) ??
      (item.email as string | undefined),
    conversationId:
      type === 'contact' ? undefined : conversationId || undefined,
    contactId:
      type === 'contact'
        ? id
        : Number(item.contactId ?? item.contact_id ?? 0) || undefined,
    createdAt: String(
      item.createdAt ?? item.created_at ?? new Date().toISOString()
    ),
  };
}

function normalizeSearchResponse(
  response: AccountSearchResponse,
  page: number,
  perPage: number
): SearchResponse {
  if (Array.isArray(response.results)) {
    return {
      results: response.results,
      meta: {
        total: response.meta?.total ?? response.results.length,
        page,
        perPage,
      },
    };
  }

  const grouped = response.data ?? {};
  const mapping: Array<{ key: string; type: SearchResult['type'] }> = [
    { key: 'conversations', type: 'conversation' },
    { key: 'contacts', type: 'contact' },
    { key: 'messages', type: 'message' },
  ];

  const results = mapping.flatMap(({ key, type }) => {
    const items = (grouped[key] ?? []) as Record<string, unknown>[];
    return items.map(item => toSearchResult(type, item));
  });

  return {
    results,
    meta: {
      total: response.meta?.total ?? results.length,
      page,
      perPage,
    },
  };
}

/**
 * Global search across all entities
 */
export async function search(
  accountId: number,
  query: string,
  filters: SearchFilters = {},
  page: number = 1
): Promise<SearchResponse> {
  const perPage = 15;
  const response = await api
    .get(`api/v1/accounts/${accountId}/search`, {
      searchParams: toSearchParams({
        q: query,
        ...filters,
        page,
        perPage,
      }),
    })
    .json<AccountSearchResponse>();

  return normalizeSearchResponse(response, page, perPage);
}

/**
 * Search conversations specifically
 */
export async function searchConversations(
  accountId: number,
  query: string,
  filters: Omit<SearchFilters, 'type'> = {},
  page: number = 1
): Promise<SearchResponse> {
  return search(accountId, query, { ...filters, type: 'conversation' }, page);
}

/**
 * Search contacts specifically
 */
export async function searchContacts(
  accountId: number,
  query: string,
  page: number = 1
): Promise<SearchResponse> {
  return search(accountId, query, { type: 'contact' }, page);
}

/**
 * Search messages specifically
 */
export async function searchMessages(
  accountId: number,
  query: string,
  filters: Omit<SearchFilters, 'type'> = {},
  page: number = 1
): Promise<SearchResponse> {
  return search(accountId, query, { ...filters, type: 'message' }, page);
}
