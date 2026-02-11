<script lang="ts">
  /**
   * CampaignView Component
   * Main campaign display view for the widget
   * Ported from Vue Campaigns component and UnreadMessageList
   */
  import { createEventDispatcher } from 'svelte';
  import type { WidgetCampaign } from '$lib/api/widget-campaigns';
  import CampaignMessage from './CampaignMessage.svelte';
  import { X, ArrowRight } from 'lucide-svelte';

  interface Props {
    campaign: WidgetCampaign;
    companyName?: string;
    useInboxAvatarForBot?: boolean;
    inboxAvatarUrl?: string;
    widgetColor?: string;
    unreadMessageCount?: number;
  }

  let {
    campaign,
    companyName = '',
    useInboxAvatarForBot = false,
    inboxAvatarUrl = '',
    widgetColor = '#1f93ff',
    unreadMessageCount = 0,
  }: Props = $props();

  const dispatch = createEventDispatcher<{
    close: void;
    campaignClick: { campaignId: number };
    viewMessages: void;
  }>();

  let isBackgroundLighter = $derived(() => {
    // Simple check for light colors
    if (!widgetColor) return false;

    const hex = widgetColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);

    // Calculate brightness using relative luminance formula
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 128;
  });

  function handleClose() {
    dispatch('close');
  }

  function handleCampaignClick(event: CustomEvent<{ campaignId: number }>) {
    dispatch('campaignClick', event.detail);
  }

  function handleViewMessages() {
    dispatch('viewMessages');
  }
</script>

<div class="unread-wrap" dir="ltr">
  <!-- Close button -->
  <div class="close-unread-wrap">
    <button
      type="button"
      class="button small close-unread-button"
      onclick={handleClose}
    >
      <span class="flex items-center">
        <X class="mr-1" size={12} />
        Close
      </span>
    </button>
  </div>

  <!-- Campaign message -->
  <div class="unread-messages">
    <CampaignMessage
      {campaign}
      showSender={true}
      {companyName}
      {useInboxAvatarForBot}
      {inboxAvatarUrl}
      on:click={handleCampaignClick}
    />
  </div>

  <!-- View messages button -->
  {#if unreadMessageCount > 0}
    <div class="open-read-view-wrap">
      <button
        type="button"
        class="button clear-button"
        onclick={handleViewMessages}
      >
        <span
          class="flex items-center"
          class:text-slate-800={isBackgroundLighter}
          style:color={widgetColor}
        >
          <ArrowRight class="mr-2" size={16} />
          View Messages
        </span>
      </button>
    </div>
  {/if}
</div>

<style lang="postcss">
  .unread-wrap {
    width: 100%;
    height: auto;
    max-height: 100vh;
    background: transparent;
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    justify-content: flex-end;
    overflow: hidden;
  }

  .unread-messages {
    @apply pb-2;
  }

  .clear-button {
    transition: all 0.3s cubic-bezier(0.17, 0.67, 0.83, 0.67);
    @apply bg-transparent border-none border-0 font-semibold text-base ml-1 py-0 pl-0 pr-2.5 hover:brightness-75 hover:translate-x-1;
    color: var(--widget-color, #1f93ff);
  }

  .close-unread-button {
    transition: all 0.3s cubic-bezier(0.17, 0.67, 0.83, 0.67);
    @apply bg-gray-100 text-gray-800 hover:bg-gray-200 border-none border-0 font-medium text-xs rounded-2xl mb-3 px-3 py-1;
  }

  .button {
    @apply cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-blue-500/50;
  }
</style>
