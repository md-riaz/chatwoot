<script lang="ts">
  /**
   * CampaignMessage Component
   * Displays individual campaign messages in the widget
   * Ported from Vue UnreadMessage component
   */
  import { createEventDispatcher } from 'svelte';
  import type { WidgetCampaign } from '$lib/api/widget-campaigns';
  import { Avatar, AvatarImage, AvatarFallback } from '$lib/components/ui/avatar';

  interface Props {
    campaign: WidgetCampaign;
    showSender?: boolean;
    companyName?: string;
    useInboxAvatarForBot?: boolean;
    inboxAvatarUrl?: string;
  }

  let {
    campaign,
    showSender = false,
    companyName = '',
    useInboxAvatarForBot = false,
    inboxAvatarUrl = ''
  }: Props = $props();

  const dispatch = createEventDispatcher<{
    click: { campaignId: number };
  }>();

  let avatarUrl = $derived(() => {
    if (campaign.sender) {
      return campaign.sender.avatar_url || '';
    }
    
    if (useInboxAvatarForBot && inboxAvatarUrl) {
      return inboxAvatarUrl;
    }
    
    return '/assets/images/chatwoot_bot.png';
  });

  let agentName = $derived(() => {
    if (campaign.sender) {
      return campaign.sender.available_name || campaign.sender.name;
    }
    
    if (useInboxAvatarForBot && companyName) {
      return companyName;
    }
    
    return 'Bot';
  });

  let availabilityStatus = $derived(() => {
    return campaign.sender?.availability_status || null;
  });

  let displayCompanyName = $derived(() => {
    return companyName ? `from ${companyName}` : '';
  });

  function handleClick() {
    dispatch('click', { campaignId: campaign.id });
  }

  function formatMessage(content: string): string {
    // Basic HTML sanitization and formatting
    return content
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#x27;')
      .replace(/\n/g, '<br>');
  }
</script>

<div class="chat-bubble-wrap">
  <button 
    type="button"
    class="chat-bubble agent bg-white" 
    onclick={handleClick}
  >
    {#if showSender}
      <div class="row--agent-block">
        <Avatar class="h-5 w-5 rounded-full">
          <AvatarImage src={avatarUrl} alt={agentName} />
          <AvatarFallback>{agentName.slice(0, 2).toUpperCase()}</AvatarFallback>
        </Avatar>
        <span class="agent--name">{agentName}</span>
        {#if displayCompanyName}
          <span class="company--name">{displayCompanyName}</span>
        {/if}
      </div>
    {/if}
    
    <div class="message-content">
      {@html formatMessage(campaign.message)}
    </div>
  </button>
</div>

<style>
  .chat-bubble {
    @apply max-w-[85%] cursor-pointer p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow;
  }

  .row--agent-block {
    @apply items-center flex text-left pb-2 text-xs;
  }

  .agent--name {
    @apply font-medium ml-1 text-gray-900;
  }

  .company--name {
    @apply text-gray-600 ml-1;
  }

  .message-content {
    @apply text-sm text-gray-800 leading-relaxed;
  }

  .message-content :global(br) {
    @apply block mb-1;
  }
</style>