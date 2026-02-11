<script lang="ts">
  /**
   * Widget App Component
   * Main widget application with campaign integration
   * Demonstrates how to use the campaign system
   */
  import { onMount, onDestroy } from 'svelte';
  import { widgetCampaignsStore } from '$lib/stores/widget-campaigns.svelte';
  import { WidgetCampaignManager } from '$lib/utils/widget-campaign-manager';
  import { widgetEmitter } from '$lib/utils/widget-events';
  import CampaignView from './CampaignView.svelte';
  import {
    ON_CAMPAIGN_MESSAGE_CLICK,
    EXECUTE_CAMPAIGN,
    SNOOZE_CAMPAIGNS,
  } from '$lib/constants/widget-events';

  interface Props {
    websiteToken: string;
    companyName?: string;
    widgetColor?: string;
    isIFrame?: boolean;
  }

  let {
    websiteToken,
    companyName = '',
    widgetColor = '#1f93ff',
    isIFrame = false,
  }: Props = $props();

  // Widget state
  let isWidgetOpen = $state(false);
  let messageCount = $state(0);
  let currentRoute = $state('home');
  let campaignsSnoozedTill = $state<number | null>(null);

  // Campaign manager
  let campaignManager: WidgetCampaignManager;

  // Reactive campaign state
  let activeCampaign = $derived(widgetCampaignsStore.activeCampaign);
  let isCampaignReadyToExecute = $derived(
    widgetCampaignsStore.isCampaignReadyToExecute
  );

  onMount(async () => {
    // Initialize campaign manager
    campaignManager = new WidgetCampaignManager({
      websiteToken,
      isIFrame,
      shouldShowPreChatForm: false, // Configure based on your needs
      messageCount,
    });

    await campaignManager.initialize();

    // Initialize campaigns for current URL
    await campaignManager.initCampaigns({
      currentURL: window.location.href,
      isInBusinessHours: true, // You should implement business hours logic
    });

    // Register widget-specific event handlers
    registerWidgetEvents();

    // Set up URL change detection
    setupUrlChangeDetection();

    // Set initial campaign view
    setCampaignView();
  });

  onDestroy(() => {
    campaignManager?.destroy();
  });

  function registerWidgetEvents() {
    // Handle campaign message clicks
    widgetEmitter.on(ON_CAMPAIGN_MESSAGE_CLICK, () => {
      if (shouldShowPreChatForm()) {
        currentRoute = 'prechat-form';
      } else {
        currentRoute = 'messages';
        if (activeCampaign) {
          widgetEmitter.emit(EXECUTE_CAMPAIGN, {
            campaignId: activeCampaign.id,
            customAttributes: {},
          });
        }
      }
      unsetUnreadView();
    });

    // Campaign execution is handled by campaignManager internally
    // Just listen for navigation after execution
    widgetEmitter.on('navigate-to-messages', () => {
      currentRoute = 'messages';
    });

    // Handle campaign snoozing
    widgetEmitter.on(SNOOZE_CAMPAIGNS, () => {
      const expireBy = new Date();
      expireBy.setHours(expireBy.getHours() + 1);
      campaignsSnoozedTill = expireBy.getTime();
    });

    // Handle navigation events
    widgetEmitter.on('navigate-to-prechat', () => {
      currentRoute = 'prechat-form';
    });

    widgetEmitter.on('navigate-to-messages', () => {
      currentRoute = 'messages';
    });

    widgetEmitter.on('unset-unread-view', () => {
      unsetUnreadView();
    });
  }

  function setupUrlChangeDetection() {
    // Listen for URL changes (for SPAs)
    let lastUrl = window.location.href;

    const checkUrlChange = () => {
      const currentUrl = window.location.href;
      if (currentUrl !== lastUrl) {
        lastUrl = currentUrl;
        campaignManager.handleUrlChange({
          referrerURL: currentUrl,
          isInBusinessHours: true, // Implement business hours logic
        });
      }
    };

    // Check for URL changes periodically
    const urlCheckInterval = setInterval(checkUrlChange, 1000);

    // Also listen for popstate events
    window.addEventListener('popstate', checkUrlChange);

    // Cleanup
    onDestroy(() => {
      clearInterval(urlCheckInterval);
      window.removeEventListener('popstate', checkUrlChange);
    });
  }

  function setCampaignView() {
    const shouldSnoozeCampaign =
      campaignsSnoozedTill && campaignsSnoozedTill > Date.now();

    const isCampaignReady =
      activeCampaign && !messageCount && !shouldSnoozeCampaign;

    if (isIFrame && isCampaignReady) {
      currentRoute = 'campaigns';
      setIframeHeight(true);
      sendIFrameMessage({ event: 'setUnreadMode' });
    }
  }

  function shouldShowPreChatForm(): boolean {
    // Implement your pre-chat form logic here
    return false;
  }

  function unsetUnreadView() {
    // Handle unread view state changes
  }

  function handleCampaignClose() {
    if (isIFrame) {
      sendIFrameMessage({ event: 'setCampaignReadOn' });
      sendIFrameMessage({ event: 'toggleBubble' });
      widgetEmitter.emit(SNOOZE_CAMPAIGNS);
    }
  }

  function handleCampaignClick(event: CustomEvent<{ campaignId: number }>) {
    widgetEmitter.emit(ON_CAMPAIGN_MESSAGE_CLICK, event.detail.campaignId);
  }

  function handleViewMessages() {
    widgetEmitter.emit('navigate-to-messages');
  }

  function sendIFrameMessage(message: any) {
    if (isIFrame && window.parent) {
      window.parent.postMessage(message, '*');
    }
  }

  function setIframeHeight(expand: boolean) {
    if (isIFrame) {
      sendIFrameMessage({
        event: 'setIframeHeight',
        data: { expand },
      });
    }
  }

  // Watch for campaign changes
  $effect(() => {
    setCampaignView();
  });

  // Handle widget open/close
  $effect(() => {
    if (!isWidgetOpen) {
      campaignManager?.handleWidgetClose();
    }
  });
