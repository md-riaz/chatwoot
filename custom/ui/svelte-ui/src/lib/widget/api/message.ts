/**
 * Message API
 * 
 * API methods for managing widget messages.
 */

import { getWidgetApi } from './client';
import type { Message, CreateMessageParams, ApiResponse } from './types';

/**
 * Get messages for a conversation
 */
export async function getMessages(conversationId: number): Promise<Message[]> {
  const api = getWidgetApi();
  const response = await api
    .get(`conversations/${conversationId}/messages`)
    .json<ApiResponse<Message[]>>();
  return response.data;
}

/**
 * Create a new message
 */
export async function createMessage(params: CreateMessageParams): Promise<Message> {
  const api = getWidgetApi();
  const { conversationId, content, attachments, echoId } = params;

  // If there are attachments, use FormData
  if (attachments && attachments.length > 0) {
    const formData = new FormData();
    formData.append('content', content);
    if (echoId) {
      formData.append('echo_id', echoId);
    }

    attachments.forEach((file) => {
      formData.append('attachments[]', file);
    });

    const response = await api
      .post(`conversations/${conversationId}/messages`, { body: formData })
      .json<ApiResponse<Message>>();
    return response.data;
  }

  // Otherwise, use JSON
  const response = await api
    .post(`conversations/${conversationId}/messages`, {
      json: { content, echoId },
    })
    .json<ApiResponse<Message>>();
  return response.data;
}

/**
 * Delete a message (if supported by API)
 */
export async function deleteMessage(
  conversationId: number,
  messageId: number
): Promise<void> {
  const api = getWidgetApi();
  await api.delete(`conversations/${conversationId}/messages/${messageId}`);
}
