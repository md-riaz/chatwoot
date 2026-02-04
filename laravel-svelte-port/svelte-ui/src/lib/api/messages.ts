/**
 * Messages API client
 * Handles message operations (create, delete, retry, pagination, translation)
 * Replaces Vue's app/javascript/dashboard/api/inbox/message.js
 */

import { api } from './client';
import type { PaginatedResponse } from './types';

// Types
export interface Message {
  id: number;
  content: string;
  messageType: number; // 0: incoming, 1: outgoing, 2: activity, 3: template
  createdAt: string;
  private: boolean;
  status?: 'sent' | 'delivered' | 'read' | 'failed' | 'progress';
  sourceId?: string | null;
  contentType?: 'text' | 'input_select' | 'cards' | 'form' | 'article';
  contentAttributes?: Record<string, any>;
  sender?: MessageSender;
  attachments?: MessageAttachment[];
  conversationId: number;
  echoId?: string;
  translatedContent?: string | null;
  inbox?: { id: number; name: string };
}

export interface MessageSender {
  id: number;
  name: string;
  avatarUrl?: string;
  type: 'contact' | 'user';
  email?: string;
  thumbnail?: string;
}

export interface MessageAttachment {
  id: number;
  messageId: number;
  fileType: 'image' | 'video' | 'audio' | 'file';
  accountId: number;
  extension?: string;
  dataUrl: string;
  thumbUrl?: string;
  fileSize?: number;
}

export interface CreateMessageParams {
  conversationId: number;
  message?: string;
  private?: boolean;
  contentAttributes?: Record<string, any>;
  echoId?: string;
  files?: File[];
  ccEmails?: string;
  bccEmails?: string;
  toEmails?: string;
  templateParams?: Record<string, any>;
}

export interface GetMessagesParams {
  conversationId: number;
  before?: number; // Message ID to fetch messages before
  after?: number; // Message ID to fetch messages after (for gaps)
}

export interface TranslateMessageParams {
  conversationId: number;
  messageId: number;
  targetLanguage: string;
}

/**
 * Build payload for creating a message
 * Handles both text messages and messages with file attachments
 */
export function buildCreatePayload(params: CreateMessageParams): FormData | Record<string, any> {
  const {
    message,
    private: isPrivate = false,
    contentAttributes,
    echoId,
    files,
    ccEmails = '',
    bccEmails = '',
    toEmails = '',
    templateParams,
  } = params;

  // If files are attached, use FormData
  if (files && files.length > 0) {
    const payload = new FormData();
    
    if (message) {
      payload.append('content', message);
    }
    
    files.forEach(file => {
      payload.append('attachments[]', file);
    });
    
    payload.append('private', isPrivate.toString());
    
    if (echoId) {
      payload.append('echo_id', echoId);
    }
    
    payload.append('cc_emails', ccEmails);
    payload.append('bcc_emails', bccEmails);
    
    if (toEmails) {
      payload.append('to_emails', toEmails);
    }
    
    if (contentAttributes) {
      payload.append('content_attributes', JSON.stringify(contentAttributes));
    }
    
    return payload;
  }

  // Text-only message
  return {
    content: message,
    private: isPrivate,
    echo_id: echoId,
    content_attributes: contentAttributes,
    cc_emails: ccEmails,
    bcc_emails: bccEmails,
    to_emails: toEmails,
    template_params: templateParams,
  };
}

/**
 * Create a new message in a conversation
 */
export async function createMessage(params: CreateMessageParams): Promise<Message> {
  const { conversationId, ...rest } = params;
  const payload = buildCreatePayload({ conversationId, ...rest });
  
  const isFormData = payload instanceof FormData;
  
  const response = await api.post(
    `conversations/${conversationId}/messages`,
    isFormData ? { body: payload } : { json: payload }
  );
  
  return response.json<Message>();
}

/**
 * Delete a message from a conversation
 */
export async function deleteMessage(
  conversationId: number,
  messageId: number
): Promise<void> {
  await api.delete(`conversations/${conversationId}/messages/${messageId}`);
}

/**
 * Retry sending a failed message
 */
export async function retryMessage(
  conversationId: number,
  messageId: number
): Promise<Message> {
  const response = await api.post(`conversations/${conversationId}/messages/${messageId}/retry`);
  return response.json<Message>();
}

/**
 * Get previous messages for a conversation (pagination)
 * Used for loading message history
 */
export async function getPreviousMessages(
  params: GetMessagesParams
): Promise<{ messages: Message[]; meta: PaginatedResponse<Message>['meta'] }> {
  const { conversationId, before, after } = params;
  
  const searchParams = new URLSearchParams();
  
  if (before !== undefined) {
    searchParams.append('before', before.toString());
  }
  
  // Include 'after' if specified and different from 'before' (for gap filling)
  if (after !== undefined && after !== before) {
    searchParams.append('after', after.toString());
  }
  
  const response = await api.get(
    `conversations/${conversationId}/messages?${searchParams.toString()}`
  );
  
  const data = await response.json<{
    payload: Message[];
    meta: PaginatedResponse<Message>['meta'];
  }>();
  
  return {
    messages: data.payload || [],
    meta: data.meta,
  };
}

/**
 * Get messages for a conversation (wrapper for getPreviousMessages)
 */
export async function getMessages(conversationId: number): Promise<Message[]> {
  const result = await getPreviousMessages({ conversationId });
  return result.messages;
}

/**
 * Get messages since a specific message ID (for WebSocket sync)
 * Used to sync missed messages after reconnection
 */
export async function getMessagesSince(
  conversationId: number,
  lastMessageId: number
): Promise<Message[]> {
  const result = await getPreviousMessages({ 
    conversationId, 
    after: lastMessageId 
  });
  return result.messages;
}

/**
 * Translate a message to target language
 */
export async function translateMessage(
  params: TranslateMessageParams
): Promise<{ translatedContent: string }> {
  const { conversationId, messageId, targetLanguage } = params;
  
  const response = await api.post(
    `conversations/${conversationId}/messages/${messageId}/translate`,
    {
      json: {
        targetLanguage, // Will be transformed to target_language by API client
      },
    }
  );
  
  return response.json<{ translatedContent: string }>();
}
