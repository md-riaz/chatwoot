/**
 * Widget Campaign Manager
 * Orchestrates campaign functionality for the widget
 * Integrates store, events, and components
 */

import { widgetCampaignsStore } from '$lib/stores/widget-campaigns.svelte';
import { widgetEmitter } from './widget-events';
import {
  ON_CAMPAIGN_MESSAGE_CLICK,
  ON_UNREAD_MESSAGE_CLICK,
  EXECUTE_CAMPAIGN,
  SNOOZE_CAMPAIGNS,
  SET_CAMPAIGN_READ_ON,
  TOGGLE_BUBBLE,
  SET_UNREAD_MODE,
  WIDGET_VISIBLE,
  CHANGE_URL,
} from '$lib/constants/widget-events';
import type { TriggerCampaignParams } from '$lib/api/widget-campaigns';

export interface WidgetCampaignManagerConfig {
  websiteToken: string;
  isIFrame?: boolean;
  shouldShowPreChatForm?: boolean;
  messageCount?: number;
}

export class WidgetCampaignManager {
  private config: WidgetCampaignManagerConfig;
  private isInitialized = false;

  constructor(config: WidgetCampaignManagerConfig) {
    this.config = config;
  }

  /**
   * Initialize the campaign manager
   */
  async initialize(): Promise<void> {
    if (this.isInitialized) return;

    this.registerEventHandlers();
    this.isInitialized = true;
  }

  /**
   * Initialize campaigns for a specific URL
   */
  async initCampaigns(params: {
    currentURL: string;
    isInBusinessHours: boolean;
  }): Promise<void> {
    await widgetCampaignsStore.initCampaigns({
      websiteToken: this.config.websiteToken,
      currentURL: params.currentURL,
      isInBusinessHours: params.isInBusinessHours,
    });
  }

  /**
   * Set campaign view based on current state
   */
  setCampaignView(params: {
    messageCount: number;
    isWidgetOpen: boolean;
    router?: any; // Widget router instance
  }): void {
    const { messageCount, isWidgetOpen, router } = params;
    
    const shouldSnoozeCampaign = widgetCampaignsStore.isCampaignSnoozed;
    const isCampaignReadyToExecute =
      widgetCampaignsStore.isCampaignReadyToExecute &&
      !messageCount &&
      !shouldSnoozeCampaign;

    if (this.config.isIFrame && isCampaignReadyToExecute && router) {
      router.replace({ name: 'campaigns' }).then(() => {
        this.setIframeHeight(true);
        this.sendIFrameMessage({ event: SET_UNREAD_MODE });
      });
    }
  }

  /**
   * Handle campaign message click
   */
  private handleCampaignMessageClick(): void {
    if (this.config.shouldShowPreChatForm) {
      // Navigate to pre-chat form
      widgetEmitter.emit('navigate-to-prechat');
    } else {
      // Navigate to messages and execute campaign
      widgetEmitter.emit('navigate-to-messages');
      
      if (widgetCampaignsStore.activeCampaign) {
        widgetEmitter.emit(EXECUTE_CAMPAIGN, {
          campaignId: widgetCampaignsStore.activeCampaign.id,
          customAttributes: {},
        });
      }
    }
    
    widgetEmitter.emit('unset-unread-view');
  }

  /**
   * Execute a campaign
   */
  private async handleExecuteCampaign(event: {
    campaignId: number;
    customAttributes?: Record<string, any>;
  }): Promise<void> {
    const { campaignId, customAttributes } = event;

    try {
      const params: TriggerCampaignParams = {
        website_token: this.config.websiteToken,
        campaign_id: campaignId,
        custom_attributes: customAttributes,
      };

      await widgetCampaignsStore.executeCampaign(params);
      
      // Navigate to messages after successful execution
      widgetEmitter.emit('navigate-to-messages');
    } catch (error) {
      console.error('Failed to execute campaign:', error);
    }
  }

  /**
   * Handle campaign snoozing
   */
  private handleSnoozeCampaigns(): void {
    widgetCampaignsStore.snoozeCampaigns();
    
    if (this.config.isIFrame) {
      this.sendIFrameMessage({ event: SET_CAMPAIGN_READ_ON });
      this.sendIFrameMessage({ event: TOGGLE_BUBBLE });
    }
  }

  /**
   * Handle URL changes
   */
  async handleUrlChange(params: {
    referrerURL: string;
    isInBusinessHours: boolean;
  }): Promise<void> {
    await this.initCampaigns({
      currentURL: params.referrerURL,
      isInBusinessHours: params.isInBusinessHours,
    });
  }

  /**
   * Handle widget close
   */
  handleWidgetClose(): void {
    widgetCampaignsStore.resetCampaign();
  }

  /**
   * Set campaign read data from external source
   */
  setCampaignReadData(snoozedTill?: number): void {
    widgetCampaignsStore.setCampaignReadData(snoozedTill);
  }

  /**
   * Register event handlers
   */
  private registerEventHandlers(): void {
    // Campaign message click
    widgetEmitter.on(ON_CAMPAIGN_MESSAGE_CLICK, () => {
      this.handleCampaignMessageClick();
    });

    // Execute campaign
    widgetEmitter.on(EXECUTE_CAMPAIGN, (event) => {
      this.handleExecuteCampaign(event);
    });

    // Snooze campaigns
    widgetEmitter.on(SNOOZE_CAMPAIGNS, () => {
      this.handleSnoozeCampaigns();
    });

    // URL change
    widgetEmitter.on(CHANGE_URL, (event) => {
      this.handleUrlChange(event);
    });

    // Widget visibility
    widgetEmitter.on(WIDGET_VISIBLE, () => {
      // Handle widget becoming visible
    });
  }

  /**
   * Send message to iframe parent
   */
  private sendIFrameMessage(message: any): void {
    if (this.config.isIFrame && window.parent) {
      window.parent.postMessage(message, '*');
    }
  }

  /**
   * Set iframe height
   */
  private setIframeHeight(expand: boolean): void {
    if (this.config.isIFrame) {
      this.sendIFrameMessage({
        event: 'set-iframe-height',
        data: { expand }
      });
    }
  }

  /**
   * Get current campaign state
   */
  get campaignState() {
    return {
      activeCampaign: widgetCampaignsStore.activeCampaign,
      isCampaignSnoozed: widgetCampaignsStore.isCampaignSnoozed,
      isCampaignReadyToExecute: widgetCampaignsStore.isCampaignReadyToExecute,
      isLoading: widgetCampaignsStore.isLoading,
      error: widgetCampaignsStore.error,
    };
  }

  /**
   * Cleanup resources
   */
  destroy(): void {
    widgetCampaignsStore.clear();
    widgetEmitter.clear();
    this.isInitialized = false;
  }
}