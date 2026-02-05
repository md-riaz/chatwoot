import * as widgetCampaignsApi from '$lib/api/widget-campaigns';
import type { WidgetCampaign, TriggerCampaignParams } from '$lib/api/widget-campaigns';
import { campaignTimer } from '$lib/utils/campaign-timer';
import { isPatternMatchingWithURL, filterCampaigns } from '$lib/utils/campaign-helper';

/**
 * Widget Campaigns Store using Svelte 5 runes
 * Manages widget-side campaign state and execution
 */
class WidgetCampaignsStore {
  // Reactive state using $state rune
  allCampaigns = $state<WidgetCampaign[]>([]);
  activeCampaign = $state<WidgetCampaign | null>(null);
  campaignsSnoozedTill = $state<number | null>(null);
  isLoading = $state<boolean>(false);
  error = $state<string | null>(null);
  hasFetched = $state<boolean>(false);

  // Computed values using $derived rune
  get isCampaignSnoozed(): boolean {
    return this.campaignsSnoozedTill !== null && this.campaignsSnoozedTill > Date.now();
  }

  get isCampaignReadyToExecute(): boolean {
    return (
      this.activeCampaign !== null &&
      !this.isCampaignSnoozed
    );
  }

  /**
   * Fetch campaigns from the widget API
   */
  async fetchCampaigns(websiteToken: string): Promise<void> {
    try {
      this.isLoading = true;
      this.error = null;

      const response = await widgetCampaignsApi.getCampaigns(websiteToken);
      this.allCampaigns = response.data || [];
      this.hasFetched = true;
    } catch (err: any) {
      this.error = err.message || 'Failed to fetch campaigns';
      console.error('Error fetching widget campaigns:', err);
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Initialize campaigns with URL filtering and timer setup
   */
  async initCampaigns(params: {
    websiteToken: string;
    currentURL: string;
    isInBusinessHours: boolean;
  }): Promise<void> {
    const { websiteToken, currentURL, isInBusinessHours } = params;

    if (!this.allCampaigns.length) {
      if (!this.hasFetched) {
        await this.fetchCampaigns(websiteToken);
      }
    }

    if (this.allCampaigns.length > 0) {
      this.resetCampaignTimers(currentURL, websiteToken, isInBusinessHours);
    }
  }

  /**
   * Start a specific campaign
   */
  async startCampaign(params: {
    websiteToken: string;
    campaignId: number;
    isWidgetOpen: boolean;
  }): Promise<void> {
    const { websiteToken, campaignId, isWidgetOpen } = params;

    // Don't execute campaigns if widget is already open
    if (isWidgetOpen) {
      return;
    }

    try {
      // Refresh campaigns to ensure we have latest data
      await this.fetchCampaigns(websiteToken);

      // Find the campaign
      const campaign = this.allCampaigns.find(c => c.id === campaignId);
      
      if (campaign) {
        this.activeCampaign = campaign;
      }
    } catch (err: any) {
      this.error = err.message || 'Failed to start campaign';
      console.error('Error starting campaign:', err);
    }
  }

  /**
   * Execute a campaign (trigger conversation creation)
   */
  async executeCampaign(params: TriggerCampaignParams): Promise<void> {
    try {
      this.isLoading = true;
      this.error = null;

      await widgetCampaignsApi.triggerCampaign(params);
      
      // Reset active campaign after successful execution
      this.activeCampaign = null;
    } catch (err: any) {
      this.error = err.message || 'Failed to execute campaign';
      console.error('Error executing campaign:', err);
      throw err;
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Reset active campaign
   */
  resetCampaign(): void {
    this.activeCampaign = null;
    campaignTimer.clearTimers();
  }

  /**
   * Snooze campaigns for 1 hour
   */
  snoozeCampaigns(): void {
    const expireBy = new Date();
    expireBy.setHours(expireBy.getHours() + 1);
    this.campaignsSnoozedTill = expireBy.getTime();
    
    // Clear any active campaign
    this.activeCampaign = null;
    campaignTimer.clearTimers();
  }

  /**
   * Set campaign snooze data from external source
   */
  setCampaignReadData(snoozedTill?: number): void {
    if (snoozedTill) {
      this.campaignsSnoozedTill = snoozedTill;
    }
  }

  /**
   * Reset campaign timers based on current URL and business hours
   */
  private resetCampaignTimers(
    currentURL: string,
    websiteToken: string,
    isInBusinessHours: boolean
  ): void {
    // Filter campaigns based on URL and business hours
    const filteredCampaigns = filterCampaigns({
      campaigns: this.allCampaigns,
      currentURL,
      isInBusinessHours,
    });

    // Initialize timers for filtered campaigns
    campaignTimer.initTimers(
      { campaigns: filteredCampaigns },
      websiteToken,
      (campaignId: number) => {
        this.startCampaign({
          websiteToken,
          campaignId,
          isWidgetOpen: false, // This will be determined by the widget state
        });
      }
    );
  }

  /**
   * Clear all campaigns and reset state
   */
  clear(): void {
    this.allCampaigns = [];
    this.activeCampaign = null;
    this.campaignsSnoozedTill = null;
    this.error = null;
    this.hasFetched = false;
    campaignTimer.clearTimers();
  }
}

// Export singleton instance
export const widgetCampaignsStore = new WidgetCampaignsStore();