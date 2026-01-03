/**
 * Mock data factories for testing
 * These functions create mock objects with sensible defaults that can be overridden
 */

export interface User {
  id: number;
  email: string;
  name: string;
  avatarUrl?: string;
  role: string;
  confirmed: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface CurrentUser extends User {
  accounts: UserAccount[];
  accountId: number;
  availabilityStatus: 'online' | 'offline' | 'busy';
  autoOffline: boolean;
  customAttributes: Record<string, any>;
  uiSettings: {
    displayRichContent: boolean;
    enterToSendMessage: boolean;
  };
}

export interface UserAccount {
  id: number;
  name: string;
  role: string;
  activeAt?: string;
}

export interface Conversation {
  id: number;
  accountId: number;
  inboxId: number;
  status: 'open' | 'resolved' | 'pending' | 'snoozed';
  priority: 'urgent' | 'high' | 'medium' | 'low' | null;
  assigneeId: number | null;
  teamId: number | null;
  contactId: number;
  contact: Contact;
  messages: Message[];
  labels: string[];
  customAttributes: Record<string, any>;
  muted: boolean;
  unreadCount: number;
  lastActivityAt: string;
  createdAt: string;
  updatedAt: string;
}

export interface Message {
  id: number;
  content: string;
  message_type: number; // 0 = outgoing, 1 = incoming
  contentType: 'text' | 'input_select' | 'cards' | 'form' | 'article';
  contentAttributes: Record<string, any>;
  created_at: number; // Unix timestamp in seconds
  private: boolean;
  attachments: Attachment[];
  sender: User | null;
  conversationId: number;
  accountId: number;
  inboxId: number;
  status: 'sent' | 'delivered' | 'read' | 'failed';
  sourceId: string | null;
}

export interface Contact {
  id: number;
  name: string;
  email?: string;
  phoneNumber?: string;
  identifier?: string;
  avatarUrl?: string;
  customAttributes: Record<string, any>;
  availabilityStatus?: 'online' | 'offline' | 'busy';
  conversationsCount: number;
  lastActivityAt?: string;
  createdAt: string;
  updatedAt: string;
}

export interface Attachment {
  id: number;
  file_type: string;
  file_name?: string;
  accountId: number;
  extension: string | null;
  data_url: string;
  thumb_url?: string;
  file_size: number;
}

export interface Inbox {
  id: number;
  name: string;
  channelType: string;
  channelId: number;
  avatarUrl: string | null;
  webhookUrl: string | null;
  greetingEnabled: boolean;
  greetingMessage: string;
  emailAddress: string;
  workingHoursEnabled: boolean;
  enableAutoAssignment: boolean;
  allowMessagesAfterResolved: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface Team {
  id: number;
  name: string;
  description: string;
  allowAutoAssign: boolean;
  accountId: number;
  isDefault: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface Label {
  id: number;
  title: string;
  description: string;
  color: string;
  showOnSidebar: boolean;
  accountId: number;
  createdAt: string;
  updatedAt: string;
}

/**
 * Create a mock user with default values
 */
export function createMockUser(overrides?: Partial<User>): User {
  return {
    id: 1,
    email: 'test@example.com',
    name: 'Test User',
    avatarUrl: 'https://example.com/avatar.jpg',
    role: 'agent',
    confirmed: true,
    createdAt: '2024-01-01T00:00:00.000Z',
    updatedAt: '2024-01-01T00:00:00.000Z',
    ...overrides
  };
}

/**
 * Create a mock current user with accounts
 */
export function createMockCurrentUser(overrides?: Partial<CurrentUser>): CurrentUser {
  return {
    ...createMockUser(),
    accounts: [
      {
        id: 1,
        name: 'Test Account',
        role: 'administrator',
        activeAt: '2024-01-01T00:00:00.000Z'
      }
    ],
    accountId: 1,
    availabilityStatus: 'online',
    autoOffline: true,
    customAttributes: {},
    uiSettings: {
      displayRichContent: true,
      enterToSendMessage: true
    },
    ...overrides
  };
}

/**
 * Create a mock conversation
 */
export function createMockConversation(overrides?: Partial<Conversation>): Conversation {
  return {
    id: 1,
    accountId: 1,
    inboxId: 1,
    status: 'open',
    priority: 'medium',
    assigneeId: null,
    teamId: null,
    contactId: 1,
    contact: createMockContact(),
    messages: [],
    labels: [],
    customAttributes: {},
    muted: false,
    unreadCount: 0,
    lastActivityAt: new Date().toISOString(),
    createdAt: '2024-01-01T00:00:00.000Z',
    updatedAt: '2024-01-01T00:00:00.000Z',
    ...overrides
  };
}

/**
 * Create a mock message
 */
export function createMockMessage(overrides?: Partial<Message>): Message {
  return {
    id: 1,
    content: 'Test message content',
    message_type: 1, // incoming
    contentType: 'text',
    contentAttributes: {},
    created_at: Math.floor(Date.now() / 1000), // Unix timestamp in seconds
    private: false,
    attachments: [],
    sender: createMockUser(),
    conversationId: 1,
    accountId: 1,
    inboxId: 1,
    status: 'sent',
    sourceId: null,
    ...overrides
  };
}

/**
 * Create a mock contact
 */
export function createMockContact(overrides?: Partial<Contact>): Contact {
  return {
    id: 1,
    name: 'Test Contact',
    email: 'contact@example.com',
    phoneNumber: '+1234567890',
    identifier: 'test-identifier',
    avatarUrl: 'https://example.com/contact-avatar.jpg',
    customAttributes: {},
    availabilityStatus: 'online',
    conversationsCount: 0,
    lastActivityAt: '2024-01-01T00:00:00.000Z',
    createdAt: '2024-01-01T00:00:00.000Z',
    updatedAt: '2024-01-01T00:00:00.000Z',
    ...overrides
  };
}

/**
 * Create a mock inbox
 */
export function createMockInbox(overrides?: Partial<Inbox>): Inbox {
  return {
    id: 1,
    name: 'Test Inbox',
    channelType: 'web_widget',
    channelId: 1,
    avatarUrl: null,
    webhookUrl: null,
    greetingEnabled: true,
    greetingMessage: 'Hello! How can we help?',
    emailAddress: 'support@example.com',
    workingHoursEnabled: false,
    enableAutoAssignment: true,
    allowMessagesAfterResolved: true,
    createdAt: '2024-01-01T00:00:00.000Z',
    updatedAt: '2024-01-01T00:00:00.000Z',
    ...overrides
  };
}

/**
 * Create a mock team
 */
export function createMockTeam(overrides?: Partial<Team>): Team {
  return {
    id: 1,
    name: 'Test Team',
    description: 'A test team',
    allowAutoAssign: true,
    accountId: 1,
    isDefault: false,
    createdAt: '2024-01-01T00:00:00.000Z',
    updatedAt: '2024-01-01T00:00:00.000Z',
    ...overrides
  };
}

/**
 * Create a mock label
 */
export function createMockLabel(overrides?: Partial<Label>): Label {
  return {
    id: 1,
    title: 'Test Label',
    description: 'A test label',
    color: '#FF6B6B',
    showOnSidebar: true,
    accountId: 1,
    createdAt: '2024-01-01T00:00:00.000Z',
    updatedAt: '2024-01-01T00:00:00.000Z',
    ...overrides
  };
}

/**
 * Create multiple mock items
 */
export function createMockList<T>(
  factory: (overrides?: Partial<T>) => T,
  count: number,
  overridesFn?: (index: number) => Partial<T>
): T[] {
  return Array.from({ length: count }, (_, i) =>
    factory(overridesFn ? overridesFn(i) : undefined)
  );
}
