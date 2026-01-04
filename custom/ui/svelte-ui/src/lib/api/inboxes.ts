import { api } from './client';

/**
 * Inbox interfaces
 */
export interface Inbox {
  id: number;
  name: string;
  channelType: string;
  channelId: number;
  greeting_enabled: boolean;
  greeting_message: string;
  emailAddress?: string;
  inboxIdentifier?: string;
  websiteUrl?: string;
  webhookUrl?: string;
  enableAutoAssignment: boolean;
  outOfOfficeMessage?: string;
  workingHoursEnabled: boolean;
  workingHours?: WorkingHours[];
  timezone?: string;
  allowMessagesAfterResolved: boolean;
  autoAssignmentConfig?: AutoAssignmentConfig;
  imap_enabled?: boolean;
  imap_address?: string;
  imap_port?: number;
  imap_email?: string;
  imap_enable_ssl?: boolean;
  smtp_enabled?: boolean;
  smtp_address?: string;
  smtp_port?: number;
  smtp_email?: string;
  smtp_enable_ssl_tls?: boolean;
  smtp_enable_starttls_auto?: boolean;
  smtp_authentication?: string;
  messageTemplates?: MessageTemplate[];
  additionalAttributes?: Record<string, any>;
  avatarUrl?: string;
}

export interface WorkingHours {
  day_of_week: number;
  closed_all_day: boolean;
  open_hour?: number;
  open_minutes?: number;
  close_hour?: number;
  close_minutes?: number;
  open_all_day?: boolean;
}

export interface AutoAssignmentConfig {
  max_assignment_limit?: number;
}

export interface MessageTemplate {
  id: string;
  name: string;
  status: string;
  language: string;
  category: string;
  components: TemplateComponent[];
}

export interface TemplateComponent {
  type: string;
  format?: string;
  text?: string;
  parameters?: any[];
  buttons?: any[];
}

export interface InboxListParams {
  page?: number;
  perPage?: number;
  [key: string]: string | number | boolean | undefined;
}

export interface CreateInboxParams {
  name: string;
  channelType: string;
  channelData: Record<string, any>;
  greetingEnabled?: boolean;
  greetingMessage?: string;
  enableAutoAssignment?: boolean;
  workingHoursEnabled?: boolean;
  timezone?: string;
}

export interface UpdateInboxParams {
  name?: string;
  greeting_enabled?: boolean;
  greeting_message?: string;
  enable_auto_assignment?: boolean;
  out_of_office_message?: string;
  working_hours_enabled?: boolean;
  working_hours?: WorkingHours[];
  timezone?: string;
  allow_messages_after_resolved?: boolean;
  auto_assignment_config?: AutoAssignmentConfig;
  avatar?: File;
}

export interface IMAPSettings {
  imap_enabled: boolean;
  imap_address: string;
  imap_port: number;
  imap_email: string;
  imap_password: string;
  imap_enable_ssl: boolean;
}

export interface SMTPSettings {
  smtp_enabled: boolean;
  smtp_address: string;
  smtp_port: number;
  smtp_email: string;
  smtp_password: string;
  smtp_domain?: string;
  smtp_enable_ssl_tls: boolean;
  smtp_enable_starttls_auto: boolean;
  smtp_authentication?: string;
}

/**
 * Get list of inboxes
 */
export async function getInboxes(params?: InboxListParams): Promise<Inbox[]> {
  const response = await api.get('inboxes', {
    searchParams: params,
  }).json<Inbox[]>();
  return response;
}

/**
 * Get single inbox by ID
 */
export async function getInbox(inboxId: number): Promise<Inbox> {
  const response = await api.get(`inboxes/${inboxId}`).json<Inbox>();
  return response;
}

/**
 * Create new inbox
 */
export async function createInbox(params: CreateInboxParams): Promise<Inbox> {
  const response = await api.post('inboxes', {
    json: params,
  }).json<Inbox>();
  return response;
}

/**
 * Update inbox
 */
export async function updateInbox(
  inboxId: number,
  params: UpdateInboxParams
): Promise<Inbox> {
  // If avatar is present, use FormData
  if (params.avatar) {
    const formData = new FormData();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        if (key === 'avatar') {
          formData.append(key, value as File);
        } else if (typeof value === 'object') {
          formData.append(key, JSON.stringify(value));
        } else {
          formData.append(key, String(value));
        }
      }
    });

    const response = await api.patch(`inboxes/${inboxId}`, {
      body: formData,
    }).json<Inbox>();
    return response;
  }

  // Otherwise use JSON
  const response = await api.patch(`inboxes/${inboxId}`, {
    json: params,
  }).json<Inbox>();
  return response;
}

/**
 * Delete inbox
 */
export async function deleteInbox(inboxId: number): Promise<void> {
  await api.delete(`inboxes/${inboxId}`);
}

/**
 * Delete inbox avatar
 */
export async function deleteInboxAvatar(inboxId: number): Promise<void> {
  await api.delete(`inboxes/${inboxId}/avatar`);
}

/**
 * Get agent bot for inbox
 */
export async function getAgentBot(inboxId: number): Promise<any> {
  const response = await api.get(`inboxes/${inboxId}/agent_bot`).json();
  return response;
}

/**
 * Set agent bot for inbox
 */
export async function setAgentBot(inboxId: number, botId: number | null): Promise<void> {
  await api.post(`inboxes/${inboxId}/set_agent_bot`, {
    json: {
      agent_bot: botId,
    },
  });
}

/**
 * Sync WhatsApp templates for inbox
 */
export async function syncTemplates(inboxId: number): Promise<MessageTemplate[]> {
  const response = await api.post(`inboxes/${inboxId}/sync_templates`).json<MessageTemplate[]>();
  return response;
}

/**
 * Get campaigns for inbox
 */
export async function getCampaigns(inboxId: number): Promise<any[]> {
  const response = await api.get(`inboxes/${inboxId}/campaigns`).json<any[]>();
  return response;
}

/**
 * Update IMAP settings
 */
export async function updateIMAPSettings(
  inboxId: number,
  settings: IMAPSettings
): Promise<Inbox> {
  const response = await api.patch(`inboxes/${inboxId}`, {
    json: settings,
  }).json<Inbox>();
  return response;
}

/**
 * Update SMTP settings
 */
export async function updateSMTPSettings(
  inboxId: number,
  settings: SMTPSettings
): Promise<Inbox> {
  const response = await api.patch(`inboxes/${inboxId}`, {
    json: settings,
  }).json<Inbox>();
  return response;
}
