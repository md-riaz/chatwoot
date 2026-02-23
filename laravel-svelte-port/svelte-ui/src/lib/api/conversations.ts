/**
 * Conversations API Client
 * Replaces app/javascript/dashboard/api/inbox/conversation.js
 */

import { api, toSearchParams } from './client';
import type { PaginatedResponse } from './types';

/**
 * Conversation status type
 */
export type ConversationStatus = 'open' | 'resolved' | 'pending' | 'snoozed';

/**
 * Conversation priority
 */
export type ConversationPriority = 'urgent' | 'high' | 'medium' | 'low' | null;

/**
 * Message type
 */
export interface Message {
  id: number;
  content: string;
  messageType: number; // 0: incoming, 1: outgoing, 2: activity
  contentType: string;
  contentAttributes: Record<string, any>;
  createdAt: number;
  private: boolean;
  status: string;
  sourceId: string;
  sender?: {
    id: number;
    name: string;
    type: string;
  };
  attachments?: any[];
  conversation?: any;
}

/**
 * Conversation interface
 */
export interface Conversation {
  id: number;
  accountId: number;
  inboxId: number;
  contactId: number;
  assigneeId?: number;
  teamId?: number;
  displayId: number;
  status: ConversationStatus;
  priority?: ConversationPriority;
  uuid?: string;
  customAttributes: Record<string, any>;
  firstReplyCreatedAt?: string | null;
  lastActivityAt: string | null;
  waitingSince?: string | null;
  snoozedUntil?: string | null;
  createdAt: string | null;
  updatedAt: string | null;
  // Client-side properties
  agentLastSeenAt?: number;
  canReply?: boolean;
  contactLastSeenAt?: number;
  labels?: string[];
  muted?: boolean;
  timestamp?: number;
  unreadCount?: number;
  additionalAttributes?: Record<string, any>;
  // Relationships (when loaded)
  contact?: any;
  inbox?: any;
  assignee?: any;
  messagesCount?: number;
  messages?: Message[];
  allMessagesLoaded?: boolean;
  dataFetched?: boolean;
  // Meta info (client-side computed)
  meta?: {
    sender?: {
      id: number;
      name: string;
      email: string;
      phoneNumber?: string;
      avatarUrl?: string;
      customAttributes?: Record<string, any>;
    };
    assignee?: {
      id: number;
      name: string;
      avatarUrl?: string;
      availabilityStatus?: string;
    };
    team?: {
      id: number;
      name: string;
    };
    channel?: string;
  };
}

interface ConversationsApiResponse {
  data?: any[];
  meta?: {
    totalPages?: number;
    count?: number;
    currentPage?: number;
    total?: number;
  };
}

const STATUS_MAP: Record<number, ConversationStatus> = {
  0: 'open',
  1: 'resolved',
  2: 'pending',
  3: 'snoozed',
};

const PRIORITY_MAP: Record<number, ConversationPriority> = {
  0: null,
  1: 'low',
  2: 'medium',
  3: 'high',
  4: 'urgent',
};

function toSafeNumber(value: unknown): number {
  if (value === null || value === undefined || value === '') {
    return 0;
  }

  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : 0;
}

function toConversationTimestamp(value: unknown): string | null {
  if (typeof value === 'number') {
    if (!Number.isFinite(value)) return null;
    return new Date(value * 1000).toISOString();
  }

  if (typeof value === 'string') {
    const normalized = value.trim();
    return normalized.length > 0 ? normalized : null;
  }

  return null;
}

function normalizeConversationStatus(value: unknown): ConversationStatus {
  if (typeof value === 'string') {
    const normalized = value.trim().toLowerCase();
    if (
      normalized === 'open' ||
      normalized === 'resolved' ||
      normalized === 'pending' ||
      normalized === 'snoozed'
    ) {
      return normalized;
    }

    const numericValue = Number(normalized);
    if (Number.isFinite(numericValue) && STATUS_MAP[numericValue]) {
      return STATUS_MAP[numericValue];
    }
  }

  if (typeof value === 'number' && Number.isFinite(value) && STATUS_MAP[value]) {
    return STATUS_MAP[value];
  }

  return 'open';
}

function normalizeConversationPriority(value: unknown): ConversationPriority {
  if (value === null || value === undefined || value === '') {
    return null;
  }

  if (typeof value === 'string') {
    const normalized = value.trim().toLowerCase();
    if (
      normalized === 'low' ||
      normalized === 'medium' ||
      normalized === 'high' ||
      normalized === 'urgent'
    ) {
      return normalized;
    }

    const numericValue = Number(normalized);
    if (Number.isFinite(numericValue) && numericValue in PRIORITY_MAP) {
      return PRIORITY_MAP[numericValue];
    }
  }

  if (typeof value === 'number' && Number.isFinite(value) && value in PRIORITY_MAP) {
    return PRIORITY_MAP[value];
  }

  return null;
}

