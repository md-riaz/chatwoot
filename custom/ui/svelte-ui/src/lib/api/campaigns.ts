import { api } from './client';
import type { PaginatedResponse } from './types';

/**
 * Campaign types
 */
export const CAMPAIGN_TYPES = {
  ONGOING: 'ongoing',
  ONE_OFF: 'one_off',
} as const;

export const CAMPAIGN_STATUS = {
  ACTIVE: 'active',
  PAUSED: 'paused',
  COMPLETED: 'completed',
} as const;

/**
 * Campaign interfaces
 */
export interface CampaignAudience {
  type: 'label' | 'country' | 'city' | 'browser_language';
  values: string[];
}

export interface TriggerRule {
  timeOnPage?: number;
  url?: string;
}

export interface Campaign {
  id: number;
  title: string;
  description?: string;
  message: string;
  campaignType: typeof CAMPAIGN_TYPES[keyof typeof CAMPAIGN_TYPES];
  campaignStatus: typeof CAMPAIGN_STATUS[keyof typeof CAMPAIGN_STATUS];
  audience: CampaignAudience[];
  enabled: boolean;
  triggerRules: TriggerRule;
  inboxId: number;
  inbox?: {
    id: number;
    name: string;
    channelType: string;
  };
  senderId?: number;
  sender?: {
    id: number;
    name: string;
    email: string;
    avatarUrl?: string;
  };
  scheduledAt?: string;
  createdAt: string;
  updatedAt: string;
}

export interface CreateCampaignParams {
  title: string;
  description?: string;
  message: string;
  campaignType: typeof CAMPAIGN_TYPES[keyof typeof CAMPAIGN_TYPES];
  audience?: CampaignAudience[];
  enabled?: boolean;
  triggerRules?: TriggerRule;
  inboxId: number;
  senderId?: number;
  scheduledAt?: string;
}

export interface UpdateCampaignParams extends Partial<CreateCampaignParams> {}

export interface CampaignListParams {
  page?: number;
  perPage?: number;
  inboxId?: number;
  campaignType?: string;
}

/**
 * Get all campaigns
 */
export async function getCampaigns(
  accountId: number,
  params: CampaignListParams = {}
): Promise<{ payload: Campaign[] }> {
  const searchParams = new URLSearchParams();
  if (params.page) searchParams.set('page', params.page.toString());
  if (params.perPage) searchParams.set('per_page', params.perPage.toString());
  if (params.inboxId) searchParams.set('inbox_id', params.inboxId.toString());
  if (params.campaignType)
    searchParams.set('campaign_type', params.campaignType);

  const query = searchParams.toString();
  const url = `api/v1/accounts/${accountId}/campaigns${query ? `?${query}` : ''}`;

  return api.get(url).json();
}

/**
 * Get a single campaign
 */
export async function getCampaign(
  accountId: number,
  campaignId: number
): Promise<Campaign> {
  return api.get(`api/v1/accounts/${accountId}/campaigns/${campaignId}`).json();
}

/**
 * Create a new campaign
 */
export async function createCampaign(
  accountId: number,
  data: CreateCampaignParams
): Promise<Campaign> {
  return api
    .post(`api/v1/accounts/${accountId}/campaigns`, { json: data })
    .json();
}

/**
 * Update an existing campaign
 */
export async function updateCampaign(
  accountId: number,
  campaignId: number,
  data: UpdateCampaignParams
): Promise<Campaign> {
  return api
    .patch(`api/v1/accounts/${accountId}/campaigns/${campaignId}`, {
      json: data,
    })
    .json();
}

/**
 * Delete a campaign
 */
export async function deleteCampaign(
  accountId: number,
  campaignId: number
): Promise<void> {
  return api
    .delete(`api/v1/accounts/${accountId}/campaigns/${campaignId}`)
    .json();
}

/**
 * Toggle campaign status (enable/disable)
 */
export async function toggleCampaignStatus(
  accountId: number,
  campaignId: number
): Promise<Campaign> {
  return api
    .post(`api/v1/accounts/${accountId}/campaigns/${campaignId}/toggle_status`)
    .json();
}
