/**
 * Conversation API
 * 
 * API methods for managing widget conversations.
 */

import { getWidgetApi } from './client';
import type {
  Conversation,
  CreateConversationParams,
  ApiResponse,
} from './types';

/**
 * Create a new conversation
 */
export async function createConversation(
  params: CreateConversationParams
): Promise<Conversation> {
  const api = getWidgetApi();
  const response = await api.post('conversations', { json: params }).json<ApiResponse<Conversation>>();
  return response.data;
}

/**
 * Get conversation details
 */
export async function getConversation(id: number): Promise<Conversation> {
  const api = getWidgetApi();
  const response = await api.get(`conversations/${id}`).json<ApiResponse<Conversation>>();
  return response.data;
}

/**
 * Update conversation status
 */
export async function updateConversationStatus(
  id: number,
  status: 'open' | 'resolved'
): Promise<Conversation> {
  const api = getWidgetApi();
  const response = await api
    .patch(`conversations/${id}`, { json: { status } })
    .json<ApiResponse<Conversation>>();
  return response.data;
}

/**
 * Mark conversation messages as read
 */
export async function markMessagesRead(conversationId: number): Promise<void> {
  const api = getWidgetApi();
  await api.post(`conversations/${conversationId}/messages/read`).json();
}
