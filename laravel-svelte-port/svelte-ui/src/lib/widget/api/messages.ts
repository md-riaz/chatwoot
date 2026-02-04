/**
 * Widget Messages API client
 * Handles widget-specific message operations including sync functionality
 * Replaces Vue's app/javascript/widget/api/conversation.js
 */

import { api } from '$lib/api/client';

// Widget-specific message interface
export interface WidgetMessage {
  id: number;
  content: string;
  messageType: number; // 0: incoming, 1: outgoing
  contentType?: string;
  contentAttributes?: Record<string, any>;
  createdAt: number; // Unix timestamp
  conversationId: number;
  attachments?: WidgetAttachment[];
  sender?: WidgetSender;
}

export interface WidgetAttachment {
  id: number;
  fileType: string;
  dataUrl: string;
}

export interface WidgetSender {
  id: number;
  name: string;
  avatarUrl?: string;
  type: 'contact' | 'agent';
}

export interface WidgetMessageResponse {
  data: WidgetMessage[];
  meta: {
    contact: {
      id: number;
      name: string;
      email: string;
    };
    contactLastSeenAt?: number;
    hasMore?: boolean;
  };
}

export interface CreateWidgetMessageParams {
  content?: string;
  echoId?: string;
  attachments?: File[];
  replyTo?: number;
}

/**
 * Get messages for widget conversation
 * Supports both before/after parameters for pagination and sync
 */
export async function getWidgetMessages(params: {
  before?: number;
  after?: number;
} = {}): Promise<WidgetMessageResponse> {
  const searchParams = new URLSearchParams(window.location.search);
  
  if (params.before !== undefined) {
    searchParams.append('before', params.before.toString());
  }
  
  if (params.after !== undefined) {
    searchParams.append('after', params.after.toString());
  }
  
  const response = await api.get(`widget/messages?${searchParams.toString()}`);
  return response.json<WidgetMessageResponse>();
}

/**
 * Get messages since a specific message ID (for WebSocket sync)
 * Used to sync missed messages after reconnection
 */
export async function getWidgetMessagesSince(lastMessageId: number): Promise<WidgetMessage[]> {
  const result = await getWidgetMessages({ after: lastMessageId });
  return result.data;
}

/**
 * Create a new widget message
 */
export async function createWidgetMessage(params: CreateWidgetMessageParams): Promise<WidgetMessage> {
  const searchParams = new URLSearchParams(window.location.search);
  
  // Handle file attachments
  if (params.attachments && params.attachments.length > 0) {
    const formData = new FormData();
    
    if (params.content) {
      formData.append('message[content]', params.content);
    }
    
    if (params.echoId) {
      formData.append('message[echo_id]', params.echoId);
    }
    
    if (params.replyTo) {
      formData.append('message[reply_to]', params.replyTo.toString());
    }
    
    params.attachments.forEach(file => {
      formData.append('message[attachments][]', file);
    });
    
    const response = await api.post(`widget/messages?${searchParams.toString()}`, {
      body: formData
    });
    
    return response.json<WidgetMessage>();
  }
  
  // Text-only message
  const response = await api.post(`widget/messages?${searchParams.toString()}`, {
    json: {
      message: {
        content: params.content,
        echo_id: params.echoId,
        reply_to: params.replyTo,
        timestamp: new Date().toString(),
        referer_url: window.referrerURL || '',
      }
    }
  });
  
  return response.json<WidgetMessage>();
}

/**
 * Update widget message (for feedback/rating)
 */
export async function updateWidgetMessage(
  messageId: number,
  params: {
    submittedEmail?: string;
    submittedValues?: Record<string, any>;
  }
): Promise<WidgetMessage> {
  const searchParams = new URLSearchParams(window.location.search);
  
  const response = await api.patch(`widget/messages/${messageId}?${searchParams.toString()}`, {
    json: {
      submitted_email: params.submittedEmail,
      submitted_values: params.submittedValues,
    }
  });
  
  return response.json<WidgetMessage>();
}

/**
 * Toggle typing status for widget
 */
export async function toggleWidgetTyping(typingStatus: 'on' | 'off'): Promise<void> {
  const searchParams = new URLSearchParams(window.location.search);
  
  await api.post(`widget/conversations/toggle_typing?${searchParams.toString()}`, {
    json: {
      typing_status: typingStatus
    }
  });
}

/**
 * Update contact's last seen timestamp
 */
export async function updateContactLastSeen(lastSeen: number): Promise<void> {
  const searchParams = new URLSearchParams(window.location.search);
  
  await api.post(`widget/conversations/update_last_seen?${searchParams.toString()}`, {
    json: {
      contact_last_seen_at: lastSeen
    }
  });
}