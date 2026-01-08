/**
 * Widget API Types
 * 
 * TypeScript interfaces for the widget API responses and requests.
 * Widget uses public API endpoints with website token authentication.
 */

export interface WidgetConfig {
  websiteToken: string;
  widgetColor: string;
  position: 'left' | 'right';
  locale: string;
  enabledFeatures: string[];
  preChatFormEnabled: boolean;
  preChatFormOptions: PreChatFormOptions;
  replyTime: string;
  businessName: string;
  businessDescription: string;
  businessHours: BusinessHours;
}

export interface PreChatFormOptions {
  requireEmail: boolean;
  requireName: boolean;
  requirePhoneNumber: boolean;
  preChatMessage: string;
}

export interface BusinessHours {
  enabled: boolean;
  timezone: string;
  schedule: Record<string, { enabled: boolean; from: string; to: string }>;
}

export interface Contact {
  id?: number;
  name?: string;
  email?: string;
  phoneNumber?: string;
  avatarUrl?: string;
  customAttributes?: Record<string, any>;
}

export interface Conversation {
  id: number;
  inboxId: number;
  contactId: number;
  status: 'open' | 'resolved' | 'pending' | 'snoozed';
  unreadCount: number;
  lastMessageAt: string;
  createdAt: string;
  messages?: Message[];
  contact?: Contact;
}

export interface Message {
  id: number;
  content: string;
  messageType: 0 | 1 | 2; // 0: incoming (agent), 1: outgoing (visitor), 2: activity
  createdAt: string;
  conversationId: number;
  attachments?: Attachment[];
  sender?: MessageSender;
  contentAttributes?: Record<string, any>;
  read?: boolean;
}

export interface MessageSender {
  id: number;
  name: string;
  avatarUrl?: string;
  type: 'agent' | 'contact';
}

export interface Attachment {
  id: number;
  fileName: string;
  fileType: string;
  fileSize: number;
  dataUrl: string;
  thumbUrl?: string;
}

export interface Agent {
  id: number;
  name: string;
  avatarUrl?: string;
  availabilityStatus: 'online' | 'offline' | 'busy';
  isTyping?: boolean;
}

export interface Campaign {
  id: number;
  title: string;
  message: string;
  sender?: Agent;
  triggerRules: Record<string, any>;
  enabled: boolean;
}

export interface Article {
  id: number;
  title: string;
  content: string;
  description: string;
  slug: string;
  categoryId: number;
  authorId: number;
  views: number;
  createdAt: string;
  updatedAt: string;
}

export interface CreateConversationParams {
  contact: {
    name?: string;
    email?: string;
    phoneNumber?: string;
  };
  customAttributes?: Record<string, any>;
  message?: string;
}

export interface CreateMessageParams {
  conversationId: number;
  content: string;
  attachments?: File[];
  echoId?: string;
}

export interface EventData {
  name: string;
  customAttributes?: Record<string, any>;
}

export interface ApiResponse<T> {
  data: T;
  meta?: {
    count?: number;
    currentPage?: number;
    totalPages?: number;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  statusCode?: number;
}