export function transformConversationFromApi(
  rawConversation: any
): Conversation {
  const lastActivityAt = toConversationTimestamp(
    rawConversation.lastActivityAt ?? rawConversation.timestamp
  );
  const unixTimestamp = lastActivityAt
    ? Math.floor(new Date(lastActivityAt).getTime() / 1000)
    : undefined;

  return {
    ...rawConversation,
    id: toSafeNumber(rawConversation.id),
    accountId: toSafeNumber(rawConversation.accountId),
    inboxId: toSafeNumber(rawConversation.inboxId),
    contactId: toSafeNumber(rawConversation.contactId),
    displayId: toSafeNumber(rawConversation.displayId),
    assigneeId: rawConversation.assigneeId ? toSafeNumber(rawConversation.assigneeId) : undefined,
    teamId: rawConversation.teamId ? toSafeNumber(rawConversation.teamId) : undefined,
    status: normalizeConversationStatus(rawConversation.status),
    priority: normalizeConversationPriority(rawConversation.priority),
    lastActivityAt,
    createdAt: toConversationTimestamp(rawConversation.createdAt),
    updatedAt: toConversationTimestamp(rawConversation.updatedAt),
    firstReplyCreatedAt: toConversationTimestamp(rawConversation.firstReplyCreatedAt),
    waitingSince: toConversationTimestamp(rawConversation.waitingSince),
    snoozedUntil: toConversationTimestamp(rawConversation.snoozedUntil),
    timestamp:
      rawConversation.timestamp && Number.isFinite(Number(rawConversation.timestamp))
        ? Number(rawConversation.timestamp)
        : unixTimestamp,
  };
}

function extractConversationPayload(response: ConversationsApiResponse): any[] {
  return response?.data || [];
}

/**
 * Conversation list params
 */
export interface ConversationListParams {
  accountId: number;
  inboxId?: number;
  teamId?: number;
  label?: string;
  mentioned?: boolean;
  unattended?: boolean;
  status?: ConversationStatus;
  assigneeType?: 'me' | 'unassigned' | 'all';
  page?: number;
  sortBy?: 'latest' | 'oldest' | 'unread' | 'priority';
  perPage?: number;
  [key: string]: string | number | boolean | string[] | undefined;
}

export interface ConversationMetaCounts {
  allCount: number;
  openCount: number;
  resolvedCount: number;
  pendingCount: number;
  snoozedCount: number;
  unassignedCount: number;
}

/**
 * Conversation filter params
 */
export interface ConversationFilterParams {
  accountId: number;
  payload: any[];
  page?: number;
}

/**
 * Get list of conversations
 */
export async function getConversations(
  params: ConversationListParams
): Promise<PaginatedResponse<Conversation>> {
  const { accountId, ...queryParams } = params;

  const response = await api
    .get(`api/v1/accounts/${accountId}/conversations`, {
      searchParams: toSearchParams(queryParams),
    })
    .json<ConversationsApiResponse>();

  const conversations = extractConversationPayload(response).map(
    transformConversationFromApi
  );
  const meta = response?.meta || {};

  return {
    data: conversations,
    meta: {
      currentPage: queryParams.page || 1,
      nextPage:
        (queryParams.page || 1) < (meta.totalPages || 1)
          ? (queryParams.page || 1) + 1
          : null,
      prevPage:
        (queryParams.page || 1) > 1 ? (queryParams.page || 1) - 1 : null,
      totalPages: meta.totalPages || 1,
      totalCount: meta.count || 0,
    },
  };
}

/**
 * Get conversation list meta counts.
 */
export async function getConversationsMeta(
  accountId: number,
  params: Partial<ConversationListParams> = {}
): Promise<ConversationMetaCounts> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/conversations/meta`, {
      searchParams: toSearchParams(params),
    })
    .json<{ meta?: Partial<ConversationMetaCounts> }>();

  const meta = response.meta || {};
  return {
    allCount: Number(meta.allCount || 0),
    openCount: Number(meta.openCount || 0),
    resolvedCount: Number(meta.resolvedCount || 0),
    pendingCount: Number(meta.pendingCount || 0),
    snoozedCount: Number(meta.snoozedCount || 0),
    unassignedCount: Number(meta.unassignedCount || 0),
  };
}

/**
 * Get filtered conversations
 */
export async function filterConversations(
  params: ConversationFilterParams
): Promise<{ payload: Conversation[] }> {
  const { accountId, payload, page = 1 } = params;

  const response = await api
    .post(`api/v1/accounts/${accountId}/conversations/filter`, {
      json: {
        payload,
        page,
      },
    })
    .json<ConversationsApiResponse>();

  return {
    payload: extractConversationPayload(response).map(
      transformConversationFromApi
    ),
  };
}

/**
 * Get single conversation by ID
 */
export async function getConversation(
  accountId: number,
  conversationId: number
): Promise<Conversation> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/conversations/${conversationId}`)
    .json<any>();

  // Unwrap data/payload if present
  const data = response.data || response.payload || response;
  return transformConversationFromApi(data);
}

