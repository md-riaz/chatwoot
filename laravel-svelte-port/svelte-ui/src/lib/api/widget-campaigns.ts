import { api } from './client';

/**
 * Widget Campaign interfaces
 */
export interface WidgetCampaignSender {
  id: number;
  name: string;
  available_name: string;
  avatar_url?: string;
  availability_status: string;
}

export interface WidgetCampaignTriggerRules {
  timeOnPage?: number;
  url?: string;
}

export interface WidgetCampaign {
  id: number;
  title: string;
  message: string;
  sender?: WidgetCampaignSender;
  trigger_rules: WidgetCampaignTriggerRules;
  trigger_only_during_business_hours: boolean;
}

export interface TriggerCampaignParams {
  website_token: string;
  campaign_id: number;
  custom_attributes?: Record<string, any>;
  contact_identifier?: string;
  contact_identifier_hash?: string;
}

export interface WidgetCampaignsResponse {
  data: WidgetCampaign[];
}

/**
 * Get active campaigns for widget
 */
export async function getCampaigns(websiteToken: string): Promise<WidgetCampaignsResponse> {
  const searchParams = new URLSearchParams();
  searchParams.set('website_token', websiteToken);

  const query = searchParams.toString();
  const url = `api/v1/widget/campaigns?${query}`;

  return api.get(url).json();
}

/**
 * Trigger a campaign execution
 */
export async function triggerCampaign(params: TriggerCampaignParams): Promise<void> {
  return api
    .post('api/v1/widget/campaigns/trigger', { json: params })
    .json();
}