</script>

<div class="widget-app">
  {#if currentRoute === 'campaigns' && activeCampaign}
    <CampaignView
      campaign={activeCampaign}
      {companyName}
      {widgetColor}
      unreadMessageCount={messageCount}
      useInboxAvatarForBot={true}
      on:close={handleCampaignClose}
      on:campaignClick={handleCampaignClick}
      on:viewMessages={handleViewMessages}
    />
  {:else if currentRoute === 'messages'}
    <!-- Your messages component here -->
    <div class="messages-view">
      <h3>Messages View</h3>
      <p>This is where your messages component would go</p>
    </div>
  {:else if currentRoute === 'prechat-form'}
    <!-- Your pre-chat form component here -->
    <div class="prechat-form">
      <h3>Pre-chat Form</h3>
      <p>This is where your pre-chat form would go</p>
    </div>
  {:else}
    <!-- Default home view -->
    <div class="home-view">
      <h3>Widget Home</h3>
      <p>Welcome to the widget</p>

      {#if activeCampaign && isCampaignReadyToExecute}
        <div class="campaign-notification">
          <p>Campaign ready: {activeCampaign.title}</p>
          <button
            onclick={() =>
              handleCampaignClick({
                detail: { campaignId: activeCampaign.id },
              } as CustomEvent<{ campaignId: number }>)}
          >
            View Campaign
          </button>
        </div>
      {/if}
    </div>
  {/if}
</div>

<style lang="postcss">
  .widget-app {
    @apply w-full h-full;
  }

  .messages-view,
  .prechat-form,
  .home-view {
    @apply p-4;
  }

  .campaign-notification {
    @apply mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg;
  }

  .campaign-notification button {
    @apply mt-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700;
  }
</style>
