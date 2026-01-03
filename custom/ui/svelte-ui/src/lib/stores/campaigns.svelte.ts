import { goto } from '$app/navigation';
import { page } from '$app/stores';
import * as campaignsApi from '$lib/api/campaigns';
import type {
  Campaign,
  CreateCampaignParams,
  UpdateCampaignParams,
  CampaignListParams,
} from '$lib/api/campaigns';
import { get } from 'svelte/store';
import { CAMPAIGN_TYPES } from '$lib/api/campaigns';

/**
 * Campaigns Store using Svelte 5 runes
 * Manages campaign state and CRUD operations
 */
class CampaignsStore {
  // Reactive state using $state rune
  allCampaigns = $state<Campaign[]>([]);
  selectedCampaignId = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  isCreating = $state<boolean>(false);
  isUpdating = $state<boolean>(false);
  isDeleting = $state<boolean>(false);
  error = $state<string | null>(null);

  // Computed values using $derived rune
  selectedCampaign = $derived(
    this.allCampaigns.find((c) => c.id === this.selectedCampaignId) || null
  );

  // Computed account ID from route params
  get currentAccountId(): number {
    const pageStore = get(page);
    return parseInt(pageStore.params.accountId || '0', 10);
  }

  // Get campaigns by type
  get liveChatCampaigns(): Campaign[] {
    return this.allCampaigns.filter(
      (c) =>
        c.campaignType === CAMPAIGN_TYPES.ONGOING &&
        c.inbox?.channelType === 'Channel::WebWidget'
    );
  }

  get smsCampaigns(): Campaign[] {
    return this.allCampaigns.filter(
      (c) =>
        c.campaignType === CAMPAIGN_TYPES.ONE_OFF &&
        (c.inbox?.channelType === 'Channel::Sms' ||
          c.inbox?.channelType === 'Channel::Twilio')
    );
  }

  get whatsappCampaigns(): Campaign[] {
    return this.allCampaigns.filter(
      (c) =>
        c.campaignType === CAMPAIGN_TYPES.ONE_OFF &&
        c.inbox?.channelType === 'Channel::Whatsapp'
    );
  }

  get sortedCampaigns(): Campaign[] {
    return [...this.allCampaigns].sort((a, b) => a.id - b.id);
  }

  get campaignsCount(): number {
    return this.allCampaigns.length;
  }

  /**
   * Fetch all campaigns
   */
  async fetchCampaigns(params: CampaignListParams = {}): Promise<void> {
    if (!this.currentAccountId) return;

    try {
      this.isLoading = true;
      this.error = null;

      const response = await campaignsApi.getCampaigns(
        this.currentAccountId,
        params
      );

      this.allCampaigns = response.data || [];
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch campaigns';
      console.error('Error fetching campaigns:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Fetch a single campaign
   */
  async fetchCampaign(campaignId: number): Promise<Campaign | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isLoading = true;
      this.error = null;

      const campaign = await campaignsApi.getCampaign(
        this.currentAccountId,
        campaignId
      );

      // Update in the store if it exists
      const index = this.allCampaigns.findIndex((c) => c.id === campaign.id);
      if (index !== -1) {
        this.allCampaigns[index] = campaign;
      } else {
        this.allCampaigns.push(campaign);
      }

      return campaign;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch campaign';
      console.error('Error fetching campaign:', err);
      return null;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Create a new campaign
   */
  async createCampaign(data: CreateCampaignParams): Promise<Campaign | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isCreating = true;
      this.error = null;

      const newCampaign = await campaignsApi.createCampaign(
        this.currentAccountId,
        data
      );

      this.allCampaigns.push(newCampaign);
      return newCampaign;
    } catch (err: any) {
      this.error = err.message || 'Failed to create campaign';
      console.error('Error creating campaign:', err);
      throw err;
    } finally {
      this.isCreating = false;
    }
  }

  /**
   * Update an existing campaign
   */
  async updateCampaign(
    campaignId: number,
    data: UpdateCampaignParams
  ): Promise<Campaign | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isUpdating = true;
      this.error = null;

      const updatedCampaign = await campaignsApi.updateCampaign(
        this.currentAccountId,
        campaignId,
        data
      );

      const index = this.allCampaigns.findIndex((c) => c.id === campaignId);
      if (index !== -1) {
        this.allCampaigns[index] = updatedCampaign;
      }

      return updatedCampaign;
    } catch (err: any) {
      this.error = err.message || 'Failed to update campaign';
      console.error('Error updating campaign:', err);
      throw err;
    } finally {
      this.isUpdating = false;
    }
  }

  /**
   * Delete a campaign
   */
  async deleteCampaign(campaignId: number): Promise<boolean> {
    if (!this.currentAccountId) return false;

    try {
      this.isDeleting = true;
      this.error = null;

      await campaignsApi.deleteCampaign(this.currentAccountId, campaignId);

      this.allCampaigns = this.allCampaigns.filter((c) => c.id !== campaignId);
      if (this.selectedCampaignId === campaignId) {
        this.selectedCampaignId = null;
      }

      return true;
    } catch (err: any) {
      this.error = err.message || 'Failed to delete campaign';
      console.error('Error deleting campaign:', err);
      return false;
    } finally {
      this.isDeleting = false;
    }
  }

  /**
   * Toggle campaign status
   */
  async toggleCampaignStatus(campaignId: number): Promise<Campaign | null> {
    if (!this.currentAccountId) return null;

    try {
      this.isUpdating = true;
      this.error = null;

      const updatedCampaign = await campaignsApi.toggleCampaignStatus(
        this.currentAccountId,
        campaignId
      );

      const index = this.allCampaigns.findIndex((c) => c.id === campaignId);
      if (index !== -1) {
        this.allCampaigns[index] = updatedCampaign;
      }

      return updatedCampaign;
    } catch (err: any) {
      this.error = err.message || 'Failed to toggle campaign status';
      console.error('Error toggling campaign status:', err);
      throw err;
    } finally {
      this.isUpdating = false;
    }
  }

  /**
   * Select a campaign
   */
  selectCampaign(campaignId: number | null): void {
    this.selectedCampaignId = campaignId;
  }

  /**
   * Clear all campaigns
   */
  clear(): void {
    this.allCampaigns = [];
    this.selectedCampaignId = null;
    this.error = null;
  }
}

// Export singleton instance
export const campaignsStore = new CampaignsStore();
