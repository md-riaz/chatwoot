import { api, toSearchParams } from './client';
import type { Agent } from './agents';

/**
 * Inbox interfaces
 */
export interface Inbox {
  id: number;
  name: string;
  channelType: string;
  channelId: number;
  channel?: Record<string, any>;
  greeting_enabled: boolean;
  greeting_message: string;
  emailAddress?: string;
  inboxIdentifier?: string;
  websiteUrl?: string;
  webhookUrl?: string;
  enableAutoAssignment: boolean;
  csatSurveyEnabled?: boolean;
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
  forwardingEnabled?: boolean;
  forwardToEmail?: string;
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

export interface FacebookPageOption {
  id: string;
  name: string;
  pageAccessToken?: string;
  userAccessToken?: string;
  instagramId?: string | null;
  exists?: boolean;
}

export interface CreateFacebookInboxParams {
  name: string;
  pageId: string;
  pageAccessToken: string;
  userAccessToken: string;
}

export interface CreateWhatsappInboxParams {
  name: string;
  phoneNumber: string;
  provider: 'whatsapp_cloud';
  providerConfig: {
    phoneNumberId: string;
    businessAccountId: string;
    apiKey: string;
  };
}

export interface CreateEmailInboxParams {
  name: string;
  email: string;
  imapEnabled?: boolean;
  smtpEnabled?: boolean;
}

export interface CreateWebWidgetInboxParams {
  name: string;
  websiteUrl: string;
  widgetColor?: string;
  welcomeTitle?: string;
  welcomeTagline?: string;
  greetingEnabled?: boolean;
  greetingMessage?: string;
  enableAutoAssignment?: boolean;
  workingHoursEnabled?: boolean;
  timezone?: string;
}

export interface UpdateWebWidgetInboxParams {
  name?: string;
  websiteUrl?: string;
  widgetColor?: string;
  welcomeTitle?: string;
  welcomeTagline?: string;
  preChatFormEnabled?: boolean;
  preChatFormOptions?: Record<string, any>;
  hmacMandatory?: boolean;
  continuityViaEmail?: boolean;
  allowedDomains?: string;
}

export interface WebWidgetScriptResponse {
  script: string;
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
  csat_survey_enabled?: boolean;
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

export interface UpdateWorkingHoursParams {
  working_hours: WorkingHours[];
}

/**
 * Get list of inboxes
 */
export async function getInboxes(accountId: number, params?: InboxListParams): Promise<Inbox[]> {
  const response = await api.get(`api/v1/accounts/${accountId}/inboxes`, {
    searchParams: toSearchParams(params),
  }).json<{ data: Inbox[] }>();
  return response.data;
}

/**
 * Get single inbox by ID
 */
export async function getInbox(accountId: number, inboxId: number): Promise<Inbox> {
  const response = await api.get(`api/v1/accounts/${accountId}/inboxes/${inboxId}`).json<Inbox>();
  return response;
}

/**
 * Create new inbox
 */
export async function createInbox(accountId: number, params: CreateInboxParams): Promise<Inbox> {
  const response = await api.post(`api/v1/accounts/${accountId}/inboxes`, {
    json: params,
  }).json<Inbox>();
  return response;
}

export async function getFacebookAuthorizationUrl(
  accountId: number
): Promise<string> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/callbacks/facebook/initiateAuthorization`)
    .json<{ authorizationUrl: string }>();

  return response.authorizationUrl;
}

export async function getInstagramAuthorizationUrl(
  accountId: number
): Promise<string> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/channels/instagram/initiateAuthorization`)
    .json<{ url: string }>();

  return response.url;
}

export async function consumeFacebookCallbackToken(
  accountId: number,
  tokenKey: string
): Promise<string> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/callbacks/facebook/token`, {
      searchParams: {
        token_key: tokenKey,
      },
    })
    .json<{ userAccessToken: string }>();

  return response.userAccessToken;
}

export async function getFacebookPages(
  accountId: number,
  userAccessToken?: string
): Promise<FacebookPageOption[]> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/channels/facebook/pages`, {
      searchParams: userAccessToken
        ? { user_access_token: userAccessToken }
        : undefined,
    })
    .json<{ data: FacebookPageOption[] }>();

  return response.data || [];
}

export async function createFacebookInbox(
  accountId: number,
  params: CreateFacebookInboxParams
): Promise<Inbox> {
  const response = await api
    .post(`api/v1/accounts/${accountId}/channels/facebook`, {
      json: params,
    })
    .json<{ data: Inbox }>();

  return response.data;
}

export async function createWhatsappInbox(
  accountId: number,
  params: CreateWhatsappInboxParams
): Promise<Inbox> {
  const response = await api
    .post(`api/v1/accounts/${accountId}/channels/whatsapp`, {
      json: params,
    })
    .json<{ data: Inbox }>();

  return response.data;
}

export async function createEmailInbox(
  accountId: number,
  params: CreateEmailInboxParams
): Promise<Inbox> {
  const response = await api
    .post(`api/v1/accounts/${accountId}/channels/email`, {
      json: params,
    })
    .json<{ data: Inbox }>();

  return response.data;
}

