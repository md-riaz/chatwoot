import type { FormattedCampaign } from './campaign-helper';

/**
 * Campaign Timer utility ported from Vue implementation
 * Manages campaign timing and execution
 */
class CampaignTimer {
  private campaignTimers: Map<number, NodeJS.Timeout> = new Map();

  /**
   * Initialize timers for campaigns
   */
  initTimers(
    params: { campaigns: FormattedCampaign[] },
    websiteToken: string,
    onCampaignTrigger: (campaignId: number) => void
  ): void {
    const { campaigns } = params;
    
    // Clear existing timers first
    this.clearTimers();

    campaigns.forEach(campaign => {
      const { timeOnPage, id: campaignId } = campaign;
      
      if (timeOnPage && timeOnPage > 0) {
        const timerId = setTimeout(() => {
          onCampaignTrigger(campaignId);
        }, timeOnPage * 1000);

        this.campaignTimers.set(campaignId, timerId);
      }
    });
  }

  /**
   * Clear all campaign timers
   */
  clearTimers(): void {
    this.campaignTimers.forEach((timerId, campaignId) => {
      clearTimeout(timerId);
      this.campaignTimers.delete(campaignId);
    });
  }

  /**
   * Clear a specific campaign timer
   */
  clearTimer(campaignId: number): void {
    const timerId = this.campaignTimers.get(campaignId);
    if (timerId) {
      clearTimeout(timerId);
      this.campaignTimers.delete(campaignId);
    }
  }

  /**
   * Get active timer count
   */
  get activeTimerCount(): number {
    return this.campaignTimers.size;
  }

  /**
   * Check if a specific campaign has an active timer
   */
  hasTimer(campaignId: number): boolean {
    return this.campaignTimers.has(campaignId);
  }
}

// Export singleton instance
export const campaignTimer = new CampaignTimer();