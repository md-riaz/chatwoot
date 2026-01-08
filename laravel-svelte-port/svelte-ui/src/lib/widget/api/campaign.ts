/**
 * Campaign API
 * 
 * API methods for widget campaigns.
 */

import { getWidgetApi } from './client';
import type { Campaign, ApiResponse } from './types';

/**
 * Get active campaigns
 */
export async function getActiveCampaigns(): Promise<Campaign[]> {
  const api = getWidgetApi();
  const response = await api.get('campaigns').json<ApiResponse<Campaign[]>>();
  return response.data;
}

/**
 * Trigger a campaign
 */
export async function triggerCampaign(campaignId: number): Promise<void> {
  const api = getWidgetApi();
  await api.post(`campaigns/${campaignId}/trigger`).json();
}