export async function createWebWidgetInbox(
  accountId: number,
  params: CreateWebWidgetInboxParams
): Promise<Inbox> {
  const response = await api
    .post(`api/v1/accounts/${accountId}/channels/web_widget`, {
      json: params,
    })
    .json<{ data: Inbox }>();

  return response.data;
}

/**
 * Update inbox
 */
export async function updateInbox(
  accountId: number,
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

    const response = await api.patch(`api/v1/accounts/${accountId}/inboxes/${inboxId}`, {
      body: formData,
    }).json<Inbox>();
    return response;
  }

  // Otherwise use JSON
  const response = await api.patch(`api/v1/accounts/${accountId}/inboxes/${inboxId}`, {
    json: params,
  }).json<Inbox>();
  return response;
}

/**
 * Delete inbox
 */
export async function deleteInbox(accountId: number, inboxId: number): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/inboxes/${inboxId}`);
}

/**
 * Delete inbox avatar
 */
export async function deleteInboxAvatar(accountId: number, inboxId: number): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/inboxes/${inboxId}/avatar`);
}

/**
 * Get agent bot for inbox
 */
export async function getAgentBot(accountId: number, inboxId: number): Promise<any> {
  const response = await api.get(`api/v1/accounts/${accountId}/inboxes/${inboxId}/agent_bot`).json();
  return response;
}

/**
 * Set agent bot for inbox
 */
export async function setAgentBot(accountId: number, inboxId: number, botId: number | null): Promise<void> {
  await api.post(`api/v1/accounts/${accountId}/inboxes/${inboxId}/set_agent_bot`, {
    json: {
      agent_bot: botId,
    },
  });
}

/**
 * Sync WhatsApp templates for inbox
 */
export async function syncTemplates(accountId: number, inboxId: number): Promise<MessageTemplate[]> {
  const response = await api.post(`api/v1/accounts/${accountId}/inboxes/${inboxId}/sync_templates`).json<MessageTemplate[]>();
  return response;
}

/**
 * Get campaigns for inbox
 */
export async function getCampaigns(accountId: number, inboxId: number): Promise<any[]> {
  const response = await api.get(`api/v1/accounts/${accountId}/inboxes/${inboxId}/campaigns`).json<any[]>();
  return response;
}

/**
 * Get inbox members
 */
export async function getInboxMembers(
  accountId: number,
  inboxId: number
): Promise<Agent[]> {
  return api
    .get(`api/v1/accounts/${accountId}/inboxes/${inboxId}/members`)
    .json<Agent[]>();
}

/**
 * Add inbox members
 */
export async function addInboxMembers(
  accountId: number,
  inboxId: number,
  userIds: number[]
): Promise<{ data: Agent[] }> {
  return api
    .post(`api/v1/accounts/${accountId}/inboxes/${inboxId}/members`, {
      json: { userIds },
    })
    .json<{ data: Agent[] }>();
}

/**
 * Remove inbox members
 */
export async function removeInboxMembers(
  accountId: number,
  inboxId: number,
  userIds: number[]
): Promise<void> {
  await api.delete(`api/v1/accounts/${accountId}/inboxes/${inboxId}/members`, {
    json: { userIds },
  });
}

/**
 * Update IMAP settings
 */
export async function updateIMAPSettings(
  accountId: number,
  inboxId: number,
  settings: IMAPSettings
): Promise<Inbox> {
  const response = await api.patch(`api/v1/accounts/${accountId}/channels/email/${inboxId}`, {
    json: settings,
  }).json<Inbox>();
  return response;
}

/**
 * Update SMTP settings
 */
export async function updateSMTPSettings(
  accountId: number,
  inboxId: number,
  settings: SMTPSettings
): Promise<Inbox> {
  const response = await api.patch(`api/v1/accounts/${accountId}/channels/email/${inboxId}`, {
    json: settings,
  }).json<Inbox>();
  return response;
}

export async function updateWebWidgetInbox(
  accountId: number,
  inboxId: number,
  params: UpdateWebWidgetInboxParams
): Promise<Inbox> {
  const response = await api
    .patch(`api/v1/accounts/${accountId}/channels/web_widget/${inboxId}`, {
      json: params,
    })
    .json<{ data: Inbox }>();

  return response.data;
}

export async function getWebWidgetScript(
  accountId: number,
  inboxId: number
): Promise<string> {
  const response = await api
    .get(`api/v1/accounts/${accountId}/channels/web_widget/${inboxId}/script`)
    .json<{ data: WebWidgetScriptResponse }>();

  return response.data.script;
}

export async function updateWorkingHours(
  accountId: number,
  inboxId: number,
  params: UpdateWorkingHoursParams
): Promise<WorkingHours[]> {
  const response = await api
    .patch(`api/v1/accounts/${accountId}/inboxes/${inboxId}/working_hours`, {
      json: params,
    })
    .json<{ data: WorkingHours[] }>();

  return response.data;
}