export async function getConversationsByContact(
  accountId: number,
  contactId: number,
  page = 1
): Promise<Conversation[]> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/contacts/${contactId}/conversations`, {
      searchParams: toSearchParams({ page }),
    })
    .json<ConversationsApiResponse>();

  return extractConversationPayload(response).map(transformConversationFromApi);
}

/**
 * Create a new conversation
 */
export async function createConversation(
  accountId: number,
  params: {
    sourceId: string;
    inboxId: number;
    contactId?: number;
    additionalAttributes?: Record<string, any>;
    customAttributes?: Record<string, any>;
    status?: ConversationStatus;
    assigneeId?: number;
    teamId?: number;
  }
): Promise<Conversation> {
  const raw = await api
    .post(`api/v1/accounts/${accountId}/conversations`, {
      json: params,
    })
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Update conversation
 */
export async function updateConversation(
  accountId: number,
  conversationId: number,
  params: Partial<{
    status: ConversationStatus;
    assigneeId: number | null;
    teamId: number | null;
    priority: ConversationPriority;
    snoozedUntil: number | null;
    customAttributes: Record<string, any>;
  }>
): Promise<Conversation> {
  const raw = await api
    .patch(`api/v1/accounts/${accountId}/conversations/${conversationId}`, {
      json: params,
    })
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Toggle conversation status (open <-> resolved)
 */
export async function toggleStatus(
  accountId: number,
  conversationId: number
): Promise<Conversation> {
  const raw = await api
    .post(
      `api/v1/accounts/${accountId}/conversations/${conversationId}/toggle_status`
    )
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Assign agent to conversation
 */
export async function assignAgent(
  accountId: number,
  conversationId: number,
  assigneeId: number | null
): Promise<Conversation> {
  const raw = await api
    .post(
      `api/v1/accounts/${accountId}/conversations/${conversationId}/assign`,
      {
        json: { assigneeId },
      }
    )
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Assign team to conversation
 */
export async function assignTeam(
  accountId: number,
  conversationId: number,
  teamId: number | null
): Promise<Conversation> {
  const raw = await api
    .patch(
      `api/v1/accounts/${accountId}/conversations/${conversationId}`,
      {
        json: { teamId },
      }
    )
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Mute conversation
 */
export async function muteConversation(
  accountId: number,
  conversationId: number
): Promise<Conversation> {
  const raw = await api
    .post(`api/v1/accounts/${accountId}/conversations/${conversationId}/mute`)
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Unmute conversation
 */
export async function unmuteConversation(
  accountId: number,
  conversationId: number
): Promise<Conversation> {
  const raw = await api
    .post(`api/v1/accounts/${accountId}/conversations/${conversationId}/unmute`)
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Get conversation labels
 */
export async function getLabels(
  accountId: number,
  conversationId: number
): Promise<string[]> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/conversations/${conversationId}/labels`)
    .json<{ payload: string[] }>();

  return response.payload;
}

/**
 * Update conversation labels
 */
export async function updateLabels(
  accountId: number,
  conversationId: number,
  labels: string[]
): Promise<string[]> {
  const response = await api
    .post(
      `api/v1/accounts/${accountId}/conversations/${conversationId}/labels`,
      {
        json: { labels },
      }
    )
    .json<{ payload: string[] }>();

  return response.payload;
}

/**
 * Get conversation attachments
 */
export async function getAllAttachments(
  accountId: number,
  conversationId: number
): Promise<any[]> {
  const response = await api
    .get(
      `api/v1/accounts/${accountId}/conversations/${conversationId}/attachments`
    )
    .json<{ payload: any[] }>();

  return response.payload;
}

/**
 * Update conversation custom attributes
 */
export async function updateCustomAttributes(
  accountId: number,
  conversationId: number,
  customAttributes: Record<string, any>
): Promise<Conversation> {
  const raw = await api
    .post(
      `api/v1/accounts/${accountId}/conversations/${conversationId}/custom_attributes`,
      {
        json: { customAttributes },
      }
    )
    .json<Conversation>();

  return transformConversationFromApi(raw);
}

/**
 * Mark messages as read
 */
export async function markMessagesRead(
  accountId: number,
  conversationId: number,
  beforeId?: number
): Promise<{ agentLastSeenAt: number; unreadCount: number }> {
  const response = await api
    .post(
      `api/v1/accounts/${accountId}/conversations/${conversationId}/update_last_seen`,
      {
        json: { agentLastSeenAt: Math.floor(Date.now() / 1000) },
      }
    )
    .json<{ agentLastSeenAt: number; unreadCount: number }>();

  return response;
}
