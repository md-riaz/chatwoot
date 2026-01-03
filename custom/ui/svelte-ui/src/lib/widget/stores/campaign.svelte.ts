/**
 * Widget Campaign Store
 * 
 * Manages campaign messages display and dismissal.
 */

import * as campaignApi from '../api/campaign';
import type { Campaign } from '../api/types';

class WidgetCampaignStore {
  private campaigns = $state<Campaign[]>([]);
  private dismissedIds = $state<Set<number>>(new Set());
  private isLoading = $state(false);

  // Getters
  get allCampaigns() {
    return this.campaigns;
  }

  get loading() {
    return this.isLoading;
  }

  // Derived values
  get activeCampaigns() {
    return $derived(
      this.campaigns.filter((c) => c.enabled && !this.dismissedIds.has(c.id))
    );
  }

  get hasActiveCampaigns() {
    return $derived(this.activeCampaigns.length > 0);
  }

  // Actions
  async fetchCampaigns(): Promise<void> {
    this.isLoading = true;

    try {
      const campaigns = await campaignApi.getActiveCampaigns();
      this.campaigns = campaigns;
    } catch (err) {
      console.error('Failed to fetch campaigns:', err);
    } finally {
      this.isLoading = false;
    }
  }

  dismissCampaign(campaignId: number) {
    this.dismissedIds.add(campaignId);
    // Trigger reactivity
    this.dismissedIds = new Set(this.dismissedIds);
  }

  reset() {
    this.campaigns = [];
    this.dismissedIds = new Set();
    this.isLoading = false;
  }
}

export const widgetCampaignStore = new WidgetCampaignStore();
